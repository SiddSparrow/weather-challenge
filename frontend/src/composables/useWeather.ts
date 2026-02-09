import { ref } from 'vue'
import axios from 'axios'

export interface ForecastItem {
  dt: number
  dt_txt: string
  main: {
    temp: number
    feels_like: number
    temp_min: number
    temp_max: number
    pressure: number
    humidity: number
    sea_level?: number
    grnd_level?: number
  }
  weather: Array<{
    id: number
    main: string
    description: string
    icon: string
  }>
  clouds: { all: number }
  wind: { speed: number; deg: number; gust?: number }
  visibility: number
  pop: number
  rain?: { '3h'?: number }
  snow?: { '3h'?: number }
  sys: { pod: string }
}

export interface ForecastData {
  list: ForecastItem[]
  city: {
    name: string
    coord: { lat: number; lon: number }
    country: string
    timezone: number
    sunrise: number
    sunset: number
  }
}

export interface WeatherData {
  location: {
    name: string
    country: string
    state: string
    lat: number
    lon: number
  }
  weather: {
    main: {
      temp: number
      feels_like: number
      temp_min: number
      temp_max: number
      pressure: number
      humidity: number
      sea_level?: number
    }
    visibility: number
    weather: Array<{
      main: string
      description: string
      icon: string
    }>
    wind: {
      speed: number
      deg: number
      gust?: number
    }
    clouds: {
      all: number
    }
    rain?: {
      '1h'?: number
      '3h'?: number
    }
    snow?: {
      '1h'?: number
      '3h'?: number
    }
  }
  air_pollution: {
    list: Array<{
      main: {
        aqi: number
      }
      components: Record<string, number>
    }>
  }
  forecast: ForecastData
}

export function useWeather() {
  const data = ref<WeatherData | null>(null)
  const isLoading = ref(false)
  const error = ref('')

  async function searchWeather(city: string, units: string, lang: string) {
    isLoading.value = true
    error.value = ''
    data.value = null

    try {
      const token = localStorage.getItem('token')
      const response = await axios.get('/api/weather/search', {
        params: { city, units, lang },
        headers: { Authorization: `Bearer ${token}` },
      })
      data.value = response.data
    } catch (err: unknown) {
      if (axios.isAxiosError(err) && err.response?.status === 404) {
        error.value = 'city_not_found'
      } else {
        error.value = 'api_error'
      }
    } finally {
      isLoading.value = false
    }
  }

  return {
    data,
    isLoading,
    error,
    searchWeather,
  }
}
