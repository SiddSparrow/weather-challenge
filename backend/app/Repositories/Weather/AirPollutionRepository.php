<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\AirPollutionRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;

class AirPollutionRepository implements AirPollutionRepositoryInterface
{
    private const CACHE_TTL = 1800; // 30 minutes

    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getAirPollution(float $lat, float $lon): array
    {
        $cacheKey = "weather:air_pollution:{$lat}:{$lon}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lon) {
            return $this->client->get('/data/2.5/air_pollution', [
                'lat' => $lat,
                'lon' => $lon,
            ]);
        });
    }
}