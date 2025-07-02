<?php

namespace App\DTO\Api\Flights\Search;

use Symfony\Component\Validator\Constraints as Assert;

class FlightSearchRequestDto
{
    #[Assert\Valid]
    #[Assert\NotNull]
    public FlightLocationDto $origin;

    #[Assert\Valid]
    #[Assert\NotNull]
    public FlightLocationDto $destination;

    #[Assert\NotBlank]
    #[Assert\Date]
    public ?string $departureDate = null;

    #[Assert\Valid]
    #[Assert\NotNull]
    public PassengerCountDto $pax;

    #[Assert\NotBlank]
    public string $cabinClass = 'Y';

    #[Assert\NotBlank]
    public string $provider = 'airarabia';
}
