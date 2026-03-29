<?php

declare(strict_types=1);

namespace App\Repositories;

class RiderLocationRepository extends BaseRepository
{
    public function insert(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO rider_locations (rider_id, order_id, lat, lng, heading, speed, accuracy, source) VALUES (:rider_id,:order_id,:lat,:lng,:heading,:speed,:accuracy,:source)');
        $stmt->execute($data);
    }

    public function latestByRider(int $riderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rider_locations WHERE rider_id=:rider_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['rider_id' => $riderId]);
        return $stmt->fetch() ?: null;
    }
}
