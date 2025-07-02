<?php

namespace App\Application\Handler\Price;

use App\DTO\Api\Flights\Price\FlightPriceRequestDto;
use App\DTO\Api\Flights\Price\FlightPriceResponseDto;
use App\DTO\External\AirArabia\Soap\Request\AirArabiaSoapFlightSegmentDto;
use App\DTO\External\AirArabia\Soap\Request\AirArabiaSoapPricingRequestDto;
use App\Infrastructure\Client\AirArabia\AirArabiaSoapClient;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;

class AirArabiaFlightPriceHandler implements FlightPriceHandlerInterface
{
    public function __construct(
        private AirArabiaSoapClient $soapClient,
        private CacheItemPoolInterface $cache
    )
    {
    }

    public function handle(FlightPriceRequestDto $dto): FlightPriceResponseDto
    {
        $item = $this->cache->getItem($dto->searchId);

        if (!$item->isHit()) {
            throw new RuntimeException("Invalid or expired search ID");
        }

        $cached = $item->get();
        $requestDto = $cached['request'];
        $flightOptions = $cached['flights'];

        $selectedFlight = null;
        foreach ($flightOptions as $flight) {
            foreach ($flight->segments as $segment) {
                if ($segment->flightNumber === $dto->flightNumber) {
                    $selectedFlight = $flight;
                    break 2;
                }
            }
        }

        if (!$selectedFlight) {
            throw new RuntimeException("Flight not found in cached search results");
        }

        $segments = array_map(
            fn($segment) => new AirArabiaSoapFlightSegmentDto(
                departureAirport: $segment->origin,
                arrivalAirport: $segment->destination,
                departureDateTime: $segment->departureTime,
                arrivalDateTime: $segment->arrivalTime,
                flightNumber: $segment->flightNumber,
                operatingAirline: $segment->airlineCode
            ),
            $selectedFlight->segments
        );

        $soapRequest = new AirArabiaSoapPricingRequestDto(
            segments: $segments,
            adt: $requestDto->pax->adt ?? 1,
            chd: $requestDto->pax->chd ?? 0,
            inf: $requestDto->pax->inf ?? 0
        );


        $soapResponse = $this->soapClient->getPrice($soapRequest);

        return new FlightPriceResponseDto(
            flightId: $selectedFlight->id,
            currency: 'AED',
            totalPrice: $soapResponse['TotalPrice'],
            bundles: []
        );
    }
}
