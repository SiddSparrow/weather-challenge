<?php

namespace App\Contracts\Weather;

interface CurrentWeatherRepositoryInterface
{
    public function getCurrentWeather(float $lat, float $lon, string $units = 'metric', string $lang = 'en'): array;
}
