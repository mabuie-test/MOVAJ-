<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function ensure(string $guard): void
    {
        Session::start();
        if (!Session::get($guard . '_id')) {
            Response::redirect('/login');
        }
    }
}
