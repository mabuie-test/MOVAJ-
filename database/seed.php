<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$sql = file_get_contents(__DIR__ . '/seeders/001_seed_base.sql');
$db->exec($sql);
echo "Seeds executados.\n";
