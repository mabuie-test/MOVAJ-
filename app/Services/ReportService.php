<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\ReportRepository;

class ReportService
{
    public function __construct(private readonly ReportRepository $reports = new ReportRepository()) {}

    public function dashboardKpis(): array
    {
        $db = Database::connection();
        $queries = [
            'total_orders' => 'SELECT COUNT(*) FROM orders',
            'orders_today' => 'SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURRENT_DATE',
            'delivered_orders' => "SELECT COUNT(*) FROM orders WHERE delivery_status='delivered'",
            'in_transit_orders' => "SELECT COUNT(*) FROM orders WHERE delivery_status='in_transit'",
            'canceled_orders' => "SELECT COUNT(*) FROM orders WHERE delivery_status='canceled'",
            'total_revenue' => 'SELECT COALESCE(SUM(price_total),0) FROM orders WHERE payment_status="paid"',
            'total_commission' => 'SELECT COALESCE(SUM(platform_fee),0) FROM orders WHERE payment_status="paid"',
            'total_payouts' => 'SELECT COALESCE(SUM(amount),0) FROM payouts WHERE status="success"',
            'failed_payouts' => 'SELECT COUNT(*) FROM payouts WHERE status="failed"',
            'active_merchants' => 'SELECT COUNT(*) FROM merchants WHERE is_active=1',
            'active_riders' => "SELECT COUNT(*) FROM riders WHERE approval_status='approved'",
        ];

        $result = [];
        foreach ($queries as $key => $sql) {
            $result[$key] = (float)$db->query($sql)->fetchColumn();
        }

        return $result;
    }

    public function operationalSummary(): array
    {
        $from = date('Y-m-01');
        $to = date('Y-m-d');

        return [
            'daily_revenue' => $this->reports->dailyRevenue($from, $to),
            'top_merchants' => $this->reports->topMerchants($from, $to),
        ];
    }
}
