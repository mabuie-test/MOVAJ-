<?php

declare(strict_types=1);

namespace App\Repositories;

class ProofRepository extends BaseRepository
{
    public function upsert(array $data): void
    {
        $sql = 'INSERT INTO delivery_proofs (order_id,rider_id,recipient_name,delivery_photo_path,recipient_signature_path,otp_validated,delivered_at,notes)
                VALUES (:order_id,:rider_id,:recipient_name,:delivery_photo_path,:recipient_signature_path,:otp_validated,:delivered_at,:notes)
                ON DUPLICATE KEY UPDATE recipient_name=VALUES(recipient_name),delivery_photo_path=VALUES(delivery_photo_path),recipient_signature_path=VALUES(recipient_signature_path),otp_validated=VALUES(otp_validated),delivered_at=VALUES(delivered_at),notes=VALUES(notes)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    public function findByOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM delivery_proofs WHERE order_id=:order_id LIMIT 1');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch() ?: null;
    }
}
