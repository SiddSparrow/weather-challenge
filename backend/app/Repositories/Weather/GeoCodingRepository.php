<?php

namespace App\Repositories\Weather;

use App\Contracts\Weather\GeoCodingRepositoryInterface;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;

class GeoCodingRepository implements GeoCodingRepositoryInterface
{
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly OpenWeatherMapClient $client,
    ) {}

    public function getCoordinates(string $cityName): array
    {
        $cacheKey = 'weather:geocoding:' . strtolower(trim($cityName));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($cityName) {
            return $this->client->get('/geo/1.0/direct', [
                'q' => $cityName,
                'limit' => 1,
            ]);
        });
    }
}