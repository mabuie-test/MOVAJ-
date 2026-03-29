<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\OrderRepository;

class RiderController extends Controller
{
    public function __construct(private readonly OrderRepository $orders = new OrderRepository()) {}

    public function dashboard(Request $request): void { $this->view('rider/dashboard'); }

    public function jobs(Request $request): void
    {
        Session::start();
        $jobs = $this->orders->availableJobs((string)Session::get('rider_city', 'Maputo'));
        $this->view('rider/jobs/index', ['jobs' => $jobs]);
    }

    public function jobShow(Request $request, string $id): void { $this->view('rider/jobs/show', ['order' => $this->orders->findById((int)$id)]); }

    public function accept(Request $request, string $id): void
    {
        Session::start();
        $riderId = (int)Session::get('rider_id', 0);
        $this->orders->assignRider((int)$id, $riderId);
        $this->orders->addHistory((int)$id, 'accepted', 'rider', $riderId, 'Pedido aceite na fila operacional');
        Response::json(['message' => 'Pedido aceite', 'id' => $id]);
    }

    public function updateStatus(Request $request, string $id): void
    {
        Session::start();
        $status = (string)$request->input('status', 'in_transit');
        $this->orders->updateDeliveryStatus((int)$id, $status);
        $this->orders->addHistory((int)$id, $status, 'rider', (int)Session::get('rider_id', 0), 'Atualização operacional');
        Response::json(['message' => 'Status atualizado', 'status' => $status, 'id' => $id]);
    }

    public function deliver(Request $request, string $id): void { Response::json(['message' => 'Use OTP no tracking para confirmação final.', 'id' => $id]); }
    public function earnings(Request $request): void { $this->view('rider/earnings'); }
}
