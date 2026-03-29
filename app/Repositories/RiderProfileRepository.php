<?php

declare(strict_types=1);

namespace App\Repositories;

class RiderProfileRepository extends BaseRepository
{
    public function findByRiderId(int $riderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rider_profiles WHERE rider_id=:rider_id LIMIT 1');
        $stmt->execute(['rider_id' => $riderId]);
        return $stmt->fetch() ?: null;
    }

    public function upsert(int $riderId, string $provider, string $walletNumber, ?string $zone): void
    {
        $sql = 'INSERT INTO rider_profiles (rider_id, wallet_provider, wallet_number, operating_zone) VALUES (:rider_id,:wallet_provider,:wallet_number,:operating_zone)
                ON DUPLICATE KEY UPDATE wallet_provider=VALUES(wallet_provider), wallet_number=VALUES(wallet_number), operating_zone=VALUES(operating_zone)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'rider_id' => $riderId,
            'wallet_provider' => $provider,
            'wallet_number' => $walletNumber,
            'operating_zone' => $zone,
        ]);
    }
}
