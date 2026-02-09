<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\ForecastRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;

class ForecastRepository implements ForecastRepositoryInterface
{
    private const CACHE_TTL = 1800; // 30 minutes

    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getForecast(float $lat, float $lon, string $units = 'metric', string $lang = 'en'): array
    {
        $cacheKey = "weather:forecast:{$lat}:{$lon}:{$units}:{$lang}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lon, $units, $lang) {
            return $this->client->get('/data/2.5/forecast', [
                'lat' => $lat,
                'lon' => $lon,
                'units' => $units,
                'lang' => $lang,
            ]);
        });
    }
}