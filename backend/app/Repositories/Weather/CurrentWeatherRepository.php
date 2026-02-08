<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\CurrentWeatherRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;

class CurrentWeatherRepository implements CurrentWeatherRepositoryInterface
{
    private const CACHE_TTL = 600; // 10 minutes

    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getCurrentWeather(float $lat, float $lon, string $units = 'metric', string $lang = 'en'): array
    {
        $cacheKey = "weather:current:{$lat}:{$lon}:{$units}:{$lang}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lon, $units, $lang) {
            return $this->client->get('/data/2.5/weather', [
                'lat' => $lat,
                'lon' => $lon,
                'units' => $units,
                'lang' => $lang,
            ]);
        });
    }
}