<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\CurrentWeatherRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;

class CurrentWeatherRepository implements CurrentWeatherRepositoryInterface
{
    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getCurrentWeather(float $lat, float $lon, string $units = 'metric'): array
    {
        return $this->client->get('/weather', [
            'lat' => $lat,
            'lon' => $lon,
            'units' => $units,
        ]);
    }
}
