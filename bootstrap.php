<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
} else {
    Dotenv::createImmutable(__DIR__, '.env.example')->safeLoad();
}

require __DIR__ . '/app/Helpers/functions.php';
