<template>
  <div class="space-y-4">
    <div v-for="(items, day) in groupedByDay" :key="day">
      <!-- Day Header -->
      <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ day }}</h4>

      <div class="space-y-2">
        <div
          v-for="item in items"
          :key="item.dt"
          class="rounded-lg bg-white shadow overflow-hidden"
        >
          <!-- Card Summary -->
          <button
            @click="toggle(item.dt)"
            class="w-full px-4 py-3 flex items-center gap-3 text-left hover:bg-gray-50 transition-colors"
          >
            <!-- Time -->
            <span class="text-sm font-medium text-gray-500 w-12 shrink-0">
              {{ formatTime(item.dt_txt) }}
            </span>

            <!-- Weather Icon -->
            <img
              v-if="item.weather[0]?.icon"
              :src="`https://openweathermap.org/img/wn/${item.weather[0].icon}.png`"
              :alt="item.weather[0]?.main"
              class="h-8 w-8 shrink-0"
            />

            <!-- Temp Min / Max -->
            <span class="text-sm text-gray-700 w-24 shrink-0">
              {{ Math.round(item.main.temp_min) }}{{ unitSymbol }} / {{ Math.round(item.main.temp_max) }}{{ unitSymbol }}
            </span>

            <!-- Rain -->
            <span v-if="item.rain?.['3h']" class="text-sm text-blue-600 w-16 shrink-0">
              {{ item.rain['3h'] }} mm
            </span>
            <span v-else-if="item.snow?.['3h']" class="text-sm text-blue-400 w-16 shrink-0">
              {{ item.snow['3h'] }} mm
            </span>
            <span v-else class="w-16 shrink-0">
              0.00 mm
            </span>

            <!-- Description (hidden on mobile) -->
            <span class="hidden sm:block text-sm text-gray-500 capitalize flex-1 truncate">
              {{ item.weather[0]?.description }}
            </span>

            <!-- Expand Icon -->
            <svg
              class="h-5 w-5 text-gray-400 shrink-0 transition-transform"
              :class="{ 'rotate-180': expanded.has(item.dt) }"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Expanded Details -->
          <div v-if="expanded.has(item.dt)" class="border-t border-gray-100 px-4 py-3 bg-gray-50">
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
              <div>
                <span class="text-gray-500">{{ t('forecast.feelsLike') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ Math.round(item.main.feels_like) }}{{ unitSymbol }}</span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('forecast.humidity') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ item.main.humidity }}%</span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('forecast.wind') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ item.wind.speed }} {{ units === 'imperial' ? 'mph' : 'm/s' }}</span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('forecast.clouds') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ item.clouds.all }}%</span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('forecast.pop') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ Math.round(item.pop * 100) }}%</span>
              </div>
              <div>
                <span class="text-gray-500">{{ t('weather.pressure') }}:</span>
                <span class="ml-1 font-medium text-gray-800">{{ item.main.pressure }} hPa</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive } from 'vue'
import type { ForecastData } from '../composables/useWeather'

const props = defineProps<{
  forecast: ForecastData
  units: string
  unitSymbol: string
  t: (key: string) => string
  locale: string
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

function formatDayLabel(dtTxt: string): string {
  const datePart = dtTxt.split(' ')[0]
  const [year, month, day] = datePart.split('-').map(Number)
  const date = new Date(year, month - 1, day)

  const localeMap: Record<string, string> = { en: 'en-US', es: 'es-ES', pt: 'pt-BR' }
  return date.toLocaleDateString(localeMap[props.locale] ?? 'en-US', {
    weekday: 'long',
    day: 'numeric',
    month: 'short',
  })
}

const groupedByDay = computed(() => {
  const groups: Record<string, typeof props.forecast.list> = {}
  for (const item of props.forecast.list) {
    const datePart = item.dt_txt.split(' ')[0]
    const label = formatDayLabel(item.dt_txt)
    const key = `${datePart}|${label}`
    if (!groups[key]) groups[key] = []
    groups[key].push(item)
  }
  // Return with formatted labels as keys
  const result: Record<string, typeof props.forecast.list> = {}
  for (const [key, items] of Object.entries(groups)) {
    const label = key.split('|')[1]
    result[label] = items
  }
  return result
})
</script>
