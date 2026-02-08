<?php

namespace App\Contracts\Weather;

interface AirPollutionRepositoryInterface
{
    public function getAirPollution(float $lat, float $lon): array;
}
