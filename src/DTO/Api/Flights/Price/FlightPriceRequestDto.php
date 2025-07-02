<?php

namespace App\DTO\Api\Flights\Price;

use Symfony\Component\Validator\Constraints as Assert;

class FlightPriceRequestDto
{
    #[Assert\NotBlank]
    public ?string $searchId = null;

    #[Assert\NotBlank]
    public ?string $flightNumber = null;

    #[Assert\NotBlank]
    public string $provider = 'airarabia';
}
