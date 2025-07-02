<?php
namespace App\DTO\Api\Flights\Search;

class FlightSearchResponseDto
{
    public function __construct(
        public string $searchId,
        public string $origin,
        public string $destination,
        public string $departureDate,
        public array $flightOptions
    ) {}
}
