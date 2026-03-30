<?php

declare(strict_types=1);

namespace App\Policies;

class OrderPolicy
{
    public function canMerchantView(array $order, int $merchantId): bool
    {
        return (int)$order['merchant_id'] === $merchantId;
    }

    public function canRiderAccept(array $order, int $riderId): bool
    {
        return in_array($order['delivery_status'], ['awaiting_assignment', 'assigned'], true)
            && ((int)($order['assigned_rider_id'] ?? 0) === 0 || (int)$order['assigned_rider_id'] === $riderId);
    }
}
