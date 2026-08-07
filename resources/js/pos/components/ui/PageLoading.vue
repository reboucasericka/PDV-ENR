<template>
  <div class="flex h-full items-center justify-center rounded-2xl bg-white p-8 text-center shadow-md">
    <div>
      <div
        class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-slate-900"
      />
      <p class="text-lg font-semibold text-slate-900">{{ title }}</p>
      <p v-if="subtitle" class="mt-1 text-sm text-slate-500">{{ subtitle }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'A carregar...' },
  subtitle: { type: String, default: '' },
});
</script>
