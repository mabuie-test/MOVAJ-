<?php
require __DIR__ . '/../bootstrap.php';

$service = new App\Services\OrderService();
$affected = $service->autoExpireUnassignedOrders();
echo "Reassigned stale orders: {$affected}\n";
