<?php

namespace Tests\Unit\Repositories\Weather;

use App\Repositories\Weather\CurrentWeatherRepository;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class CurrentWeatherRepositoryTest extends TestCase
{
    private CurrentWeatherRepository $repository;
    private OpenWeatherMapClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(OpenWeatherMapClient::class);
        $this->repository = new CurrentWeatherRepository($this->client);
    }

    public function test_get_current_weather_calls_correct_endpoint(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $expected = ['main' => ['temp' => 25]];

        $this->client->shouldReceive('get')
            ->once()
            ->with('/data/2.5/weather', [
                'lat' => -22.9,
                'lon' => -43.1,
                'units' => 'metric',
                'lang' => 'pt',
            ])
            ->andReturn($expected);

        $result = $this->repository->getCurrentWeather(-22.9, -43.1, 'metric', 'pt');

        $this->assertEquals($expected, $result);
    }

    public function test_get_current_weather_uses_cache(): void
    {
        $cached = ['main' => ['temp' => 30]];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cached);

        $this->client->shouldNotReceive('get');

        $result = $this->repository->getCurrentWeather(-22.9, -43.1);

        $this->assertEquals($cached, $result);
    }

    public function test_get_current_weather_uses_default_params(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $this->client->shouldReceive('get')
            ->once()
            ->with('/data/2.5/weather', [
                'lat' => 0.0,
                'lon' => 0.0,
                'units' => 'metric',
                'lang' => 'en',
            ])
            ->andReturn([]);

        $this->repository->getCurrentWeather(0.0, 0.0);
    }
}
