<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ t('app.title') }}</h1>
        <button
          @click="handleLogout"
          class="text-gray-400 hover:text-gray-600"
          title="Logout"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
          </svg>
        </button>
      </div>
    </header>

    <!-- Loading overlay for locale change -->
    <div
      v-if="isChangingLocale"
      class="fixed inset-0 z-50 flex items-center justify-center bg-white/80"
    >
      <div class="text-center">
        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
        <p class="mt-2 text-sm text-gray-600">{{ t('app.loading') }}</p>
      </div>
    </div>

    <main class="mx-auto max-w-5xl px-4 py-6 space-y-6">
      <!-- Search Form -->
      <div class="rounded-lg bg-white p-4 shadow sm:p-6">
        <form @submit.prevent="handleSearch" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <!-- City -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ t('form.city') }}</label>
            <input
              v-model="city"
              type="text"
              :placeholder="t('form.cityPlaceholder')"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>

          <!-- Temperature Unit -->
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('form.unit') }}</label>
            <select
              v-model="units"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="metric">{{ t('units.celsius') }}</option>
              <option value="imperial">{{ t('units.fahrenheit') }}</option>
              <option value="standard">{{ t('units.kelvin') }}</option>
            </select>
          </div>

          <!-- Language -->
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('form.language') }}</label>
            <select
              v-model="selectedLocale"
              @change="handleLocaleChange"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="en">{{ t('languages.en') }}</option>
              <option value="es">{{ t('languages.es') }}</option>
              <option value="pt">{{ t('languages.pt') }}</option>
            </select>
          </div>

          <!-- Search Button -->
          <div class="sm:col-span-4">
            <button
              type="submit"
              :disabled="isLoading"
              class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
            >
              {{ isLoading ? t('app.loading') : t('app.search') }}
            </button>
          </div>
        </form>

        <!-- Error -->
        <div v-if="error" class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
          {{ error === 'city_not_found' ? t('errors.cityNotFound') : t('errors.apiError') }}
        </div>
      </div>

      <!-- Favorites -->
      <div class="rounded-lg bg-white p-4 shadow sm:p-6">
        <h3 class="mb-3 text-lg font-semibold text-gray-800">{{ t('favorites.title') }}</h3>
        <div v-if="isFavoritesLoading" class="flex items-center gap-2">
          <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
          <span class="text-sm text-gray-500">{{ t('app.loading') }}</span>
        </div>
        <p v-else-if="!favorites.length" class="text-sm text-gray-500">{{ t('favorites.empty') }}</p>
        <div v-else class="flex flex-wrap gap-2">
          <div
            v-for="fav in favorites"
            :key="fav.id"
            class="group flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1.5 text-sm text-blue-700"
          >
            <button
              @click="searchFavorite(fav)"
              class="hover:underline"
            >
              {{ fav.city_name }}
              <span v-if="fav.country" class="text-blue-400">{{ fav.country }}</span>
            </button>
            <button
              @click="removeFavorite(fav.id)"
              class="ml-1 text-blue-300 hover:text-red-500"
              :title="t('favorites.remove')"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="flex justify-center py-12">
        <div class="text-center">
          <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
          <p class="mt-3 text-sm text-gray-500">{{ t('app.loading') }}</p>
        </div>
      </div>

      <!-- Weather Results -->
      <template v-if="data && !isLoading">
        <!-- Location -->
        <div class="rounded-lg bg-blue-600 p-4 text-white shadow sm:p-6">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-lg font-bold sm:text-xl">
                {{ data.location.name }}
                <span v-if="data.location.state" class="font-normal text-blue-200">, {{ data.location.state }}</span>
                <span class="font-normal text-blue-200"> - {{ data.location.country }}</span>
              </h2>
              <p class="mt-1 text-4xl font-bold sm:text-5xl">
                {{ Math.round(data.weather.main.temp) }}{{ unitSymbol }}
              </p>
              <p class="mt-1 capitalize text-blue-100">
                {{ data.weather.weather[0]?.description }}
              </p>
            </div>
            <button
              @click="handleToggleFavorite"
              class="shrink-0 p-1 transition-colors"
              :title="isFavorite(data.location.name) ? t('favorites.remove') : t('favorites.add')"
            >
              <!-- Filled star -->
              <svg v-if="isFavorite(data.location.name)" class="h-7 w-7 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <!-- Outline star -->
              <svg v-else class="h-7 w-7 text-blue-200 hover:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Weather Details -->
        <div class="rounded-lg bg-white p-4 shadow sm:p-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-800">{{ t('weather.title') }}</h3>
          <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <WeatherCard :label="t('weather.temperature')" :value="`${data.weather.main.temp}${unitSymbol}`" />
            <WeatherCard :label="t('weather.feelsLike')" :value="`${data.weather.main.feels_like}${unitSymbol}`" />
            <WeatherCard :label="t('weather.tempMin')" :value="`${data.weather.main.temp_min}${unitSymbol}`" />
            <WeatherCard :label="t('weather.tempMax')" :value="`${data.weather.main.temp_max}${unitSymbol}`" />
            <WeatherCard :label="t('weather.pressure')" :value="`${data.weather.main.pressure} hPa`" />
            <WeatherCard :label="t('weather.humidity')" :value="`${data.weather.main.humidity}%`" />
            <WeatherCard :label="t('weather.seaLevel')" :value="data.weather.main.sea_level ? `${data.weather.main.sea_level} hPa` : t('weather.noData')" />
            <WeatherCard :label="t('weather.visibility')" :value="`${(data.weather.visibility / 1000).toFixed(1)} km`" />
            <WeatherCard :label="t('weather.condition')" :value="data.weather.weather[0]?.main ?? t('weather.noData')" />
            <WeatherCard :label="t('weather.description')" :value="data.weather.weather[0]?.description ?? t('weather.noData')" class="capitalize" />
            <WeatherCard :label="t('weather.windSpeed')" :value="`${data.weather.wind.speed} ${units === 'imperial' ? 'mph' : 'm/s'}`" />
            <WeatherCard :label="t('weather.windDirection')" :value="`${data.weather.wind.deg}°`" />
            <WeatherCard :label="t('weather.windGust')" :value="data.weather.wind.gust ? `${data.weather.wind.gust} ${units === 'imperial' ? 'mph' : 'm/s'}` : t('weather.noData')" />
            <WeatherCard :label="t('weather.clouds')" :value="`${data.weather.clouds.all}%`" />
            <WeatherCard :label="t('weather.rain')" :value="data.weather.rain ? `${data.weather.rain['1h'] ?? data.weather.rain['3h'] ?? 0} mm` : t('weather.noData')" />
            <WeatherCard :label="t('weather.snow')" :value="data.weather.snow ? `${data.weather.snow['1h'] ?? data.weather.snow['3h'] ?? 0} mm` : t('weather.noData')" />
          </div>
        </div>

        <!-- Air Pollution Table -->
        <div class="rounded-lg bg-white p-4 shadow sm:p-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-800">{{ t('airPollution.title') }}</h3>

          <div v-if="data.air_pollution?.list?.length" class="overflow-x-auto">
            <!-- AQI Badge -->
            <div class="mb-4">
              <span class="text-sm text-gray-600">{{ t('airPollution.aqi') }}:</span>
              <span
                class="ml-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                :class="aqiBadgeClass"
              >
                {{ aqiLabel }}
              </span>
            </div>

            <!-- Components Table -->
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    v-for="col in pollutionColumns"
                    :key="col.key"
                    class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                  >
                    {{ col.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr>
                  <td
                    v-for="col in pollutionColumns"
                    :key="col.key"
                    class="whitespace-nowrap px-3 py-3 text-sm text-gray-700"
                  >
                    {{ data.air_pollution.list[0].components[col.key]?.toFixed(2) ?? '-' }} <span class="text-xs text-gray-400">μg/m³</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from '../i18n'
import { useWeather } from '../composables/useWeather'
import { useFavorites } from '../composables/useFavorites'
import type { FavoriteItem } from '../composables/useFavorites'
import WeatherCard from '../components/WeatherCard.vue'

const router = useRouter()
const { t, locale, isChangingLocale, setLocale } = useI18n()
const { data, isLoading, error, searchWeather } = useWeather()
const { favorites, isLoading: isFavoritesLoading, loadFavorites, toggleFavorite, removeFavorite, isFavorite } = useFavorites()

const city = ref('')
const units = ref('metric')
const selectedLocale = ref(locale.value)

onMounted(() => {
  loadFavorites()
})

const unitSymbol = computed(() => {
  const symbols: Record<string, string> = { metric: '°C', imperial: '°F', standard: 'K' }
  return symbols[units.value]
})

const aqiLabel = computed(() => {
  if (!data.value?.air_pollution?.list?.length) return ''
  const aqi = data.value.air_pollution.list[0].main.aqi
  return t.value(`airPollution.aqiLevels.${aqi}`)
})

const aqiBadgeClass = computed(() => {
  if (!data.value?.air_pollution?.list?.length) return ''
  const aqi = data.value.air_pollution.list[0].main.aqi
  const classes: Record<number, string> = {
    1: 'bg-green-100 text-green-800',
    2: 'bg-yellow-100 text-yellow-800',
    3: 'bg-orange-100 text-orange-800',
    4: 'bg-red-100 text-red-800',
    5: 'bg-purple-100 text-purple-800',
  }
  return classes[aqi] ?? ''
})

const pollutionColumns = computed(() => {
  const keys = ['co', 'no', 'no2', 'o3', 'so2', 'pm2_5', 'pm10', 'nh3']
  return keys.map((key) => ({
    key,
    label: t.value(`airPollution.${key}`),
  }))
})

async function handleLocaleChange() {
  await setLocale(selectedLocale.value as 'en' | 'es' | 'pt')
  if (city.value && data.value) {
    await searchWeather(city.value, units.value, selectedLocale.value)
  }
}

function handleSearch() {
  if (!city.value.trim()) return
  searchWeather(city.value, units.value, selectedLocale.value)
}

function handleLogout() {
  localStorage.removeItem('token')
  router.push({ name: 'login' })
}

async function handleToggleFavorite() {
  if (!data.value) return
  await toggleFavorite({
    name: data.value.location.name,
    lat: data.value.location.lat,
    lon: data.value.location.lon,
    country: data.value.location.country,
    state: data.value.location.state,
  })
}

function searchFavorite(fav: FavoriteItem) {
  city.value = fav.city_name
  searchWeather(fav.city_name, units.value, selectedLocale.value)
}

watch(locale, (newLocale: string) => {
  selectedLocale.value = newLocale as 'en' | 'es' | 'pt'
})
</script>
