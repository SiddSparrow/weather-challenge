<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\AirPollutionRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;

class AirPollutionRepository implements AirPollutionRepositoryInterface
{
    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getAirPollution(float $lat, float $lon): array
    {
        return $this->client->get('/data/2.5/air_pollution', [
            'lat' => $lat,
            'lon' => $lon,
        ]);
    }
}
