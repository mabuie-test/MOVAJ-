<?php

declare(strict_types=1);

namespace App\Repositories;

class PaymentRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO payments (order_id,merchant_id,debito_reference,provider,payment_type,request_payload,raw_response,amount,status) VALUES (:order_id,:merchant_id,:debito_reference,:provider,:payment_type,:request_payload,:raw_response,:amount,:status)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }
}
