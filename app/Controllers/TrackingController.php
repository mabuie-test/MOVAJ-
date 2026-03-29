<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Services\DeliveryConfirmationService;
use App\Services\LiveTrackingService;
use App\Services\MapPresentationService;
use App\Services\PaymentService;
use App\Services\ProofOfDeliveryService;
use App\Services\SlaService;
use App\Services\WalletService;

class TrackingController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly DeliveryConfirmationService $delivery = new DeliveryConfirmationService(),
        private readonly PaymentService $payment = new PaymentService(),
        private readonly LiveTrackingService $tracking = new LiveTrackingService(),
        private readonly MapPresentationService $map = new MapPresentationService(),
        private readonly ProofOfDeliveryService $proofs = new ProofOfDeliveryService(),
        private readonly WalletService $wallet = new WalletService(),
        private readonly SlaService $sla = new SlaService(),
    ) {}

    public function show(Request $request, string $token): void
    {
        $order = $this->orders->findByTrackingToken($token);
        if (!$order) {
            Response::json(['error' => 'Pedido não encontrado'], 404);
            return;
        }
        $live = !empty($order['assigned_rider_id']) ? $this->tracking->getLatestRiderLocation((int)$order['assigned_rider_id']) : null;
        $this->view('home/tracking', ['order' => $order, 'mapPayload' => $this->map->orderMapPayload($order, $live), 'proof' => $this->proofs->generateProofSummary((int)$order['id'])]);
    }

    public function live(Request $request, string $token): void
    {
        $order = $this->orders->findByTrackingToken($token);
        if (!$order) {
            Response::json(['error' => 'Pedido não encontrado'], 404);
            return;
        }
        $live = !empty($order['assigned_rider_id']) ? $this->tracking->getLatestRiderLocation((int)$order['assigned_rider_id']) : null;
        $timeline = $this->tracking->getTrackingTimeline((int)$order['id']);

        Response::json(['order_status' => $order['delivery_status'], 'rider_location' => $live, 'timeline' => $timeline]);
    }

    public function submitOtp(Request $request, string $token): void
    {
        $order = $this->orders->findByTrackingToken($token);
        if (!$order || !$this->delivery->validateOtp($order, (string)$request->input('otp'))) {
            Response::json(['success' => false, 'message' => 'OTP inválido, expirado ou bloqueado por tentativas'], 422);
            return;
        }

        $this->delivery->markDelivered((int)$order['id'], (int)$order['assigned_rider_id']);
        $this->sla->computeOrderLifecycleMetrics((int)$order['id']);
        $this->wallet->creditRiderWalletFromOrder((int)$order['id']);

        if (!empty($order['assigned_rider_id'])) {
            try {
                $response = $this->delivery->triggerRiderPayout($order);
                (($response['status'] ?? '') === 'success')
                    ? $this->payment->markPayoutCompleted((int)$order['id'])
                    : $this->payment->markPayoutFailed((int)$order['id']);
            } catch (\Throwable) {
                $this->payment->markPayoutFailed((int)$order['id']);
            }
        }

        Response::json(['success' => true, 'message' => 'Entrega confirmada, wallet creditada e payout processado']);
    }
}
