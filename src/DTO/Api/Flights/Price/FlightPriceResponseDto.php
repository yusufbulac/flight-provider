<?php

namespace App\DTO\Api\Flights\Price;

class FlightPriceResponseDto
{
    public function __construct(
        public string $flightId,
        public string $currency,
        public string $totalPrice,
        public array $bundles
    ) {}
}
