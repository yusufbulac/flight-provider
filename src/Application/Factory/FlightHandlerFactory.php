<?php

namespace App\Application\Factory;

use App\Application\Handler\Price\FlightPriceHandlerInterface;
use App\Application\Handler\Search\FlightSearchHandlerInterface;

class FlightHandlerFactory
{
    public function __construct(
        private iterable $searchHandlers,
        private iterable $priceHandlers
    )
    {
    }

    public function getSearchHandler(string $provider): FlightSearchHandlerInterface
    {
        return $this->searchHandlers[$provider]
            ?? throw new \RuntimeException("Search handler not found for provider: {$provider}");
    }

    public function getPriceHandler(string $provider): FlightPriceHandlerInterface
    {
        return $this->priceHandlers[$provider]
            ?? throw new \RuntimeException("Price handler not found for provider: {$provider}");
    }
}
