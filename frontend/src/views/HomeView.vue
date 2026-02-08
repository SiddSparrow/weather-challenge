<template>
  <div class="max-w-7xl mx-auto py-6 px-4">
    <div class="bg-white overflow-hidden shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Welcome</h2>
      <p class="text-gray-600">
        API Status:
        <span :class="apiStatus === 'ok' ? 'text-green-600' : 'text-red-600'" class="font-medium">
          {{ apiStatus || 'checking...' }}
        </span>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const apiStatus = ref<string>('')

onMounted(async () => {
  try {
    const response = await axios.get('/api/health')
    apiStatus.value = response.data.status
  } catch {
    apiStatus.value = 'error'
  }
})
</script>
