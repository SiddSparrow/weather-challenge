<template>
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

<script setup lang="ts">
import { computed } from 'vue'
import type { WeatherData } from '../composables/useWeather'
import WeatherCard from './WeatherCard.vue'

const props = defineProps<{
  data: WeatherData
  units: string
  unitSymbol: string
  t: (key: string) => string
}>()

const aqiLabel = computed(() => {
  if (!props.data.air_pollution?.list?.length) return ''
  const aqi = props.data.air_pollution.list[0].main.aqi
  return props.t(`airPollution.aqiLevels.${aqi}`)
})

const aqiBadgeClass = computed(() => {
  if (!props.data.air_pollution?.list?.length) return ''
  const aqi = props.data.air_pollution.list[0].main.aqi
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
    label: props.t(`airPollution.${key}`),
  }))
})
</script>
