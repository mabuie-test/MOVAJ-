<?php

declare(strict_types=1);

namespace App\Repositories;

class RiderRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO riders (name, email, phone, password_hash, city, zone, wallet_provider, bike_number, document_path, approval_status, id_number, id_issue_date, id_expiry_date, nuit, address_line, emergency_contact_phone, bi_front_path, bi_back_path, selfie_path) VALUES (:name,:email,:phone,:password_hash,:city,:zone,:wallet_provider,:bike_number,:document_path,:approval_status,:id_number,:id_issue_date,:id_expiry_date,:nuit,:address_line,:emergency_contact_phone,:bi_front_path,:bi_back_path,:selfie_path)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM riders WHERE email=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function updatePasswordByEmail(string $email, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE riders SET password_hash=:password_hash WHERE email=:email');
        $stmt->execute(['password_hash' => $passwordHash, 'email' => $email]);
    }

    public function listPending(int $limit = 20): array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, phone, city, zone, created_at FROM riders WHERE approval_status='pending' ORDER BY id DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function approve(int $riderId): void
    {
        $stmt = $this->db->prepare("UPDATE riders SET approval_status='approved' WHERE id=:id");
        $stmt->execute(['id' => $riderId]);
    }
}
