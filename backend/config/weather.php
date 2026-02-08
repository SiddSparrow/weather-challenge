<?php

return [
    'api_key' => env('WEATHER_API_KEY'),
    'base_url' => env('WEATHER_BASE_URL', 'https://api.openweathermap.org'),
    'default_units' => env('WEATHER_DEFAULT_UNITS', 'metric'),
    'timeout' => env('WEATHER_TIMEOUT', 10),
];
