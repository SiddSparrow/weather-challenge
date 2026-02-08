<?php

namespace App\Contracts\Weather;

interface GeoCodingRepositoryInterface
{
    public function getCoordinates(string $cityName): array;
}