<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;
use App\Repositories\OtpRepository;
use App\Repositories\RiderProfileRepository;

class DeliveryConfirmationService
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly OtpRepository $otps = new OtpRepository(),
        private readonly RiderProfileRepository $riderProfiles = new RiderProfileRepository(),
        private readonly PaymentService $payment = new PaymentService(),
    ) {}

    public function generateOtpForOrder(int $orderId): string
    {
        $otp = (string)random_int(100000, 999999);
        $expires = (new \DateTimeImmutable('+' . Env::get('OTP_EXPIRY_MINUTES', 30) . ' minutes'))->format('Y-m-d H:i:s');
        return $otp . '|' . $expires;
    }

    public function initializeOtp(int $orderId, string $otp, string $expiresAt): void
    {
        $this->otps->create($orderId, $otp, $expiresAt, 5);
    }

    public function validateOtp(array $order, string $otp): bool
    {
        $otpRow = $this->otps->latestByOrder((int)$order['id']);
        if (!$otpRow || $otpRow['verified_at']) {
            return false;
        }

        if ((int)$otpRow['attempts'] >= (int)$otpRow['max_attempts']) {
            return false;
        }

        if (strtotime((string)$otpRow['expires_at']) < time()) {
            return false;
        }

        $valid = hash_equals((string)$otpRow['otp_code'], trim($otp));
        if (!$valid) {
            $this->otps->incrementAttempt((int)$otpRow['id']);
            return false;
        }

        $this->otps->markVerified((int)$otpRow['id']);
        return true;
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

    public function triggerRiderPayout(array $order): array
    {
        $profile = $this->riderProfiles->findByRiderId((int)$order['assigned_rider_id']);
        if (!$profile) {
            throw new \RuntimeException('Rider profile não encontrado para payout.');
        }

        $payload = [
            'external_reference' => 'order-' . $order['id'] . '-payout',
            'customer_msisdn' => $this->payment->normalizePhone((string)$profile['wallet_number']),
            'amount' => (float)$order['rider_payout'],
            'description' => 'Payout rider order #' . $order['id'],
        ];

        $provider = (string)$profile['wallet_provider'];
        $response = $provider === 'emola'
            ? $this->payment->initiateRiderEmolaPayout($payload)
            : $this->payment->initiateRiderMpesaPayout($payload);

        $this->payment->persistGatewayResponse([
            'order_id' => $order['id'],
            'rider_id' => $order['assigned_rider_id'],
            'debito_reference' => $response['reference'] ?? null,
            'provider' => $provider,
            'payment_type' => 'b2c',
            'request_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'raw_response' => json_encode($response, JSON_THROW_ON_ERROR),
            'amount' => (float)$order['rider_payout'],
            'status' => $response['status'] ?? 'processing',
        ], true);

        return $response;
    }
}
