<template>
  <!-- Weather Details -->
  <div class="rounded-2xl bg-white/5 border border-white/10 p-4 backdrop-blur-xl sm:p-6">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white/40">{{ t('weather.title') }}</h3>
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
  <div class="rounded-2xl bg-white/5 border border-white/10 p-4 backdrop-blur-xl sm:p-6">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white/40">{{ t('airPollution.title') }}</h3>

    <div v-if="data.air_pollution?.list?.length" class="overflow-x-auto">
      <!-- AQI Badge -->
      <div class="mb-4">
        <span class="text-sm text-white/50">{{ t('airPollution.aqi') }}:</span>
        <span
          class="ml-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold"
          :class="aqiBadgeClass"
        >
          {{ aqiLabel }}
        </span>
      </div>

      <!-- Components Table -->
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-white/10">
            <th
              v-for="col in pollutionColumns"
              :key="col.key"
              class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-white/40"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td
              v-for="col in pollutionColumns"
              :key="col.key"
              class="whitespace-nowrap px-3 py-3 text-sm text-white/80"
            >
              {{ data.air_pollution.list[0].components[col.key]?.toFixed(2) ?? '-' }} <span class="text-xs text-white/40">μg/m³</span>
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
    1: 'bg-green-500/20 text-green-300 border border-green-500/30',
    2: 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
    3: 'bg-orange-500/20 text-orange-300 border border-orange-500/30',
    4: 'bg-red-500/20 text-red-300 border border-red-500/30',
    5: 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
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
