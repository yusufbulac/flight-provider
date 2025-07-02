<?php

namespace App\DTO\Api\Flights\Search;

use Symfony\Component\Validator\Constraints as Assert;

class FlightLocationDto
{
    #[Assert\NotBlank]
    public string $code;

    #[Assert\NotBlank]
    public string $locationType;
}
