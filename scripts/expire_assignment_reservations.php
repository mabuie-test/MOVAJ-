<?php
require __DIR__ . '/../bootstrap.php';
$dispatch = new App\Services\DispatchService();
$db = App\Core\Database::connection();
$orders = $db->query("SELECT DISTINCT order_id FROM order_dispatch_attempts WHERE dispatch_status='reserved'")->fetchAll();
$expired = 0;
foreach ($orders as $o) {
  if ($dispatch->releaseExpiredAssignmentReservation((int)$o['order_id'])) { $expired++; }
}
echo "Expired reservations released: {$expired}\n";
