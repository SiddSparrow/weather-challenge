<?php

namespace App\Providers;

use App\Contracts\Weather\AirPollutionRepositoryInterface;
use App\Contracts\Weather\CurrentWeatherRepositoryInterface;
use App\Contracts\Weather\ForecastRepositoryInterface;
use App\Repositories\Weather\AirPollutionRepository;
use App\Repositories\Weather\CurrentWeatherRepository;
use App\Repositories\Weather\ForecastRepository;
use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CurrentWeatherRepositoryInterface::class, CurrentWeatherRepository::class);
        $this->app->bind(ForecastRepositoryInterface::class, ForecastRepository::class);
        $this->app->bind(AirPollutionRepositoryInterface::class, AirPollutionRepository::class);
    }
}
