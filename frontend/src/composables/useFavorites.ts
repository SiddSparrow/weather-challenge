import { ref, computed } from 'vue'
import axios from 'axios'

export interface FavoriteItem {
  id: number
  city_name: string
  latitude: number
  longitude: number
  country: string | null
  state: string | null
}

const favorites = ref<FavoriteItem[]>([])
const isLoading = ref(false)

function getHeaders() {
  return { Authorization: `Bearer ${localStorage.getItem('token')}` }
}

export function useFavorites() {
  async function loadFavorites() {
    isLoading.value = true
    try {
      const response = await axios.get('/api/favorites', { headers: getHeaders() })
      favorites.value = response.data
    } catch {
      favorites.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function addFavorite(location: { name: string; lat: number; lon: number; country: string; state: string }) {
    try {
      const response = await axios.post('/api/favorites', {
        city_name: location.name,
        latitude: location.lat,
        longitude: location.lon,
        country: location.country,
        state: location.state,
      }, { headers: getHeaders() })
      favorites.value.unshift(response.data)
    } catch {
      // silently fail if already exists
    }
  }

  async function removeFavorite(id: number) {
    try {
      await axios.delete(`/api/favorites/${id}`, { headers: getHeaders() })
      favorites.value = favorites.value.filter((f) => f.id !== id)
    } catch {
      // silently fail
    }
  }

  async function toggleFavorite(location: { name: string; lat: number; lon: number; country: string; state: string }) {
    const existing = favorites.value.find((f) => f.city_name === location.name)
    if (existing) {
      await removeFavorite(existing.id)
    } else {
      await addFavorite(location)
    }
  }

  function isFavorite(cityName: string): boolean {
    return favorites.value.some((f) => f.city_name === cityName)
  }

  const favoriteId = computed(() => (cityName: string) => {
    return favorites.value.find((f) => f.city_name === cityName)?.id ?? null
  })

  return {
    favorites,
    isLoading,
    loadFavorites,
    addFavorite,
    removeFavorite,
    toggleFavorite,
    isFavorite,
    favoriteId,
  }
}
