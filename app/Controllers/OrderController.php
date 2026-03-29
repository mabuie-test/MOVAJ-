<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Services\PaymentService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $ordersService = new OrderService(),
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly PaymentService $payment = new PaymentService(),
    ) {}

    public function index(Request $request): void
    {
        Session::start();
        $merchantId = (int)Session::get('merchant_id', 0);
        $orders = $merchantId ? $this->orders->listByMerchant($merchantId) : [];
        $this->view('merchant/orders/index', ['orders' => $orders]);
    }

    public function create(Request $request): void { $this->view('merchant/orders/create'); }

    public function quote(Request $request): void
    {
        try {
            $quote = $this->ordersService->quote($request->all());
            Response::json($quote);
        } catch (\Throwable $e) {
            Response::json(['error' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request): void
    {
        Session::start();
        $merchantId = (int)Session::get('merchant_id', 0);
        if ($merchantId === 0) {
            Response::json(['error' => 'Autenticação do merchant obrigatória'], 401);
            return;
        }

        try {
            $input = $request->all();
            $quote = $this->ordersService->quote($input);
            $orderId = $this->ordersService->createFromQuote($merchantId, $input, $quote);
            Response::json(['order_id' => $orderId, 'message' => 'Pedido criado e pendente de pagamento']);
        } catch (\Throwable $e) {
            Response::json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, string $id): void
    {
        $this->view('merchant/orders/show', ['order' => $this->orders->findById((int)$id)]);
    }

    public function pay(Request $request, string $id): void
    {
        Session::start();
        $order = $this->orders->findById((int)$id);
        if (!$order) {
            Response::json(['error' => 'Pedido não encontrado'], 404);
            return;
        }

        $provider = (string)$request->input('provider', 'mpesa');
        $payload = [
            'external_reference' => 'order-' . $order['id'],
            'customer_msisdn' => $this->payment->normalizePhone((string)$request->input('phone')),
            'amount' => (float)$order['price_total'],
            'description' => 'Pagamento entrega order #' . $order['id'],
        ];

        $response = $provider === 'emola'
            ? $this->payment->initiateMerchantEmolaPayment($payload)
            : $this->payment->initiateMerchantMpesaPayment($payload);

        $this->payment->persistGatewayResponse([
            'order_id' => $order['id'],
            'merchant_id' => $order['merchant_id'],
            'debito_reference' => $response['reference'] ?? null,
            'provider' => $provider,
            'payment_type' => 'c2b',
            'request_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'raw_response' => json_encode($response, JSON_THROW_ON_ERROR),
            'amount' => (float)$order['price_total'],
            'status' => $response['status'] ?? 'processing',
        ]);

        (($response['status'] ?? '') === 'success') ? $this->payment->markPaymentCompleted((int)$id) : $this->payment->markPaymentFailed((int)$id);

        Response::json(['message' => 'Processamento de pagamento iniciado', 'gateway' => $response]);
    }
}
