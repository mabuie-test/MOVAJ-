<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$count = $db->query("SELECT COUNT(*) FROM orders WHERE payout_status IN ('payout_pending','payout_processing')")->fetchColumn();
echo "Pending payouts: {$count}\n";
