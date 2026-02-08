<?php

namespace Tests\Unit\Repositories\Weather;

use App\Repositories\Weather\GeoCodingRepository;
use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class GeoCodingRepositoryTest extends TestCase
{
    private GeoCodingRepository $repository;
    private OpenWeatherMapClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(OpenWeatherMapClient::class);
        $this->repository = new GeoCodingRepository($this->client);
    }

    public function test_get_coordinates_calls_correct_endpoint(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn($key, $ttl, $callback) => $callback());

        $expected = [['lat' => -22.9, 'lon' => -43.1, 'name' => 'Niterói']];

        $this->client->shouldReceive('get')
            ->once()
            ->with('/geo/1.0/direct', [
                'q' => 'Niterói',
                'limit' => 1,
            ])
            ->andReturn($expected);

        $result = $this->repository->getCoordinates('Niterói');

        $this->assertEquals($expected, $result);
    }

    public function test_get_coordinates_uses_cache(): void
    {
        $cached = [['lat' => -22.9, 'lon' => -43.1]];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cached);

        $this->client->shouldNotReceive('get');

        $result = $this->repository->getCoordinates('Niterói');

        $this->assertEquals($cached, $result);
    }

    public function test_get_coordinates_normalizes_cache_key(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key) {
                return $key === 'weather:geocoding:são paulo';
            })
            ->andReturn([]);

        $this->repository->getCoordinates('São Paulo');
    }
}
