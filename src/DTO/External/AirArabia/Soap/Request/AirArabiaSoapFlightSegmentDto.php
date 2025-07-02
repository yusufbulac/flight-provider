<?php

namespace App\DTO\External\AirArabia\Soap\Request;

class AirArabiaSoapFlightSegmentDto
{
    public function __construct(
        public string $departureAirport,
        public string $arrivalAirport,
        public string $departureDateTime,
        public string $arrivalDateTime,
        public string $flightNumber,
        public string $operatingAirline = 'G9'
    ) {}
}
