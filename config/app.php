<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'MovaJá',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOL),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
];
