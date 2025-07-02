<?php

namespace App\Application\Handler\Search;


use App\DTO\Api\Flights\Search\FlightSearchRequestDto;
use App\DTO\Api\Flights\Search\FlightSearchResponseDto;

interface FlightSearchHandlerInterface
{
    public function handle(FlightSearchRequestDto $dto): FlightSearchResponseDto;
}
