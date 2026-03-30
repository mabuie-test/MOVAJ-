<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$count = $db->query("SELECT COUNT(*) FROM rider_wallet_payout_requests WHERE status IN ('pending','processing')")->fetchColumn();
echo "Pending wallet payouts: {$count}\n";
