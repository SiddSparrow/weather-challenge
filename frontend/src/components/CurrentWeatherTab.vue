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

  <!-- Today's Forecast -->
  <div v-if="todayItems.length" class="rounded-2xl bg-white/5 border border-white/10 p-4 backdrop-blur-xl sm:p-6">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white/40">{{ t('tabs.todayForecast') }}</h3>
    <div class="space-y-2">
      <div
        v-for="item in todayItems"
        :key="item.dt"
        class="rounded-xl bg-white/5 border border-white/10 overflow-hidden backdrop-blur-sm"
      >
        <!-- Card Summary -->
        <button
          @click="toggle(item.dt)"
          class="w-full px-4 py-3 flex items-center gap-3 text-left hover:bg-white/5 transition-colors"
        >
          <span class="text-sm font-medium text-white/50 w-12 shrink-0">
            {{ formatTime(item.dt_txt) }}
          </span>
          <img
            v-if="item.weather[0]?.icon"
            :src="`https://openweathermap.org/img/wn/${item.weather[0].icon}.png`"
            :alt="item.weather[0]?.main"
            class="h-8 w-8 shrink-0"
          />
          <span class="text-sm text-white/80 w-24 shrink-0">
            {{ Math.round(item.main.temp_min) }}{{ unitSymbol }} / {{ Math.round(item.main.temp_max) }}{{ unitSymbol }}
          </span>
          <span v-if="item.rain?.['3h']" class="text-sm text-blue-400 w-16 shrink-0">
            {{ item.rain['3h'] }} mm
          </span>
          <span v-else-if="item.snow?.['3h']" class="text-sm text-blue-300 w-16 shrink-0">
            {{ item.snow['3h'] }} mm
          </span>
          <span v-else class="text-sm text-white/30 w-16 shrink-0">
            0.00 mm
          </span>
          <span class="hidden sm:block text-sm text-white/50 capitalize flex-1 truncate">
            {{ item.weather[0]?.description }}
          </span>
          <svg
            class="h-5 w-5 text-white/30 shrink-0 transition-transform"
            :class="{ 'rotate-180': expanded.has(item.dt) }"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Expanded Details -->
        <div v-if="expanded.has(item.dt)" class="border-t border-white/10 px-4 py-3 bg-white/5">
          <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
            <div>
              <span class="text-white/40">{{ t('forecast.feelsLike') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ Math.round(item.main.feels_like) }}{{ unitSymbol }}</span>
            </div>
            <div>
              <span class="text-white/40">{{ t('forecast.humidity') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ item.main.humidity }}%</span>
            </div>
            <div>
              <span class="text-white/40">{{ t('forecast.wind') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ item.wind.speed }} {{ units === 'imperial' ? 'mph' : 'm/s' }}</span>
            </div>
            <div>
              <span class="text-white/40">{{ t('forecast.clouds') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ item.clouds.all }}%</span>
            </div>
            <div>
              <span class="text-white/40">{{ t('forecast.pop') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ Math.round(item.pop * 100) }}%</span>
            </div>
            <div>
              <span class="text-white/40">{{ t('weather.pressure') }}:</span>
              <span class="ml-1 font-medium text-white/80">{{ item.main.pressure }} hPa</span>
            </div>
          </div>
        </div>
      </div>
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
import { computed, reactive } from 'vue'
import type { WeatherData, ForecastItem } from '../composables/useWeather'
import WeatherCard from './WeatherCard.vue'

const props = defineProps<{
  data: WeatherData
  units: string
  unitSymbol: string
  t: (key: string) => string
  todayForecast: ForecastItem[]
}>()

const expanded = reactive(new Set<number>())

function toggle(dt: number) {
  if (expanded.has(dt)) {
    expanded.delete(dt)
  } else {
    expanded.add(dt)
  }
}

function formatTime(dtTxt: string): string {
  const timePart = dtTxt.split(' ')[1]
  return timePart?.substring(0, 5) ?? ''
}

const todayItems = computed(() => props.todayForecast ?? [])

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
