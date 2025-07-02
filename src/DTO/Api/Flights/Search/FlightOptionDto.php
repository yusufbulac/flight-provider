<?php

namespace App\DTO\Api\Flights\Search;

class FlightOptionDto
{
    public function __construct(
        public string $id,
        public array $segments,
        public string $totalPrice,
        public string $currency,
        public ?array $availableBundles = null
    ) {}
}
