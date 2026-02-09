<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()->latest()->get();

        return response()->json($favorites);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'city_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'country' => ['nullable', 'string', 'max:10'],
            'state' => ['nullable', 'string', 'max:255'],
        ]);

        $favorite = $request->user()->favorites()->firstOrCreate(
            ['city_name' => $request->input('city_name')],
            $request->only(['latitude', 'longitude', 'country', 'state']),
        );

        return response()->json($favorite, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->favorites()->where('id', $id)->delete();

        return response()->json(null, 204);
    }
}
