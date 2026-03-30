<?php

declare(strict_types=1);

namespace App\DTOs;

class OrderQuoteDTO
{
    public function __construct(
        public readonly string $pickupAddress,
        public readonly string $dropoffAddress,
        public readonly string $city,
        public readonly string $packageType,
        public readonly bool $urgent
    ) {}
}
