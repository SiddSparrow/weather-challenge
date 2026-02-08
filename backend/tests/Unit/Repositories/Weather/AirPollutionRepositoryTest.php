<?php

namespace Tests\Unit\Repositories\Weather;

use App\Repositories\Weather\AirPollutionRepository;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class AirPollutionRepositoryTest extends TestCase
{
    private AirPollutionRepository $repository;
    private OpenWeatherMapClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(OpenWeatherMapClient::class);
        $this->repository = new AirPollutionRepository($this->client);
    }

    public function test_get_air_pollution_calls_correct_endpoint(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $expected = ['list' => [['main' => ['aqi' => 2]]]];

        $this->client->shouldReceive('get')
            ->once()
            ->with('/data/2.5/air_pollution', [
                'lat' => -22.9,
                'lon' => -43.1,
            ])
            ->andReturn($expected);

        $result = $this->repository->getAirPollution(-22.9, -43.1);

        $this->assertEquals($expected, $result);
    }

    public function test_get_air_pollution_uses_cache(): void
    {
        $cached = ['list' => [['main' => ['aqi' => 1]]]];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cached);

        $this->client->shouldNotReceive('get');

        $result = $this->repository->getAirPollution(-22.9, -43.1);

        $this->assertEquals($cached, $result);
    }
}
