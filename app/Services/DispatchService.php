<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\DispatchRepository;
use App\Repositories\OrderRepository;

class DispatchService
{
    public function __construct(
        private readonly DispatchRepository $dispatch = new DispatchRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
    ) {}

    public function getEligibleRidersForOrder(int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if (!$order) {
            return [];
        }

        return $this->dispatch->eligibleRiders(
            $order,
            (float)Env::get('DISPATCH_RADIUS_KM', 5),
            (int)Env::get('MAX_ACTIVE_JOBS_PER_RIDER', 3)
        );
    }

    public function rankRidersByPickupProximity(int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        $riders = $this->getEligibleRidersForOrder($orderId);

        usort($riders, function ($a, $b) use ($order) {
            $da = $this->distance((float)$order['pickup_lat'], (float)$order['pickup_lng'], (float)$a['lat'], (float)$a['lng']);
            $db = $this->distance((float)$order['pickup_lat'], (float)$order['pickup_lng'], (float)$b['lat'], (float)$b['lng']);
            return $da <=> $db;
        });

        foreach ($riders as $idx => &$r) {
            $r['distance_to_pickup_km'] = round($this->distance((float)$order['pickup_lat'], (float)$order['pickup_lng'], (float)$r['lat'], (float)$r['lng']), 3);
            $r['rank_score'] = 1 / max($r['distance_to_pickup_km'], 0.1);
            $r['rank_position'] = $idx + 1;
        }
        return $riders;
    }

    public function autoAssignOrder(int $orderId): bool
    {
        $mode = Env::get('DISPATCH_MODE', 'reserved');
        return $mode === 'open' ? $this->reserveOrderForNearestRider($orderId) : $this->reserveOrderForNearestRider($orderId);
    }

    public function reserveOrderForNearestRider(int $orderId): bool
    {
        $ranked = $this->rankRidersByPickupProximity($orderId);
        if (empty($ranked)) {
            return false;
        }

        $nearest = $ranked[0];
        $reservation = (new \DateTimeImmutable('+' . Env::get('ASSIGNMENT_RESERVATION_MINUTES', 3) . ' minutes'))->format('Y-m-d H:i:s');

        $this->dispatch->createAttempt([
            'order_id' => $orderId,
            'rider_id' => $nearest['id'],
            'distance_to_pickup_km' => $nearest['distance_to_pickup_km'],
            'rank_score' => $nearest['rank_score'],
            'dispatch_status' => 'reserved',
            'reserved_until' => $reservation,
            'responded_at' => null,
        ]);

        $this->orders->addHistory($orderId, 'assigned', 'system', 0, 'Reserva para rider próximo #' . $nearest['id']);
        return true;
    }

    public function releaseExpiredAssignmentReservation(int $orderId): bool
    {
        $reservation = $this->dispatch->latestReservation($orderId);
        if (!$reservation || strtotime((string)$reservation['reserved_until']) > time()) {
            return false;
        }

        $this->dispatch->createAttempt([
            'order_id' => $orderId,
            'rider_id' => (int)$reservation['rider_id'],
            'distance_to_pickup_km' => (float)$reservation['distance_to_pickup_km'],
            'rank_score' => (float)$reservation['rank_score'],
            'dispatch_status' => 'expired',
            'reserved_until' => null,
            'responded_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function reassignOrderIfTimedOut(int $orderId): bool
    {
        return $this->releaseExpiredAssignmentReservation($orderId) && $this->reserveOrderForNearestRider($orderId);
    }

    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
