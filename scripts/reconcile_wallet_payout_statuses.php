<?php
require __DIR__ . '/../bootstrap.php';
$wallet = new App\Services\WalletService();
$wallet->syncWalletPayoutStatus();
echo "Wallet payout statuses synchronized.\n";
