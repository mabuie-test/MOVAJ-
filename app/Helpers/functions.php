<?php

declare(strict_types=1);

use App\Core\Session;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    Session::start();
    $token = Session::get('_csrf_token');
    if (!$token) {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
    }

    return $token;
}
