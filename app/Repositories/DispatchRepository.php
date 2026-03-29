<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class DispatchRepository extends BaseRepository
{
    public function eligibleRiders(array $order, float $radiusKm, int $maxActiveJobs): array
    {
        $sql = "SELECT r.id, r.name, r.city, rp.operating_zone, rp.wallet_provider, rp.wallet_number,
                rl.lat, rl.lng,
                (SELECT COUNT(*) FROM orders o WHERE o.assigned_rider_id=r.id AND o.delivery_status IN ('accepted','picked_up','in_transit','near_destination','arrived')) active_jobs
                FROM riders r
                JOIN rider_profiles rp ON rp.rider_id=r.id
                LEFT JOIN rider_locations rl ON rl.id = (SELECT id FROM rider_locations x WHERE x.rider_id=r.id ORDER BY x.id DESC LIMIT 1)
                WHERE r.approval_status='approved' AND r.city=:city AND rp.is_online=1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $order['city']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_values(array_filter($rows, function (array $r) use ($order, $radiusKm, $maxActiveJobs) {
            if ((int)$r['active_jobs'] >= $maxActiveJobs || empty($r['lat']) || empty($r['lng'])) {
                return false;
            }
            $distance = $this->haversine((float)$order['pickup_lat'], (float)$order['pickup_lng'], (float)$r['lat'], (float)$r['lng']);
            return $distance <= $radiusKm;
        }));
    }

    public function createAttempt(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO order_dispatch_attempts (order_id,rider_id,distance_to_pickup_km,rank_score,dispatch_status,reserved_until,responded_at) VALUES (:order_id,:rider_id,:distance_to_pickup_km,:rank_score,:dispatch_status,:reserved_until,:responded_at)');
        $stmt->execute($data);
    }

    public function latestReservation(int $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM order_dispatch_attempts WHERE order_id=:order_id AND dispatch_status='reserved' ORDER BY id DESC LIMIT 1");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
