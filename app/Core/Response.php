<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_THROW_ON_ERROR);
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
