<?php

declare(strict_types=1);

namespace App\Repositories;

class AdminRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE email=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function updatePasswordByEmail(string $email, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE admins SET password_hash=:password_hash WHERE email=:email');
        $stmt->execute(['password_hash' => $passwordHash, 'email' => $email]);
    }
}
