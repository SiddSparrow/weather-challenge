<?php

namespace Tests\Unit\Repositories\Weather;

use App\Repositories\Weather\ForecastRepository;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class ForecastRepositoryTest extends TestCase
{
    private ForecastRepository $repository;
    private OpenWeatherMapClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(OpenWeatherMapClient::class);
        $this->repository = new ForecastRepository($this->client);
    }

    public function test_get_forecast_calls_correct_endpoint(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $expected = ['list' => [['main' => ['temp' => 20]]]];

        $this->client->shouldReceive('get')
            ->once()
            ->with('/data/2.5/forecast', [
                'lat' => -22.9,
                'lon' => -43.1,
                'units' => 'imperial',
                'lang' => 'pt',
            ])
            ->andReturn($expected);

        $result = $this->repository->getForecast(-22.9, -43.1, 'imperial', 'pt');

        $this->assertEquals($expected, $result);
    }

    public function test_get_forecast_uses_cache(): void
    {
        $cached = ['list' => []];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cached);

        $this->client->shouldNotReceive('get');

        $result = $this->repository->getForecast(-22.9, -43.1);

        $this->assertEquals($cached, $result);
    }
}
