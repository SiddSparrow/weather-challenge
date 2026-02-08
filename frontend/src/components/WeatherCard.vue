<template>
  <div class="rounded-md bg-gray-50 p-3">
    <p class="text-xs font-medium text-gray-500">{{ label }}</p>
    <p class="mt-1 text-sm font-semibold text-gray-900">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: string
}>()
</script>
