<?php

namespace App\DTO\External\AirArabia\Soap\Request;

class AirArabiaSoapPricingRequestDto
{
    /**
     * @param AirArabiaSoapFlightSegmentDto[] $segments
     */
    public function __construct(
        public array $segments,
        public int $adt,
        public int $chd,
        public int $inf
    ) {}
}
