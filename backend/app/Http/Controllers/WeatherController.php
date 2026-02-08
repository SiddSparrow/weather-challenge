<?php

namespace App\Http\Controllers;

use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(
        private readonly WeatherService $weatherService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'available_units' => ['metric', 'imperial', 'standard'],
            'available_languages' => ['en', 'es', 'pt'],
            'defaults' => [
                'units' => 'metric',
                'language' => 'en',
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'city' => ['required', 'string', 'max:255'],
            'units' => ['sometimes', 'string', 'in:metric,imperial,standard'],
            'lang' => ['sometimes', 'string', 'in:en,es,pt'],
        ]);

        $result = $this->weatherService->getFullWeatherByCity(
            city: $request->input('city'),
            units: $request->input('units', 'metric'),
            lang: $request->input('lang', 'en'),
        );

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 404);
        }

        return response()->json($result);
    }
}
