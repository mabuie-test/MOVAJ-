<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\MerchantController;
use App\Controllers\OrderController;
use App\Controllers\RiderController;
use App\Controllers\TrackingController;
use App\Core\Router;

/** @var Router $router */
$router->get('/', [HomeController::class, 'index']);
$router->get('/track/{token}', [TrackingController::class, 'show']);
$router->post('/track/{token}/otp', [TrackingController::class, 'submitOtp']);

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register/merchant', [AuthController::class, 'registerMerchantForm']);
$router->post('/register/merchant', [AuthController::class, 'registerMerchant']);
$router->get('/register/rider', [AuthController::class, 'registerRiderForm']);
$router->post('/register/rider', [AuthController::class, 'registerRider']);
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

$router->get('/merchant/dashboard', [MerchantController::class, 'dashboard']);
$router->get('/merchant/orders', [OrderController::class, 'index']);
$router->get('/merchant/orders/create', [OrderController::class, 'create']);
$router->post('/merchant/orders/quote', [OrderController::class, 'quote']);
$router->post('/merchant/orders', [OrderController::class, 'store']);
$router->get('/merchant/orders/{id}', [OrderController::class, 'show']);
$router->post('/merchant/orders/{id}/pay', [OrderController::class, 'pay']);
$router->get('/merchant/reports', [MerchantController::class, 'reports']);

$router->get('/rider/dashboard', [RiderController::class, 'dashboard']);
$router->get('/rider/jobs', [RiderController::class, 'jobs']);
$router->get('/rider/jobs/{id}', [RiderController::class, 'jobShow']);
$router->post('/rider/jobs/{id}/accept', [RiderController::class, 'accept']);
$router->post('/rider/jobs/{id}/status', [RiderController::class, 'updateStatus']);
$router->post('/rider/jobs/{id}/deliver', [RiderController::class, 'deliver']);
$router->get('/rider/earnings', [RiderController::class, 'earnings']);

$router->get('/admin/login', [AdminController::class, 'loginForm']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/payments', [AdminController::class, 'payments']);
$router->get('/admin/payouts', [AdminController::class, 'payouts']);
$router->get('/admin/reports', [AdminController::class, 'reports']);
$router->post('/admin/riders/{id}/approve', [AdminController::class, 'approveRider']);
$router->post('/admin/orders/{id}/intervene', [AdminController::class, 'interveneOrder']);
