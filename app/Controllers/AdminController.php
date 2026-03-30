<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Repositories\OrderRepository;
use App\Repositories\RiderRepository;
use App\Services\AuthService;
use App\Services\DispatchService;
use App\Services\ReportService;

class AdminController extends Controller
{
    public function __construct(
        private readonly ReportService $reports = new ReportService(),
        private readonly RiderRepository $riders = new RiderRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly DispatchService $dispatch = new DispatchService(),
        private readonly AuthMiddleware $auth = new AuthMiddleware(),
        private readonly AuthService $authService = new AuthService(),
    ) {}

    public function loginForm(Request $request): void
    {
        Session::start();
        if (Session::get('admin_id')) {
            Response::redirect('/admin');
            return;
        }

        $error = $request->input('error');
        $this->view('auth/admin_login', ['error' => $error]);
    }

    public function login(Request $request): void
    {
        $email = (string)$request->input('email');
        $password = (string)$request->input('password');

        if (!$this->authService->attemptAdminLogin($email, $password)) {
            $this->view('auth/admin_login', ['error' => 'Credenciais de administrador inválidas.']);
            return;
        }

        Response::redirect('/admin');
    }

    public function dashboard(Request $request): void
    {
        $this->auth->ensure('admin');
        $this->view('admin/dashboard', ['kpis' => $this->reports->dashboardKpis(), 'activeOrders' => $this->orders->activeOrdersForMap(), 'pendingRiders' => $this->riders->listPending(30)]);
    }

    public function orders(Request $request): void { $this->auth->ensure('admin'); $this->view('admin/orders'); }
    public function payments(Request $request): void { $this->auth->ensure('admin'); $this->view('admin/payments'); }
    public function payouts(Request $request): void { $this->auth->ensure('admin'); $this->view('admin/payouts'); }

    public function reports(Request $request): void
    {
        $this->auth->ensure('admin');
        $this->view('admin/reports', ['report' => $this->reports->operationalSummary()]);
    }

    public function approveRider(Request $request, string $id): void
    {
        $this->auth->ensure('admin');
        $this->riders->approve((int)$id);
        Response::json(['approved' => true, 'rider_id' => $id]);
    }

    public function dispatchOrder(Request $request, string $id): void
    {
        $this->auth->ensure('admin');
        $ok = $this->dispatch->autoAssignOrder((int)$id);
        Response::json(['dispatched' => $ok, 'order_id' => $id]);
    }

    public function interveneOrder(Request $request, string $id): void
    {
        $this->auth->ensure('admin');
        Response::json(['intervened' => true, 'order_id' => $id]);
    }
}
