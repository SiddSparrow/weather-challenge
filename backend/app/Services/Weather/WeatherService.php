<?php

namespace App\Services\Weather;

use App\Contracts\Weather\AirPollutionRepositoryInterface;
use App\Contracts\Weather\CurrentWeatherRepositoryInterface;
use App\Contracts\Weather\ForecastRepositoryInterface;
use App\Contracts\Weather\GeoCodingRepositoryInterface;

class WeatherService
{
    public function __construct(
        private readonly CurrentWeatherRepositoryInterface $currentWeatherRepository,
        private readonly ForecastRepositoryInterface $forecastRepository,
        private readonly AirPollutionRepositoryInterface $airPollutionRepository,
        private readonly GeoCodingRepositoryInterface $geoCodingRepository,
    ) {}

    public function getCurrentWeather(float $lat, float $lon, string $units = 'metric'): array
    {
        return $this->currentWeatherRepository->getCurrentWeather($lat, $lon, $units);
    }

    public function getForecast(float $lat, float $lon, string $units = 'metric'): array
    {
        return $this->forecastRepository->getForecast($lat, $lon, $units);
    }

    public function getAirPollution(float $lat, float $lon): array
    {
        return $this->airPollutionRepository->getAirPollution($lat, $lon);
    }

    public function getGeoCoding(string $city): array
    {
        return $this->geoCodingRepository->getCoordinates($city);
    }
}
