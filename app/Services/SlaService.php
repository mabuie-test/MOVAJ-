<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class SlaService
{
    public function calculatePickupSla(array $order): array
    {
        if (empty($order['accepted_at']) || empty($order['picked_up_at'])) {
            return ['minutes' => null, 'status' => null];
        }
        $minutes = (int)((strtotime($order['picked_up_at']) - strtotime($order['accepted_at'])) / 60);
        return ['minutes' => $minutes, 'status' => $minutes <= 20 ? 'on_time' : ($minutes <= 40 ? 'delayed' : 'critical_delay')];
    }

    public function calculateDeliverySla(array $order): array
    {
        if (empty($order['picked_up_at']) || empty($order['delivered_at'])) {
            return ['minutes' => null, 'status' => null];
        }
        $minutes = (int)((strtotime($order['delivered_at']) - strtotime($order['picked_up_at'])) / 60);
        $eta = (int)$order['route_duration_minutes'];
        $delay = $minutes - $eta;
        return ['minutes' => $minutes, 'delay' => $delay, 'status' => $delay <= 10 ? 'on_time' : ($delay <= 30 ? 'delayed' : 'critical_delay')];
    }

    public function computeOrderLifecycleMetrics(int $orderId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM orders WHERE id=:id');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) return [];

        $pickup = $this->calculatePickupSla($order);
        $delivery = $this->calculateDeliverySla($order);
        $status = $delivery['status'] ?? $pickup['status'] ?? null;

        $upd = $db->prepare('UPDATE orders SET pickup_delay_minutes=:pickup_delay, delivery_delay_minutes=:delivery_delay, sla_status=:sla_status WHERE id=:id');
        $upd->execute([
            'id' => $orderId,
            'pickup_delay' => $pickup['minutes'],
            'delivery_delay' => $delivery['delay'] ?? null,
            'sla_status' => $status,
        ]);

        return ['pickup' => $pickup, 'delivery' => $delivery, 'sla_status' => $status];
    }

    public function detectLatePickup(int $orderId): bool
    {
        $metrics = $this->computeOrderLifecycleMetrics($orderId);
        return ($metrics['pickup']['status'] ?? null) !== 'on_time';
    }

    public function detectLateDelivery(int $orderId): bool
    {
        $metrics = $this->computeOrderLifecycleMetrics($orderId);
        return ($metrics['delivery']['status'] ?? null) !== 'on_time';
    }
}
