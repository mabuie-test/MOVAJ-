<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        Session::start();
        $sessionToken = Session::get('_csrf_token');
        $inputToken = $request->input('_token');

        if (!$sessionToken || !$inputToken || !hash_equals($sessionToken, $inputToken)) {
            http_response_code(419);
            exit('CSRF token inválido.');
        }
    }
}
