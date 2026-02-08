<?php

namespace App\Services\Weather;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class OpenWeatherMapClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('weather.base_url');
        $this->apiKey = config('weather.api_key');
        $this->timeout = config('weather.timeout');
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function get(string $endpoint, array $params = []): array
    {
        $params['appid'] = $this->apiKey;

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->get($endpoint, $params)
            ->throw();

        return $response->json();
    }
}
