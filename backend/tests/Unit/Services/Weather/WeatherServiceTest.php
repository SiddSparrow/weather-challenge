<?php

namespace Tests\Unit\Services\Weather;

use App\Contracts\Weather\AirPollutionRepositoryInterface;
use App\Contracts\Weather\CurrentWeatherRepositoryInterface;
use App\Contracts\Weather\ForecastRepositoryInterface;
use App\Contracts\Weather\GeoCodingRepositoryInterface;
use App\Services\Weather\WeatherService;
use Mockery;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    private WeatherService $service;
    private CurrentWeatherRepositoryInterface $currentWeatherRepo;
    private ForecastRepositoryInterface $forecastRepo;
    private AirPollutionRepositoryInterface $airPollutionRepo;
    private GeoCodingRepositoryInterface $geoCodingRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentWeatherRepo = Mockery::mock(CurrentWeatherRepositoryInterface::class);
        $this->forecastRepo = Mockery::mock(ForecastRepositoryInterface::class);
        $this->airPollutionRepo = Mockery::mock(AirPollutionRepositoryInterface::class);
        $this->geoCodingRepo = Mockery::mock(GeoCodingRepositoryInterface::class);

        $this->service = new WeatherService(
            $this->currentWeatherRepo,
            $this->forecastRepo,
            $this->airPollutionRepo,
            $this->geoCodingRepo,
        );
    }

    public function test_get_full_weather_by_city_orchestrates_all_repositories(): void
    {
        $geoData = [['lat' => -22.9, 'lon' => -43.1, 'name' => 'Niterói', 'country' => 'BR', 'state' => 'RJ']];
        $weatherData = ['main' => ['temp' => 25]];
        $pollutionData = ['list' => [['main' => ['aqi' => 2]]]];

        $this->geoCodingRepo->shouldReceive('getCoordinates')
            ->once()
            ->with('Niterói')
            ->andReturn($geoData);

        $this->currentWeatherRepo->shouldReceive('getCurrentWeather')
            ->once()
            ->with(-22.9, -43.1, 'metric', 'pt')
            ->andReturn($weatherData);

        $this->airPollutionRepo->shouldReceive('getAirPollution')
            ->once()
            ->with(-22.9, -43.1)
            ->andReturn($pollutionData);

        $result = $this->service->getFullWeatherByCity('Niterói', 'metric', 'pt');

        $this->assertEquals('Niterói', $result['location']['name']);
        $this->assertEquals('BR', $result['location']['country']);
        $this->assertEquals('RJ', $result['location']['state']);
        $this->assertEquals($weatherData, $result['weather']);
        $this->assertEquals($pollutionData, $result['air_pollution']);
    }

    public function test_get_full_weather_by_city_returns_error_when_city_not_found(): void
    {
        $this->geoCodingRepo->shouldReceive('getCoordinates')
            ->once()
            ->with('CidadeInexistente')
            ->andReturn([]);

        $result = $this->service->getFullWeatherByCity('CidadeInexistente');

        $this->assertEquals(['error' => 'city_not_found'], $result);
    }

    public function test_get_current_weather_delegates_to_repository(): void
    {
        $expected = ['main' => ['temp' => 30]];

        $this->currentWeatherRepo->shouldReceive('getCurrentWeather')
            ->once()
            ->with(-22.9, -43.1, 'imperial', 'es')
            ->andReturn($expected);

        $result = $this->service->getCurrentWeather(-22.9, -43.1, 'imperial', 'es');

        $this->assertEquals($expected, $result);
    }

    public function test_get_forecast_delegates_to_repository(): void
    {
        $expected = ['list' => []];

        $this->forecastRepo->shouldReceive('getForecast')
            ->once()
            ->with(-22.9, -43.1, 'metric')
            ->andReturn($expected);

        $result = $this->service->getForecast(-22.9, -43.1, 'metric');

        $this->assertEquals($expected, $result);
    }

    public function test_get_air_pollution_delegates_to_repository(): void
    {
        $expected = ['list' => [['main' => ['aqi' => 1]]]];

        $this->airPollutionRepo->shouldReceive('getAirPollution')
            ->once()
            ->with(-22.9, -43.1)
            ->andReturn($expected);

        $result = $this->service->getAirPollution(-22.9, -43.1);

        $this->assertEquals($expected, $result);
    }

    public function test_get_geo_coding_delegates_to_repository(): void
    {
        $expected = [['lat' => -22.9, 'lon' => -43.1]];

        $this->geoCodingRepo->shouldReceive('getCoordinates')
            ->once()
            ->with('Niterói')
            ->andReturn($expected);

        $result = $this->service->getGeoCoding('Niterói');

        $this->assertEquals($expected, $result);
    }
}
