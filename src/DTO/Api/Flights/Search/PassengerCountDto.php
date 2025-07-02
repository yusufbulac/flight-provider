<?php

namespace App\DTO\Api\Flights\Search;

use Symfony\Component\Validator\Constraints as Assert;

class PassengerCountDto
{
    #[Assert\PositiveOrZero]
    public int $adt = 1;

    #[Assert\PositiveOrZero]
    public int $chd = 0;

    #[Assert\PositiveOrZero]
    public int $inf = 0;
}
