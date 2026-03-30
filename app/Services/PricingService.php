<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

class PricingService
{
    public function quoteOrder(float $distanceKm, bool $urgent = false, string $packageType = 'normal'): array
    {
        $base = (float)Env::get('BASE_DELIVERY_PRICE', 100);
        $distancePrice = $this->calculateDistancePrice($distanceKm);
        $urgentSurcharge = $urgent ? (float)Env::get('URGENT_SURCHARGE', 30) : 0.0;

        $extra = match ($packageType) {
            'fragile' => (float)Env::get('FRAGILE_SURCHARGE', 20),
            'express' => (float)Env::get('EXPRESS_SURCHARGE', 40),
            default => 0.0,
        };

        $total = $base + $distancePrice + $urgentSurcharge + $extra;
        $platformFee = $this->calculatePlatformFee($total);
        $riderPayout = $this->calculateRiderPayout($total, $platformFee);

        return [
            'base_price' => $base,
            'distance_price' => $distancePrice,
            'urgency_surcharge' => $urgentSurcharge,
            'extra_fee' => $extra,
            'platform_fee' => $platformFee,
            'rider_payout' => $riderPayout,
            'price_total' => round($total, 2),
            'pricing_breakdown' => json_encode($this->generatePricingBreakdown($base, $distancePrice, $urgentSurcharge, $extra, $platformFee, $riderPayout), JSON_THROW_ON_ERROR),
        ];
    }

    public function calculateDistancePrice(float $distanceKm): float
    {
        return round($distanceKm * (float)Env::get('PRICE_PER_KM', 10), 2);
    }

    public function calculatePlatformFee(float $total): float
    {
        $fixed = (float)Env::get('PLATFORM_COMMISSION_FIXED', 0);
        $percent = (float)Env::get('PLATFORM_COMMISSION_PERCENT', 15);
        return round($fixed + (($percent / 100) * $total), 2);
    }

    public function calculateRiderPayout(float $total, float $platformFee): float
    {
        $minimum = (float)Env::get('MINIMUM_PAYOUT', 50);
        return max(round($total - $platformFee, 2), $minimum);
    }

    public function generatePricingBreakdown(float $base, float $distance, float $urgent, float $extra, float $platformFee, float $payout): array
    {
        return compact('base', 'distance', 'urgent', 'extra', 'platformFee', 'payout');
    }
}
