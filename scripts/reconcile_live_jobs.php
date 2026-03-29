<?php
require __DIR__ . '/../bootstrap.php';
$dispatch = new App\Services\DispatchService();
$db = App\Core\Database::connection();
$orders = $db->query("SELECT id FROM orders WHERE delivery_status='awaiting_assignment' AND payment_status='paid' LIMIT 100")->fetchAll();
$count = 0;
foreach ($orders as $o) { if ($dispatch->autoAssignOrder((int)$o['id'])) $count++; }
echo "Live jobs dispatched: {$count}\n";
