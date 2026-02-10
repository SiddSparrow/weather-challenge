Video: https://youtu.be/WhCysTFcDa4<br>
LinkedIn: www.linkedin.com/in/fabioduqueestrada


# Weather Challenge

A full-stack weather application built with **Laravel 12** and **Vue.js 3**, featuring real-time weather data, 3-day forecasts, air quality monitoring, and multi-language support.

## Tech Stack

| Layer          | Technology                                      |
|----------------|-------------------------------------------------|
| Backend        | PHP 8.2, Laravel 12, Laravel Sanctum            |
| Frontend       | Vue 3, TypeScript 5.7, Tailwind CSS 4, Vite 6   |
| Database       | PostgreSQL 16                                   |
| Cache/Session  | Redis 7                                         |
| Infrastructure | Docker, Docker Compose, Nginx                   |
| CI/CD          | GitHub Actions                                  |
| External API   | OpenWeatherMap API                              |

## Features

- **User Authentication** - Registration and login with token-based auth (Laravel Sanctum)
- **City Weather Search** - Current weather data with detailed metrics (temperature, humidity, pressure, wind, visibility, clouds)
- **3-Day Forecast** - Hourly forecast grouped by day with expandable details
- **Air Quality Index** - Pollution data with AQI levels and component breakdown (CO, NO, NO2, O3, SO2, PM2.5, PM10, NH3)
- **Favorites** - Save and quick-access favorite cities
- **Internationalization** - Full i18n support for English, Spanish, and Portuguese
- **Temperature Units** - Switch between Celsius, Fahrenheit, and Kelvin with automatic data refresh
- **Smart Caching** - Redis-based caching with differentiated TTLs per data type
- **Responsive UI** - Mobile-first glassmorphism design with dark theme

## Architecture

### Infrastructure Overview

```
                                          ┌─────────────────────────┐
                                          │   OpenWeatherMap API    │
                                          └────────────▲────────────┘
                                                       │
                                                       │ HTTP
                                                       │
┌──────────┐       ┌──────────────┐       ┌────────────┴────────────┐       ┌──────────────┐
│          │ :5173 │              │       │                         │       │              │
│   User   ├──────►│   Frontend   │       │      Laravel API        │       │  PostgreSQL  │
│ (Browser)│◄──────┤  Vue 3 + TS  │       │                         |       │     :5432    │
│          │       │    Vite      │       │                         ├──────►│              │
└──────────┘       │   :5173      │       │      Controllers        │       │  Users       │
                   └──────┬───────┘       │           │             |       |  Favorites   |
                          |               |           ▼             │◄──────┤              │
                          │               │        Services         │       │              │
                          │  /api/*       │           │             |       └──────────────┘
                          |               |           ▼             │       
                          ▼               │      Repositories       │
                   ┌──────────────┐       │           │             |
                   |              |       |           ▼             │       ┌──────────────┐
                   │              │       │      Cache Layer ──────────────►│              │
                   │    Nginx     ├──────►│                         │◄──────┤    Redis     │
                   │    :8000     │       │     Auth (Sanctum)      │       │    :6379     │
                   │              │◄──────┤     Rate Limiting       │       │              │
                   └──────────────┘       │     Validation          │       │  Cache       │
                                          │                         │       │  Sessions    │
                                          └─────────────────────────┘       └──────────────┘
```

### Backend - Repository + Service Pattern

```
Controller (HTTP layer)
    │
Service (orchestration)
    │
Repository (data access + caching)
    │
OpenWeatherMap API
```

Each data source is abstracted behind an interface, bound through Laravel's service container:

| Repository        | Interface                          | Cache TTL |
|-------------------|------------------------------------|-----------|
| GeoCoding         | GeoCodingRepositoryInterface       | 24 hours  |
| Current Weather   | CurrentWeatherRepositoryInterface  | 10 min    |
| Forecast          | ForecastRepositoryInterface        | 30 min    |
| Air Pollution     | AirPollutionRepositoryInterface    | 30 min    |

This approach enables:
- Dependency injection and loose coupling
- Easy unit testing with mocked interfaces
- Swappable implementations (e.g., change weather provider without touching business logic)
- SOLID principles compliance (particularly Dependency Inversion)

### Frontend - Composables Architecture

```
Views (pages)
    |
Components (UI)
    |
Composables (state + logic)
    |
Axios (HTTP)
```

| Composable     | Responsibility                                    |
|----------------|--------------------------------------------------|
| `useWeather`   | Search state, loading, error handling, API calls  |
| `useFavorites` | CRUD operations, optimistic state management      |
| `useI18n`      | Reactive translations, locale switching           |

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose
- [OpenWeatherMap API Key](https://openweathermap.org/api) (free tier)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/weather-challenge.git
   cd weather-challenge
   ```

2. **Configure environment**
   ```bash
   cp backend/.env.example backend/.env
   ```
   Add your OpenWeatherMap API key to `backend/.env`:
   ```
   WEATHER_API_KEY=your_api_key_here
   ```

3. **Start the containers**
   ```bash
   docker compose up -d
   ```

4. **Install dependencies and run migrations**
   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```

5. **Access the application**
   - Frontend: http://localhost:5173
   - API: http://localhost:8000/api

## Docker Services

| Service    | Container          | Port | Description              |
|------------|--------------------|------|--------------------------|
| app        | weather-app        | -    | PHP 8.2-FPM (Laravel)    |
| nginx      | weather-nginx      | 8000 | Reverse proxy            |
| frontend   | weather-frontend   | 5173 | Vite dev server (Vue)    |
| postgres   | weather-postgres   | 5432 | PostgreSQL 16            |
| redis      | weather-redis      | 6379 | Redis 7 (cache/session)  |

## API Endpoints

### Public

| Method | Endpoint         | Description          |
|--------|-----------------|----------------------|
| POST   | `/api/register`  | Create account       |
| POST   | `/api/login`     | Authenticate         |
| GET    | `/api/health`    | Health check         |

### Protected (requires Bearer token)

| Method | Endpoint              | Description                 |
|--------|-----------------------|-----------------------------|
| POST   | `/api/logout`         | Revoke token                |
| GET    | `/api/me`             | Current user profile        |
| GET    | `/api/weather`        | Available units & languages |
| GET    | `/api/weather/search` | Search weather by city      |
| GET    | `/api/favorites`      | List favorites              |
| POST   | `/api/favorites`      | Add favorite                |
| DELETE | `/api/favorites/{id}` | Remove favorite             |

### Authentication

All protected endpoints require the `Authorization` header:
```
Authorization: Bearer {token}
```

Unauthenticated requests return `401`:
```json
{ "message": "Unauthenticated." }
```

### Weather Search

```
GET /api/weather/search?city=London&units=metric&lang=en
```

**Parameters:**

| Param  | Type   | Default  | Description                           |
|--------|--------|----------|---------------------------------------|
| city   | string | required | City name                             |
| units  | string | metric   | `metric` / `imperial` / `standard`    |
| lang   | string | en       | `en` / `es` / `pt`                    |

**Response:**
```json
{
  "location": { "name": "London", "lat": 51.5074, "lon": -0.1278, "country": "GB" },
  "weather": { "main": { "temp": 12.5, "feels_like": 11.2, ... }, ... },
  "air_pollution": { "list": [{ "main": { "aqi": 2 }, "components": { ... } }] },
  "forecast": { "list": [ ... ], "city": { ... } }
}
```

## Testing

The project uses PHPUnit with **37 tests** and **101 assertions** covering unit and feature levels.

```bash
docker compose exec app php artisan test
```

### Test Configuration

| Setting        | Value       | Reason                          |
|----------------|-------------|--------------------------------|
| Database       | SQLite :memory: | Fast, no external dependency |
| Cache          | Array       | Isolated, no Redis needed      |
| Session        | Array       | Stateless test execution       |
| Bcrypt Rounds  | 4           | Faster password hashing        |

### Test Coverage

**Unit Tests:**
- `WeatherServiceTest` - Service orchestration, repository delegation, error handling
- `CurrentWeatherRepositoryTest` - API calls, cache hits, default parameters
- `ForecastRepositoryTest` - Forecast data retrieval and caching
- `AirPollutionRepositoryTest` - Pollution data retrieval and caching
- `GeoCodingRepositoryTest` - Geocoding resolution and caching
- `OpenWeatherMapClientTest` - HTTP client behavior and error handling

**Feature Tests:**
- `LoginTest` - Authentication flow, validation, error responses
- `RegisterTest` - Registration, unique email constraint, password rules
- `WeatherSearchTest` - End-to-end search with mocked repositories

## Caching Strategy

Redis caching is applied at the repository level with TTLs calibrated to data volatility:

```
weather:geocoding:{city}              -> 24 hours (coordinates don't change)
weather:current:{lat}:{lon}:{units}:{lang}  -> 10 minutes (frequently updated)
weather:forecast:{lat}:{lon}:{units}:{lang} -> 30 minutes (moderate volatility)
weather:air_pollution:{lat}:{lon}           -> 30 minutes (moderate volatility)
```

This reduces API calls by serving repeated requests from cache while keeping data reasonably fresh.

## CI/CD

GitHub Actions pipeline runs on push/PR to `main` and `develop`:

**Backend Job:**
- PHP 8.2 with PostgreSQL 16 and Redis 7 services
- Composer install, migrations, test suite execution

**Frontend Job:**
- Node.js 20 with npm caching
- TypeScript type checking (`vue-tsc`) and production build (`vite build`)

## Project Structure

```
weather-challenge/
|-- backend/
|   |-- app/
|   |   |-- Contracts/Weather/          # Repository interfaces
|   |   |-- Http/Controllers/           # API controllers
|   |   |-- Http/Requests/              # Form request validation
|   |   |-- Models/                     # Eloquent models
|   |   |-- Providers/                  # Service providers (DI bindings)
|   |   |-- Repositories/Weather/       # Repository implementations
|   |   |-- Services/Weather/           # Business logic orchestration
|   |-- config/weather.php              # API configuration
|   |-- database/migrations/            # Database schema
|   |-- routes/api.php                  # API route definitions
|   |-- tests/
|       |-- Unit/                       # Isolated unit tests
|       |-- Feature/                    # Integration tests
|-- frontend/
|   |-- src/
|       |-- components/                 # Reusable UI components
|       |-- composables/                # State management (useWeather, useFavorites)
|       |-- i18n/                       # Translation files (en, es, pt)
|       |-- router/                     # Vue Router with auth guards
|       |-- views/                      # Page-level components
|-- docker/nginx/                       # Nginx configuration
|-- docker-compose.yml                  # Container orchestration
|-- .github/workflows/ci.yml           # CI pipeline
```

## Environment Variables

| Variable            | Description                  | Default                            |
|---------------------|------------------------------|------------------------------------|
| `WEATHER_API_KEY`   | OpenWeatherMap API key       | -                                  |
| `WEATHER_BASE_URL`  | API base URL                 | `https://api.openweathermap.org`   |
| `WEATHER_TIMEOUT`   | HTTP request timeout (sec)   | `10`                               |
| `DB_CONNECTION`     | Database driver              | `pgsql`                            |
| `DB_HOST`           | Database host                | `postgres`                         |
| `DB_DATABASE`       | Database name                | `weather_challenge`                |
| `CACHE_STORE`       | Cache driver                 | `redis`                            |
| `REDIS_HOST`        | Redis host                   | `redis`                            |
| `FRONTEND_URL`      | Frontend URL (CORS)          | `http://localhost:5173`            |

## License

This project was built as a technical challenge.
