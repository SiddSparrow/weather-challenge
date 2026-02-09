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

    public function getFullWeatherByCity(string $city, string $units = 'metric', string $lang = 'en'): array
    {
        $locations = $this->geoCodingRepository->getCoordinates($city);

        if (empty($locations)) {
            return ['error' => 'city_not_found'];
        }

        $lat = $locations[0]['lat'];
        $lon = $locations[0]['lon'];

        $weather = $this->currentWeatherRepository->getCurrentWeather($lat, $lon, $units, $lang);
        $airPollution = $this->airPollutionRepository->getAirPollution($lat, $lon);
        $forecast = $this->forecastRepository->getForecast($lat, $lon, $units, $lang);

        return [
            'location' => [
                'name' => $locations[0]['name'],
                'country' => $locations[0]['country'] ?? '',
                'state' => $locations[0]['state'] ?? '',
                'lat' => $lat,
                'lon' => $lon,
            ],
            'weather' => $weather,
            'air_pollution' => $airPollution,
            'forecast' => $forecast,
        ];
    }

    public function getCurrentWeather(float $lat, float $lon, string $units = 'metric', string $lang = 'en'): array
    {
        return $this->currentWeatherRepository->getCurrentWeather($lat, $lon, $units, $lang);
    }

    public function getForecast(float $lat, float $lon, string $units = 'metric', string $lang = 'en'): array
    {
        return $this->forecastRepository->getForecast($lat, $lon, $units, $lang);
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
