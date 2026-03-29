<?php

declare(strict_types=1);

namespace App\Repositories;

class MerchantRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO merchants (business_name, owner_name, email, phone, password_hash, city) VALUES (:business_name,:owner_name,:email,:phone,:password_hash,:city)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM merchants WHERE email=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}
