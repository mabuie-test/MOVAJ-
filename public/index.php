<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Middleware\CsrfMiddleware;

require dirname(__DIR__) . '/bootstrap.php';

Session::start();
$request = new Request();
(new CsrfMiddleware())->handle($request);

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
$router->dispatch($request);
