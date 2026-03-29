<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$count = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status IN ('payment_processing','pending_payment')")->fetchColumn();
echo "Pending payments: {$count}\n";
