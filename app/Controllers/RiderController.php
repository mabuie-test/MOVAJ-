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
use App\Services\LiveTrackingService;
use App\Services\ProofOfDeliveryService;
use App\Services\WalletService;

class RiderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly AuthMiddleware $auth = new AuthMiddleware(),
        private readonly OrderPolicy $policy = new OrderPolicy(),
        private readonly LiveTrackingService $tracking = new LiveTrackingService(),
        private readonly ProofOfDeliveryService $proofs = new ProofOfDeliveryService(),
        private readonly WalletService $wallet = new WalletService(),
    ) {}

    public function dashboard(Request $request): void
    {
        $this->auth->ensure('rider');
        $riderId = (int)Session::get('rider_id', 0);
        $this->view('rider/dashboard', [
            'available_balance' => $this->wallet->getAvailableBalance($riderId),
            'pending_balance' => $this->wallet->getPendingBalance($riderId),
        ]);
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
        $this->orders->updateStatusWithTimestamp((int)$id, 'accepted');
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

        $this->orders->updateStatusWithTimestamp((int)$id, $status);
        $this->orders->addHistory((int)$id, $status, 'rider', (int)Session::get('rider_id', 0), 'Atualização operacional');
        Response::json(['message' => 'Status atualizado', 'status' => $status, 'id' => $id]);
    }

    public function updateLocation(Request $request): void
    {
        $this->auth->ensure('rider');
        $ok = $this->tracking->updateRiderLocation(
            (int)Session::get('rider_id', 0),
            (float)$request->input('lat'),
            (float)$request->input('lng'),
            $request->input('order_id') ? (int)$request->input('order_id') : null,
            $request->input('heading') ? (float)$request->input('heading') : null,
            $request->input('speed') ? (float)$request->input('speed') : null,
            $request->input('accuracy') ? (float)$request->input('accuracy') : null,
        );

        Response::json(['success' => $ok]);
    }

    public function finalizeDelivery(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        $riderId = (int)Session::get('rider_id', 0);

        $photo = $this->proofs->storeDeliveryPhoto($_FILES['delivery_photo'] ?? []);
        $signature = $this->proofs->storeRecipientSignature((string)$request->input('recipient_signature', ''));
        $this->proofs->attachProofToOrder((int)$id, $riderId, $request->input('recipient_name'), $photo, $signature, true, $request->input('notes'));

        Response::json(['success' => true, 'message' => 'Prova de entrega anexada']);
    }

    public function deliver(Request $request, string $id): void
    {
        $this->auth->ensure('rider');
        Response::json(['message' => 'Solicite OTP ao destinatário para confirmar entrega.', 'id' => $id]);
    }

    public function earnings(Request $request): void
    {
        $this->auth->ensure('rider');
        $riderId = (int)Session::get('rider_id', 0);
        $this->view('rider/earnings', [
            'available_balance' => $this->wallet->getAvailableBalance($riderId),
            'pending_balance' => $this->wallet->getPendingBalance($riderId),
        ]);
    }
}
