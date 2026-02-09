<template>
  <div class="min-h-screen bg-linear-to-br from-slate-900 via-blue-950 to-indigo-950">
    <!-- Header -->
    <header class="border-b border-white/10 backdrop-blur-sm">
      <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />
            </svg>
          </div>
          <h1 class="text-lg font-bold text-white sm:text-xl">{{ t('app.title') }}</h1>
        </div>
        <button
          @click="handleLogout"
          class="flex items-center gap-2 rounded-xl bg-white/5 border border-white/10 px-3 py-2 text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all"
          title="Logout"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
          </svg>
          <span class="hidden sm:inline">Logout</span>
        </button>
      </div>
    </header>

    <!-- Loading overlay for locale change -->
    <div
      v-if="isChangingLocale"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm"
    >
      <div class="text-center">
        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-blue-400 border-t-transparent"></div>
        <p class="mt-2 text-sm text-white/60">{{ t('app.loading') }}</p>
      </div>
    </div>

    <main class="mx-auto max-w-5xl px-4 py-6 space-y-6">
      <!-- Search Form -->
      <div class="rounded-2xl bg-white/5 border border-white/10 p-4 backdrop-blur-xl sm:p-6">
        <form @submit.prevent="handleSearch" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <!-- City -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-white/60">{{ t('form.city') }}</label>
            <input
              v-model="city"
              type="text"
              :placeholder="t('form.cityPlaceholder')"
              class="mt-1 block w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white shadow-sm placeholder:text-white/30 focus:border-blue-400/50 focus:outline-none focus:ring-1 focus:ring-blue-400/50"
            />
          </div>

          <!-- Temperature Unit -->
          <div>
            <label class="block text-sm font-medium text-white/60">{{ t('form.unit') }}</label>
            <select
              v-model="units"
              @change="handleTempChange"
              class="mt-1 block w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white shadow-sm focus:border-blue-400/50 focus:outline-none focus:ring-1 focus:ring-blue-400/50 [&>option]:bg-slate-800 [&>option]:text-white"
            >
              <option value="metric">{{ t('units.celsius') }}</option>
              <option value="imperial">{{ t('units.fahrenheit') }}</option>
              <option value="standard">{{ t('units.kelvin') }}</option>
            </select>
          </div>

          <!-- Language -->
          <div>
            <label class="block text-sm font-medium text-white/60">{{ t('form.language') }}</label>
            <select
              v-model="selectedLocale"
              @change="handleLocaleChange"
              class="mt-1 block w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white shadow-sm focus:border-blue-400/50 focus:outline-none focus:ring-1 focus:ring-blue-400/50 [&>option]:bg-slate-800 [&>option]:text-white"
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
              class="w-full rounded-xl bg-linear-to-r from-blue-500 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:shadow-xl hover:shadow-blue-500/30 hover:brightness-110 disabled:opacity-50 sm:w-auto"
            >
              {{ isLoading ? t('app.loading') : t('app.search') }}
            </button>
          </div>
        </form>

        <!-- Error -->
        <div v-if="error" class="mt-4 rounded-xl bg-red-500/20 border border-red-500/30 p-3 text-sm text-red-300">
          {{ error === 'city_not_found' ? t('errors.cityNotFound') : t('errors.apiError') }}
        </div>
      </div>

      <!-- Favorites -->
      <div class="rounded-2xl bg-white/5 border border-white/10 p-4 backdrop-blur-xl sm:p-6">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-white/40">{{ t('favorites.title') }}</h3>
        <div v-if="isFavoritesLoading" class="flex items-center gap-2">
          <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></div>
          <span class="text-sm text-white/50">{{ t('app.loading') }}</span>
        </div>
        <p v-else-if="!favorites.length" class="text-sm text-white/40">{{ t('favorites.empty') }}</p>
        <div v-else class="flex flex-wrap gap-2">
          <div
            v-for="fav in favorites"
            :key="fav.id"
            class="group flex items-center gap-1 rounded-full bg-white/10 border border-white/10 px-3 py-1.5 text-sm text-white/80 transition-all hover:bg-white/15"
          >
            <button
              @click="searchFavorite(fav)"
              class="hover:text-white transition-colors"
            >
              {{ fav.city_name }}
              <span v-if="fav.country" class="text-white/40">{{ fav.country }}</span>
            </button>
            <button
              @click="removeFavorite(fav.id)"
              class="ml-1 text-white/30 hover:text-red-400 transition-colors"
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
          <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-blue-400 border-t-transparent"></div>
          <p class="mt-3 text-sm text-white/50">{{ t('app.loading') }}</p>
        </div>
      </div>

      <!-- Weather Results -->
      <template v-if="data && !isLoading">
        <!-- Location Banner -->
        <div class="rounded-2xl bg-linear-to-r from-blue-600/40 to-indigo-600/40 border border-white/10 p-4 backdrop-blur-xl shadow-xl sm:p-6">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-lg font-bold text-white sm:text-xl">
                {{ data.location.name }}
                <span v-if="data.location.state" class="font-normal text-white/60">, {{ data.location.state }}</span>
                <span class="font-normal text-white/60"> - {{ data.location.country }}</span>
              </h2>
              <div class="mt-1 flex items-center gap-2">
                <img
                  v-if="data.weather.weather[0]?.icon"
                  :src="`https://openweathermap.org/img/wn/${data.weather.weather[0].icon}@2x.png`"
                  :alt="data.weather.weather[0]?.description"
                  class="h-16 w-16 -ml-2"
                />
                <p class="text-4xl font-bold text-white sm:text-5xl">
                  {{ Math.round(data.weather.main.temp) }}{{ unitSymbol }}
                </p>
              </div>
              <p class="mt-1 capitalize text-white/70">
                {{ data.weather.weather[0]?.description }}
              </p>
            </div>
            <button
              @click="handleToggleFavorite"
              class="shrink-0 p-2 rounded-full transition-all hover:bg-white/10"
              :title="isFavorite(data.location.name) ? t('favorites.remove') : t('favorites.add')"
            >
              <!-- Filled heart -->
              <svg v-if="isFavorite(data.location.name)" class="h-6 w-6 text-red-400 drop-shadow-[0_0_6px_rgba(248,113,113,0.5)]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
              </svg>
              <!-- Outline heart -->
              <svg v-else class="h-6 w-6 text-white/40 hover:text-red-300 transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 8.25c0-3.105-2.464-5.25-5.437-5.25A5.5 5.5 0 0012 5.052 5.5 5.5 0 007.688 3C4.714 3 2.25 5.145 2.25 8.25c0 3.925 2.438 7.111 4.739 9.256a25.175 25.175 0 004.244 3.17c.11.065.217.124.317.176a.752.752 0 00.704 0c.1-.052.208-.111.317-.176a25.175 25.175 0 004.244-3.17C19.312 15.36 21.75 12.174 21.75 8.25z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-white/10">
          <button
            @click="activeTab = 'current'"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === 'current'
              ? 'border-blue-400 text-blue-400'
              : 'border-transparent text-white/40 hover:text-white/70 hover:border-white/20'"
          >
            {{ t('tabs.current') }}
          </button>
          <button
            @click="activeTab = 'forecast'"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === 'forecast'
              ? 'border-blue-400 text-blue-400'
              : 'border-transparent text-white/40 hover:text-white/70 hover:border-white/20'"
          >
            {{ t('tabs.forecast') }}
          </button>
        </div>

        <!-- Tab Content -->
        <CurrentWeatherTab
          v-if="activeTab === 'current'"
          :data="data"
          :units="units"
          :unit-symbol="unitSymbol"
          :t="tFn"
        />

        <ForecastTab
          v-if="activeTab === 'forecast' && data.forecast"
          :forecast="data.forecast"
          :units="units"
          :unit-symbol="unitSymbol"
          :t="tFn"
          :locale="selectedLocale"
        />
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
import CurrentWeatherTab from '../components/CurrentWeatherTab.vue'
import ForecastTab from '../components/ForecastTab.vue'

const router = useRouter()
const { t, locale, isChangingLocale, setLocale } = useI18n()
const { data, isLoading, error, searchWeather } = useWeather()
const { favorites, isLoading: isFavoritesLoading, loadFavorites, toggleFavorite, removeFavorite, isFavorite } = useFavorites()

const city = ref('')
const units = ref('metric')
const selectedLocale = ref(locale.value)
const activeTab = ref<'current' | 'forecast'>('current')

onMounted(() => {
  loadFavorites()
})

const unitSymbol = computed(() => {
  const symbols: Record<string, string> = { metric: '°C', imperial: '°F', standard: 'K' }
  return symbols[units.value]
})

const tFn = computed(() => (key: string) => t.value(key))

async function handleLocaleChange() {
  await setLocale(selectedLocale.value as 'en' | 'es' | 'pt')
  if (city.value && data.value) {
    await searchWeather(city.value, units.value, selectedLocale.value)
  }
}

async function handleTempChange() {
  if (city.value && data.value) {
    await searchWeather(city.value, units.value, selectedLocale.value)
  }
}

function handleSearch() {
  if (!city.value.trim()) return
  activeTab.value = 'current'
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
  activeTab.value = 'current'
  searchWeather(fav.city_name, units.value, selectedLocale.value)
}

watch(locale, (newLocale: string) => {
  selectedLocale.value = newLocale as 'en' | 'es' | 'pt'
})
</script>
