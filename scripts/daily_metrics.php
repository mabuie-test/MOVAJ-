<?php
require __DIR__ . '/../bootstrap.php';
$service = new App\Services\ReportService();
$kpis = $service->dashboardKpis();
file_put_contents(__DIR__ . '/../storage/exports/daily_metrics_' . date('Ymd') . '.json', json_encode($kpis, JSON_PRETTY_PRINT));
echo "Metrics exported.\n";
