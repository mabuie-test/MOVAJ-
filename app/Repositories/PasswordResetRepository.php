<?php

declare(strict_types=1);

namespace App\Repositories;

class PasswordResetRepository extends BaseRepository
{
    public function create(string $userType, string $email, string $token, string $expiresAt): void
    {
        $stmt = $this->db->prepare('INSERT INTO password_resets (user_type, email, token, expires_at) VALUES (:user_type, :email, :token, :expires_at)');
        $stmt->execute([
            'user_type' => $userType,
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE token = :token AND expires_at >= NOW() ORDER BY id DESC LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function deleteByToken(string $token): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE token = :token');
        $stmt->execute(['token' => $token]);
    }

    public function deleteExpired(): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE expires_at < NOW()');
        $stmt->execute();
    }
}
