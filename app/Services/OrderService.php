<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;
use Ramsey\Uuid\Uuid;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly RouteService $routeService = new RouteService(),
        private readonly PricingService $pricingService = new PricingService(),
        private readonly DeliveryConfirmationService $otpService = new DeliveryConfirmationService(),
    ) {}

    public function quote(array $input): array
    {
        $city = $input['city'];
        if (!$this->routeService->validateAddressWithinCoverage($city)) {
            throw new \RuntimeException('Cidade fora da cobertura operacional.');
        }

        $hasPickupCoordinates = isset($input['pickup_lat'], $input['pickup_lng'])
            && is_numeric($input['pickup_lat'])
            && is_numeric($input['pickup_lng']);

        $pickup = $hasPickupCoordinates
            ? [
                'lat' => (float)$input['pickup_lat'],
                'lng' => (float)$input['pickup_lng'],
                'confidence' => 1.0,
                'display_name' => $input['pickup_address'] ?: 'Minha localização atual',
            ]
            : $this->routeService->geocodeAddress($input['pickup_address'], $city);

        $dropoff = $this->routeService->geocodeAddress($input['dropoff_address'], $city);
        $route = $this->routeService->calculateRoute($pickup, $dropoff);
        $pricing = $this->pricingService->quoteOrder((float)$route['distance_km'], !empty($input['urgent']), $input['package_type'] ?? 'normal');

        return compact('pickup', 'dropoff', 'route', 'pricing');
    }

    public function createFromQuote(int $merchantId, array $input, array $quote): int
    {
        [$otp, $expiresAt] = explode('|', $this->otpService->generateOtpForOrder(0));

        $payload = [
            'merchant_id' => $merchantId,
            'public_tracking_token' => Uuid::uuid4()->toString(),
            'pickup_contact_name' => $input['pickup_contact_name'],
            'pickup_contact_phone' => $input['pickup_contact_phone'],
            'pickup_address' => $input['pickup_address'],
            'pickup_reference' => $input['pickup_reference'] ?? null,
            'pickup_lat' => $quote['pickup']['lat'],
            'pickup_lng' => $quote['pickup']['lng'],
            'dropoff_contact_name' => $input['dropoff_contact_name'],
            'dropoff_contact_phone' => $input['dropoff_contact_phone'],
            'dropoff_address' => $input['dropoff_address'],
            'dropoff_reference' => $input['dropoff_reference'] ?? null,
            'dropoff_lat' => $quote['dropoff']['lat'],
            'dropoff_lng' => $quote['dropoff']['lng'],
            'package_type' => $input['package_type'],
            'package_description' => $input['package_description'],
            'package_size' => $input['package_size'] ?? null,
            'package_weight' => $input['package_weight'] ?? null,
            'estimated_value' => $input['estimated_value'] ?? null,
            'notes' => $input['notes'] ?? null,
            'city' => $input['city'],
            'zone' => $input['zone'] ?? null,
            'route_distance_km' => $quote['route']['distance_km'],
            'route_duration_minutes' => $quote['route']['duration_minutes'],
            'route_provider' => $quote['route']['provider'],
            'route_polyline' => $quote['route']['polyline'],
            'geocoding_confidence' => min($quote['pickup']['confidence'], $quote['dropoff']['confidence']),
            'base_price' => $quote['pricing']['base_price'],
            'distance_price' => $quote['pricing']['distance_price'],
            'urgency_surcharge' => $quote['pricing']['urgency_surcharge'],
            'extra_fee' => $quote['pricing']['extra_fee'],
            'platform_fee' => $quote['pricing']['platform_fee'],
            'rider_payout' => $quote['pricing']['rider_payout'],
            'price_total' => $quote['pricing']['price_total'],
            'pricing_breakdown' => $quote['pricing']['pricing_breakdown'],
            'payment_method' => $input['payment_method'] ?? 'mpesa',
            'payment_status' => 'pending_payment',
            'payout_status' => 'payout_pending',
            'delivery_status' => 'pending_payment',
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ];

        $orderId = $this->orders->create($payload);
        $this->otpService->initializeOtp($orderId, $otp, $expiresAt);
        $this->orders->addHistory($orderId, 'pending_payment', 'merchant', $merchantId, 'Pedido criado e pendente de pagamento');
        return $orderId;
    }

    public function autoExpireUnassignedOrders(): int
    {
        return $this->orders->reassignStale((int)Env::get('MAX_ASSIGNMENT_TIME_MINUTES', 15));
    }
}
