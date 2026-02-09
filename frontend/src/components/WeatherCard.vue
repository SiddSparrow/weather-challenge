<template>
  <div class="rounded-xl bg-white/5 border border-white/10 p-3">
    <p class="text-xs font-medium text-white/50">{{ label }}</p>
    <p class="mt-1 text-sm font-semibold text-white">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: string
}>()
</script>
