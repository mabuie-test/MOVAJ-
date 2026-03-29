<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;

class DeliveryConfirmationService
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly PaymentService $payment = new PaymentService(),
    ) {}

    public function generateOtpForOrder(int $orderId): string
    {
        $otp = (string)random_int(100000, 999999);
        $expires = (new \DateTimeImmutable('+' . Env::get('OTP_EXPIRY_MINUTES', 30) . ' minutes'))->format('Y-m-d H:i:s');
        return $otp . '|' . $expires;
    }

    public function validateOtp(array $order, string $otp): bool
    {
        if (!isset($order['otp_code'], $order['otp_expires_at'])) {
            return false;
        }

        return hash_equals((string)$order['otp_code'], trim($otp)) && strtotime((string)$order['otp_expires_at']) >= time();
    }

    public function markDelivered(int $orderId, int $riderId): void
    {
        $this->orders->updateDeliveryStatus($orderId, 'delivered');
        $this->orders->addHistory($orderId, 'delivered', 'rider', $riderId, 'Entrega confirmada com OTP válido');
    }

    public function attachProofOfDelivery(int $orderId, string $path): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('UPDATE orders SET proof_of_delivery_path=:path WHERE id=:id');
        $stmt->execute(['id' => $orderId, 'path' => $path]);
    }

    public function triggerRiderPayout(array $order, string $walletProvider, string $walletNumber): array
    {
        $payload = [
            'external_reference' => 'order-' . $order['id'] . '-payout',
            'customer_msisdn' => $this->payment->normalizePhone($walletNumber),
            'amount' => (float)$order['rider_payout'],
            'description' => 'Payout rider order #' . $order['id'],
        ];

        $response = $walletProvider === 'emola'
            ? $this->payment->initiateRiderEmolaPayout($payload)
            : $this->payment->initiateRiderMpesaPayout($payload);

        $this->payment->persistGatewayResponse([
            'order_id' => $order['id'],
            'rider_id' => $order['assigned_rider_id'],
            'debito_reference' => $response['reference'] ?? null,
            'provider' => $walletProvider,
            'payment_type' => 'b2c',
            'request_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'raw_response' => json_encode($response, JSON_THROW_ON_ERROR),
            'amount' => (float)$order['rider_payout'],
            'status' => $response['status'] ?? 'processing',
        ], true);

        return $response;
    }
}
