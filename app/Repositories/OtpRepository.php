<?php

declare(strict_types=1);

namespace App\Repositories;

class OtpRepository extends BaseRepository
{
    public function create(int $orderId, string $otpCode, string $expiresAt, int $maxAttempts = 5): void
    {
        $stmt = $this->db->prepare('INSERT INTO otp_confirmations (order_id, otp_code, attempts, max_attempts, expires_at) VALUES (:order_id,:otp_code,0,:max_attempts,:expires_at)');
        $stmt->execute([
            'order_id' => $orderId,
            'otp_code' => $otpCode,
            'max_attempts' => $maxAttempts,
            'expires_at' => $expiresAt,
        ]);
    }

    public function latestByOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM otp_confirmations WHERE order_id=:order_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    public function incrementAttempt(int $otpId): void
    {
        $stmt = $this->db->prepare('UPDATE otp_confirmations SET attempts=attempts+1 WHERE id=:id');
        $stmt->execute(['id' => $otpId]);
    }

    public function markVerified(int $otpId): void
    {
        $stmt = $this->db->prepare('UPDATE otp_confirmations SET verified_at=NOW() WHERE id=:id');
        $stmt->execute(['id' => $otpId]);
    }
}
