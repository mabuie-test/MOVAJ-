<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;
use App\Repositories\RiderLocationRepository;

class LiveTrackingService
{
    public function __construct(
        private readonly RiderLocationRepository $locations = new RiderLocationRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
    ) {}

    public function updateRiderLocation(int $riderId, float $lat, float $lng, ?int $orderId = null, ?float $heading = null, ?float $speed = null, ?float $accuracy = null): bool
    {
        if ($orderId) {
            $order = $this->orders->findById($orderId);
            if (!$order || (int)($order['assigned_rider_id'] ?? 0) !== $riderId) {
                return false;
            }
        }

        $this->locations->insert([
            'rider_id' => $riderId,
            'order_id' => $orderId,
            'lat' => $lat,
            'lng' => $lng,
            'heading' => $heading,
            'speed' => $speed,
            'accuracy' => $accuracy,
            'source' => 'mobile_polling',
        ]);
        return true;
    }

    public function getLatestRiderLocation(int $riderId): ?array
    {
        $latest = $this->locations->latestByRider($riderId);
        if (!$latest) {
            return null;
        }
        $staleMax = (int)Env::get('MAX_LOCATION_STALENESS_SECONDS', 120);
        if (time() - strtotime((string)$latest['created_at']) > $staleMax) {
            return null;
        }
        return $latest;
    }

    public function getOrderLiveTrackingData(int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if (!$order) {
            return [];
        }
        $latest = !empty($order['assigned_rider_id']) ? $this->getLatestRiderLocation((int)$order['assigned_rider_id']) : null;
        return ['order' => $order, 'rider_location' => $latest];
    }

    public function getTrackingTimeline(int $orderId): array
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT status, actor_type, actor_id, notes, created_at FROM order_status_history WHERE order_id=:id ORDER BY id ASC');
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll();
    }
}
