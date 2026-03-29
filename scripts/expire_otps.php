<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$affected = $db->exec("UPDATE orders SET otp_code=NULL WHERE otp_expires_at < NOW() AND delivery_status <> 'delivered'");
echo "Expired OTPs cleaned: {$affected}\n";
