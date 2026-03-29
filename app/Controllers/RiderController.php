<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Policies\OrderPolicy;
use App\Repositories\OrderRepository;

class RiderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly AuthMiddleware $auth = new AuthMiddleware(),
        private readonly OrderPolicy $policy = new OrderPolicy(),
    ) {}

    public function dashboard(Request $request): void
    {
        $this->auth->ensure('rider');
        $this->view('rider/dashboard');
    }

    public function jobs(Request $request): void
    {
        $this->auth->ensure('rider');
        $jobs = $this->orders->availableJobs((string)Session::get('rider_city', 'Maputo'));
        $this->view('rider/jobs/index', ['jobs' => $jobs]);
    }

    public function jobShow(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        $this->view('rider/jobs/show', ['order' => $this->orders->findById((int)$id)]);
    }

    public function accept(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        $riderId = (int)Session::get('rider_id', 0);
        $order = $this->orders->findById((int)$id);
        if (!$order || !$this->policy->canRiderAccept($order, $riderId)) {
            Response::json(['error' => 'Pedido não elegível para aceite'], 422);
            return;
        }

        $this->orders->assignRider((int)$id, $riderId);
        $this->orders->addHistory((int)$id, 'accepted', 'rider', $riderId, 'Pedido aceite na fila operacional');
        Response::json(['message' => 'Pedido aceite', 'id' => $id]);
    }

    public function updateStatus(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        $status = (string)$request->input('status', 'in_transit');
        $allowed = ['pickup_arrived', 'picked_up', 'in_transit', 'near_destination', 'arrived'];
        if (!in_array($status, $allowed, true)) {
            Response::json(['error' => 'Status operacional inválido'], 422);
            return;
        }

        $this->orders->updateDeliveryStatus((int)$id, $status);
        $this->orders->addHistory((int)$id, $status, 'rider', (int)Session::get('rider_id', 0), 'Atualização operacional');
        Response::json(['message' => 'Status atualizado', 'status' => $status, 'id' => $id]);
    }

    public function deliver(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        Response::json(['message' => 'Solicite OTP ao destinatário para confirmar entrega.', 'id' => $id]);
    }

    public function earnings(Request $request): void
    {
        $this->auth->ensure('rider');
        $this->view('rider/earnings');
    }
}
