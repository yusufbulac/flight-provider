<?php

namespace App\Application\Handler\Price;

use App\DTO\Api\Flights\Price\FlightPriceRequestDto;
use App\DTO\Api\Flights\Price\FlightPriceResponseDto;

interface FlightPriceHandlerInterface
{
    public function handle(FlightPriceRequestDto $dto): FlightPriceResponseDto;
}
