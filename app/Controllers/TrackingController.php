<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\OrderRepository;
use App\Services\DeliveryConfirmationService;
use App\Services\PaymentService;

class TrackingController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly DeliveryConfirmationService $delivery = new DeliveryConfirmationService(),
        private readonly PaymentService $payment = new PaymentService(),
    ) {}

    public function show(Request $request, string $token): void
    {
        $order = $this->orders->findByTrackingToken($token);
        if (!$order) {
            Response::json(['error' => 'Pedido não encontrado'], 404);
            return;
        }

        $this->view('home/tracking', ['order' => $order]);
    }

    public function submitOtp(Request $request, string $token): void
    {
        $order = $this->orders->findByTrackingToken($token);
        if (!$order || !$this->delivery->validateOtp($order, (string)$request->input('otp'))) {
            Response::json(['success' => false, 'message' => 'OTP inválido, expirado ou bloqueado por tentativas'], 422);
            return;
        }

        $this->delivery->markDelivered((int)$order['id'], (int)$order['assigned_rider_id']);

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

        Response::json(['success' => true, 'message' => 'Entrega confirmada e payout processado']);
    }
}
