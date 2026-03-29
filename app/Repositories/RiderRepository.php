<?php

declare(strict_types=1);

namespace App\Repositories;

class RiderRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO riders (name, email, phone, password_hash, city, zone, wallet_provider, bike_number, document_path, approval_status) VALUES (:name,:email,:phone,:password_hash,:city,:zone,:wallet_provider,:bike_number,:document_path,:approval_status)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM riders WHERE email=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function approve(int $riderId): void
    {
        $stmt = $this->db->prepare("UPDATE riders SET approval_status='approved' WHERE id=:id");
        $stmt->execute(['id' => $riderId]);
    }
}
