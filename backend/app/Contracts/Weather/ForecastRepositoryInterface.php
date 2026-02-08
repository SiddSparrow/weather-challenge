<?php

namespace App\Contracts\Weather;

interface ForecastRepositoryInterface
{
    public function getForecast(float $lat, float $lon, string $units = 'metric'): array;
}
