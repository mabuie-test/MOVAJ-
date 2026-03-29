<?php
require __DIR__ . '/../bootstrap.php';
$sla = new App\Services\SlaService();
$db = App\Core\Database::connection();
$orders = $db->query("SELECT id FROM orders WHERE delivered_at IS NOT NULL AND DATE(delivered_at)=CURRENT_DATE")->fetchAll();
$rows = [];
foreach ($orders as $o) { $rows[] = $sla->computeOrderLifecycleMetrics((int)$o['id']); }
file_put_contents(__DIR__ . '/../storage/exports/sla_daily_' . date('Ymd') . '.json', json_encode($rows, JSON_PRETTY_PRINT));
echo "SLA daily metrics generated: " . count($rows) . " orders\n";
