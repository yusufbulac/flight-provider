<?php

namespace App\Infrastructure\Client\AirArabia;

use App\DTO\Api\Flights\Search\FlightOptionDto;
use App\DTO\Api\Flights\Search\FlightSearchRequestDto;
use App\DTO\Api\Flights\Search\FlightSearchResponseDto;
use App\DTO\Api\Flights\Search\FlightSegmentDto;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AirArabiaRestClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $authUrl,
        private string $searchUrl,
        private string $login,
        private string $password,
        private string $agentCode,
        private string $posCountry,
        private string $posStation,
        private string $currencyCode
    )
    {
    }

    public function search(FlightSearchRequestDto $dto): FlightSearchResponseDto
    {
        $token = $this->authenticate();

        $ondRef = $dto->origin->code . '/' . $dto->destination->code;

        $payload = [
            'searchOnds' => [
                [
                    'origin' => [
                        'code' => $dto->origin->code,
                        'locationType' => $dto->origin->locationType
                    ],
                    'destination' => [
                        'code' => $dto->destination->code,
                        'locationType' => $dto->destination->locationType
                    ],
                    'searchStartDate' => $dto->departureDate,
                    'searchEndDate' => $dto->departureDate,
                    'preferredDate' => $dto->departureDate,
                    'bookingType' => 'NORMAL',
                    'cabinClass' => $dto->cabinClass,
                    'ondRef' => $ondRef,
                    'interlineQuoteDetails' => null
                ]
            ],
            'paxCounts' => [
                ['count' => $dto->pax->adt, 'paxType' => 'ADT'],
                ['count' => $dto->pax->chd, 'paxType' => 'CHD'],
                ['count' => $dto->pax->inf, 'paxType' => 'INF']
            ],
            'isReturn' => false,
            'currencyCode' => $this->currencyCode,
            'cabinClass' => $dto->cabinClass,
            'metaData' => [
                'agentCode' => $this->agentCode,
                'country' => $this->posCountry,
                'station' => $this->posStation,
                'salesChannel' => 'OTA',
                'otherMetaData' => [
                    [
                        'metaDataKey' => 'SKIP_OND_MERGE',
                        'metaDataValue' => 'false'
                    ]
                ]
            ]
        ];

        $response = $this->httpClient->request('POST', $this->searchUrl, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);

        $data = $response->toArray();
        return $this->mapToResponseDto($data);
    }


    private function authenticate(): string
    {
        $response = $this->httpClient->request('POST', $this->authUrl, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'login' => $this->login,
                'password' => $this->password
            ]
        ]);

        $data = $response->toArray();

        return $data['tokenPair']['accessToken'] ?? throw new RuntimeException("Access token missing");
    }

    private function mapToResponseDto(array $data): FlightSearchResponseDto
    {
        $flightOptions = [];
        $optionIndex = 0;
        $ondRef = array_key_first($data['ondWiseFlightCombinations']);
        $combinations = $data['ondWiseFlightCombinations'][$ondRef]['dateWiseFlightCombinations'];
        $key = array_key_first($combinations);

        foreach ($combinations[$key] as $opt) {
            if (!is_array($opt)) continue;
            foreach ($opt as $seg) {

                $segments[] = new FlightSegmentDto(
                    $seg['flightSegments'][0]['origin']['airportCode'],
                    $seg['flightSegments'][0]['destination']['airportCode'],
                    $seg['flightSegments'][0]['departureDateTimeLocal'],
                    $seg['flightSegments'][0]['arrivalDateTimeLocal'],
                    $seg['flightSegments'][0]['flightNumber'],
                    $seg['flightSegments'][0]['flightNumber'] ? substr($seg['flightSegments'][0]['flightNumber'], 0, 2) : null
                );

                $price = isset($seg['cabinPrices'][0]['price']) ? $seg['cabinPrices'][0]['price'] : 0;
                $currency = $this->currencyCode;
                $commissioned = number_format($price * 1.10, 2, '.', '');

                $flightId = $seg['cabinPrices'][0]['fareOndWiseBookingClassCodes'][$ondRef] ?? (string)$optionIndex;
                $optionIndex++;

                $flightOptions[] = new FlightOptionDto(
                    $flightId,
                    $segments,
                    $commissioned,
                    $currency
                );
            }
        }

        return new FlightSearchResponseDto(
            '',
            $segments[0]->origin ?? '',
            $segments[0]->destination ?? '',
            $segments[0]->departureTime ?? '',
            $flightOptions
        );
    }
}
