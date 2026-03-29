<?php

declare(strict_types=1);

namespace App\Services;

class MapPresentationService
{
    public function orderMapPayload(array $order, ?array $riderLocation = null): array
    {
        return [
            'pickup' => ['lat' => (float)$order['pickup_lat'], 'lng' => (float)$order['pickup_lng'], 'address' => $order['pickup_address']],
            'dropoff' => ['lat' => (float)$order['dropoff_lat'], 'lng' => (float)$order['dropoff_lng'], 'address' => $order['dropoff_address']],
            'route' => [
                'polyline' => $order['route_polyline'] ?? null,
                'distance_km' => (float)$order['route_distance_km'],
                'duration_minutes' => (int)$order['route_duration_minutes'],
            ],
            'rider' => $riderLocation,
            'status' => $order['delivery_status'],
        ];
    }
}
