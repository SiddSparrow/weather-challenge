<?php

namespace App\Repositories\Weather;
use App\Services\Weather\OpenWeatherMapClient;
use App\Contracts\Weather\GeoCodingRepositoryInterface;

class GeoCodingRepository implements GeoCodingRepositoryInterface
{
    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getCoordinates(string $cityName): array
    {
        return $this->client->get('/geo/1.0/direct', [
            'q' => $cityName,
            'limit' => 1,
        ]);
    }
}