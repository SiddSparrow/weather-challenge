<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\ForecastRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;

class ForecastRepository implements ForecastRepositoryInterface
{
    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getForecast(float $lat, float $lon, string $units = 'metric'): array
    {
        return $this->client->get('/forecast', [
            'lat' => $lat,
            'lon' => $lon,
            'units' => $units,
        ]);
    }
}
