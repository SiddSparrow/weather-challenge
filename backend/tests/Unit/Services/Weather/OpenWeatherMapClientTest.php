<?php

namespace Tests\Unit\Services\Weather;

use App\Services\Weather\OpenWeatherMapClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWeatherMapClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'weather.base_url' => 'https://api.openweathermap.org',
            'weather.api_key' => 'test-api-key',
            'weather.timeout' => 5,
        ]);
    }

    public function test_get_sends_request_with_api_key(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['main' => ['temp' => 25]], 200),
        ]);

        $client = new OpenWeatherMapClient();
        $result = $client->get('/data/2.5/weather', ['lat' => -22.9, 'lon' => -43.1]);

        $this->assertEquals(['main' => ['temp' => 25]], $result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'appid=test-api-key')
                && str_contains($request->url(), 'lat=-22.9')
                && str_contains($request->url(), 'lon=-43.1');
        });
    }

    public function test_get_throws_exception_on_server_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Internal Error'], 500),
        ]);

        $this->expectException(RequestException::class);

        $client = new OpenWeatherMapClient();
        $client->get('/data/2.5/weather', ['lat' => 0, 'lon' => 0]);
    }

    public function test_get_throws_exception_on_unauthorized(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401),
        ]);

        $this->expectException(RequestException::class);

        $client = new OpenWeatherMapClient();
        $client->get('/data/2.5/weather', ['lat' => 0, 'lon' => 0]);
    }
}
