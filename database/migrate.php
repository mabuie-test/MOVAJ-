<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
$sql = file_get_contents(__DIR__ . '/migrations/001_create_tables.sql');
$db->exec($sql);
echo "Migrations executadas.\n";
