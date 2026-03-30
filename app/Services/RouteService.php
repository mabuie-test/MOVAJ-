<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

class RouteService
{
    public function __construct(private readonly HttpClientService $http = new HttpClientService()) {}

    public function normalizeAddress(string $address): string
    {
        return trim(preg_replace('/\s+/', ' ', $address));
    }

    public function geocodeAddress(string $address, string $city): array
    {
        $client = $this->http->client(['base_uri' => Env::get('GEOCODING_BASE_URL')]);
        $query = $this->normalizeAddress($address . ', ' . $city . ', Mozambique');
        $res = $client->get('/search', ['query' => ['q' => $query, 'format' => 'json', 'limit' => 1]]);
        $data = json_decode((string)$res->getBody(), true);

        if (empty($data[0])) {
            throw new \RuntimeException('Endereço não geocodificado.');
        }

        return [
            'lat' => (float)$data[0]['lat'],
            'lng' => (float)$data[0]['lon'],
            'confidence' => (float)($data[0]['importance'] ?? 0),
            'display_name' => $data[0]['display_name'] ?? null,
        ];
    }

    public function calculateRoute(array $origin, array $destination): array
    {
        $client = $this->http->client(['base_uri' => Env::get('MAP_BASE_URL')]);
        $coords = sprintf('%F,%F;%F,%F', $origin['lng'], $origin['lat'], $destination['lng'], $destination['lat']);
        $res = $client->get('/route/v1/driving/' . $coords, ['query' => ['overview' => 'full', 'geometries' => 'polyline']]);
        $data = json_decode((string)$res->getBody(), true);

        if (empty($data['routes'][0])) {
            throw new \RuntimeException('Rota não encontrada.');
        }

        $route = $data['routes'][0];
        return [
            'distance_km' => round(((float)$route['distance']) / 1000, 2),
            'duration_minutes' => (int)round(((float)$route['duration']) / 60),
            'provider' => Env::get('MAP_PROVIDER', 'osrm'),
            'polyline' => $route['geometry'] ?? null,
        ];
    }

    public function validateAddressWithinCoverage(string $city): bool
    {
        return in_array(strtolower($city), ['maputo', 'matola'], true);
    }
}
