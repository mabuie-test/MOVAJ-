<?php
require __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::connection();
foreach (glob(__DIR__ . '/migrations/*.sql') as $migration) {
    $db->exec(file_get_contents($migration));
    echo "Executed: " . basename($migration) . PHP_EOL;
}
