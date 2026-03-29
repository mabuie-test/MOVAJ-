<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PayoutRepository;

class PaymentService
{
    public function __construct(
        private readonly HttpClientService $http = new HttpClientService(),
        private readonly PaymentRepository $payments = new PaymentRepository(),
        private readonly PayoutRepository $payouts = new PayoutRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
    ) {}

    public function initiateMerchantMpesaPayment(array $payload): array
    {
        return $this->debitRequest('/wallets/' . Env::get('DEBITO_WALLET_ID') . '/c2b/mpesa', $payload, 'mpesa', 'c2b');
    }

    public function initiateMerchantEmolaPayment(array $payload): array
    {
        return $this->debitRequest('/wallets/' . Env::get('DEBITO_WALLET_ID') . '/c2b/emola', $payload, 'emola', 'c2b');
    }

    public function checkTransactionStatus(string $reference): array
    {
        $client = $this->http->client(['base_uri' => Env::get('DEBITO_BASE_URL')]);
        $res = $client->get('/transactions/' . $reference . '/status', ['headers' => $this->headers()]);
        return json_decode((string)$res->getBody(), true);
    }

    public function initiateRiderMpesaPayout(array $payload): array
    {
        return $this->debitRequest('/wallets/' . Env::get('DEBITO_WALLET_ID') . '/b2c/mpesa', $payload, 'mpesa', 'b2c');
    }

    public function initiateRiderEmolaPayout(array $payload): array
    {
        return $this->debitRequest('/wallets/' . Env::get('DEBITO_WALLET_ID') . '/b2c/emola', $payload, 'emola', 'b2c');
    }

    public function markPaymentCompleted(int $orderId): void
    {
        $this->orders->updateStatuses($orderId, 'paid', 'awaiting_assignment', 'payout_pending');
        $this->orders->addHistory($orderId, 'paid', 'system', 0, 'Pagamento confirmado via Débito API');
    }

    public function markPaymentFailed(int $orderId): void
    {
        $this->orders->updateStatuses($orderId, 'payment_failed', 'failed', 'payout_pending');
        $this->orders->addHistory($orderId, 'failed', 'system', 0, 'Falha de pagamento');
    }

    public function markPayoutCompleted(int $orderId): void
    {
        $this->orders->updateStatuses($orderId, 'paid', 'delivered', 'payout_completed');
        $this->orders->addHistory($orderId, 'payout_completed', 'system', 0, 'Payout concluído');
    }

    public function markPayoutFailed(int $orderId): void
    {
        $this->orders->updateStatuses($orderId, 'paid', 'delivered', 'payout_failed');
        $this->orders->addHistory($orderId, 'payout_failed', 'system', 0, 'Falha no payout');
    }

    public function persistGatewayResponse(array $record, bool $payout = false): int
    {
        return $payout ? $this->payouts->create($record) : $this->payments->create($record);
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        return str_starts_with($digits, '258') ? $digits : '258' . ltrim($digits, '0');
    }

    private function debitRequest(string $endpoint, array $payload, string $provider, string $paymentType): array
    {
        $client = $this->http->client(['base_uri' => Env::get('DEBITO_BASE_URL')]);
        $res = $client->post($endpoint, ['headers' => $this->headers(), 'json' => $payload]);
        $json = json_decode((string)$res->getBody(), true);
        $json['provider'] = $provider;
        $json['payment_type'] = $paymentType;
        return $json;
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . Env::get('DEBITO_TOKEN'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
