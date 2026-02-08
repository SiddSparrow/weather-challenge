<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-sm space-y-6">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Weather Challenge</h1>
        <p class="mt-2 text-sm text-gray-600">Entre com sua conta</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4 rounded-lg bg-white p-6 shadow sm:p-8">
        <div v-if="errorMessage" class="rounded-md bg-red-50 p-3 text-sm text-red-700">
          {{ errorMessage }}
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="seu@email.com"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
        >
          {{ isLoading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-600">
        Não tem conta?
        <router-link to="/register" class="font-semibold text-blue-600 hover:text-blue-500">
          Criar conta
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const isLoading = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
})

async function handleLogin() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await axios.post('/api/login', {
      email: form.email,
      password: form.password,
    })

    localStorage.setItem('token', response.data.token)
    router.push({ name: 'home' })
  } catch (err: unknown) {
    if (axios.isAxiosError(err) && err.response?.status === 401) {
      errorMessage.value = 'E-mail ou senha inválidos.'
    } else {
      errorMessage.value = 'Erro ao conectar. Tente novamente.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
