<?php

namespace Tests\Feature\Weather;

use App\Models\User;
use App\Services\Weather\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WeatherSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_search_requires_authentication(): void
    {
        $response = $this->getJson('/api/weather/search?city=Niteroi');

        $response->assertStatus(401);
    }

    public function test_weather_search_validates_city_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    }

    public function test_weather_search_validates_units(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather/search?city=Niteroi&units=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['units']);
    }

    public function test_weather_search_validates_lang(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather/search?city=Niteroi&lang=fr');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lang']);
    }

    public function test_weather_search_returns_data(): void
    {
        $user = User::factory()->create();

        $mockResult = [
            'location' => ['name' => 'Niterói', 'country' => 'BR', 'state' => 'RJ', 'lat' => -22.9, 'lon' => -43.1],
            'weather' => ['main' => ['temp' => 25]],
            'air_pollution' => ['list' => [['main' => ['aqi' => 2]]]],
        ];

        $weatherService = Mockery::mock(WeatherService::class);
        $weatherService->shouldReceive('getFullWeatherByCity')
            ->once()
            ->with('Niteroi', 'metric', 'en')
            ->andReturn($mockResult);

        $this->app->instance(WeatherService::class, $weatherService);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather/search?city=Niteroi&units=metric&lang=en');

        $response->assertStatus(200)
            ->assertJsonPath('location.name', 'Niterói')
            ->assertJsonStructure(['location', 'weather', 'air_pollution']);
    }

    public function test_weather_search_returns_404_when_city_not_found(): void
    {
        $user = User::factory()->create();

        $weatherService = Mockery::mock(WeatherService::class);
        $weatherService->shouldReceive('getFullWeatherByCity')
            ->once()
            ->andReturn(['error' => 'city_not_found']);

        $this->app->instance(WeatherService::class, $weatherService);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather/search?city=CidadeInexistente');

        $response->assertStatus(404);
    }

    public function test_weather_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/weather');

        $response->assertStatus(401);
    }

    public function test_weather_index_returns_config(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/weather');

        $response->assertStatus(200)
            ->assertJsonStructure(['available_units', 'available_languages', 'defaults']);
    }
}
