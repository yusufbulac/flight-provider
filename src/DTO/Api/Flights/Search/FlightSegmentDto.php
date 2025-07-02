<?php
namespace App\DTO\Api\Flights\Search;

class FlightSegmentDto
{
    public function __construct(
        public string $origin,
        public string $destination,
        public string $departureTime,
        public string $arrivalTime,
        public string $flightNumber,
        public ?string $airlineCode = null
    ) {}
}
