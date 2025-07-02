<?php

namespace App\Application\Handler\Search;

use App\DTO\Api\Flights\Search\FlightSearchRequestDto;
use App\DTO\Api\Flights\Search\FlightSearchResponseDto;
use App\Infrastructure\Client\AirArabia\AirArabiaRestClient;
use Psr\Cache\CacheItemPoolInterface;

class AirArabiaFlightSearchHandler implements FlightSearchHandlerInterface
{
    public function __construct(
        private AirArabiaRestClient $restClient,
        private CacheItemPoolInterface $cache
    )
    {
    }

    public function handle(FlightSearchRequestDto $dto): FlightSearchResponseDto
    {
        $response = $this->restClient->search($dto);
        $searchId = uniqid('search_', true);

        $item = $this->cache->getItem($searchId);
        $item->set([
            'request' => $dto,
            'flights' => $response->flightOptions
        ]);
        $this->cache->save($item);

        $response->searchId = $searchId;
        return $response;
    }
}
