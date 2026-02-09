<template>
  <div class="flex min-h-screen items-center justify-center bg-linear-to-br from-slate-900 via-blue-950 to-indigo-950 px-4">
    <div class="w-full max-w-sm space-y-8">
      <div class="text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm">
          <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Weather Challenge</h1>
        <p class="mt-2 text-sm text-white/50">Entre com sua conta</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5 rounded-2xl bg-white/10 p-6 shadow-xl shadow-black/10 backdrop-blur-xl border border-white/10 sm:p-8">
        <div v-if="errorMessage" class="rounded-xl bg-red-500/20 border border-red-500/30 p-3 text-sm text-red-300">
          {{ errorMessage }}
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-white/70">E-mail</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="seu@email.com"
            class="mt-1 block w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white shadow-sm placeholder:text-white/30 focus:border-blue-400/50 focus:outline-none focus:ring-1 focus:ring-blue-400/50"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-white/70">Senha</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="mt-1 block w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white shadow-sm placeholder:text-white/30 focus:border-blue-400/50 focus:outline-none focus:ring-1 focus:ring-blue-400/50"
          />
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full rounded-xl bg-linear-to-r from-blue-500 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:shadow-xl hover:shadow-blue-500/30 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:hover:shadow-lg"
        >
          {{ isLoading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="text-center text-sm text-white/40">
        Não tem conta?
        <router-link to="/register" class="font-semibold text-blue-400 hover:text-blue-300 transition-colors">
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
