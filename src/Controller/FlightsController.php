<?php

namespace App\Controller;

use App\Application\Factory\FlightHandlerFactory;
use App\DTO\Api\Flights\Search\FlightSearchRequestDto;
use App\DTO\Api\Flights\Price\FlightPriceRequestDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class FlightsController extends AbstractController
{
    public function __construct(private FlightHandlerFactory $flightHandlerFactory)
    {
    }

    #[Route('/api/flights/search', name: 'flight_search', methods: ['POST'])]
    public function search(
        #[MapRequestPayload(validationFailedStatusCode: 422)] FlightSearchRequestDto $dto
    ): JsonResponse
    {
        $handler = $this->flightHandlerFactory->getSearchHandler($dto->provider);
        $response = $handler->handle($dto);
        return $this->json($response);
    }

    #[Route('/api/flights/price', name: 'flight_price', methods: ['POST'])]
    public function price(
        #[MapRequestPayload(validationFailedStatusCode: 422)] FlightPriceRequestDto $dto
    ): JsonResponse
    {
        try {
            $handler = $this->flightHandlerFactory->getPriceHandler($dto->provider);
            $response = $handler->handle($dto);
            return $this->json($response);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
