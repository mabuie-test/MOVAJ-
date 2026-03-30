<?php

declare(strict_types=1);

namespace App\Repositories;

class ReportRepository extends BaseRepository
{
    public function dailyRevenue(string $from, string $to): array
    {
        $stmt = $this->db->prepare("SELECT DATE(created_at) as period, SUM(price_total) as total FROM orders WHERE payment_status='paid' AND DATE(created_at) BETWEEN :from AND :to GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public function topMerchants(string $from, string $to, int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT m.id,m.business_name,COUNT(o.id) total_orders,SUM(o.price_total) total_spent FROM merchants m JOIN orders o ON o.merchant_id=m.id WHERE DATE(o.created_at) BETWEEN :from AND :to GROUP BY m.id,m.business_name ORDER BY total_spent DESC LIMIT {$limit}");
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }
}
