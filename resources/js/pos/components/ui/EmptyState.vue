<template>
  <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <div
      class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400"
      aria-hidden="true"
    >
      {{ icon }}
    </div>
    <h3 class="text-base font-bold text-slate-900">{{ title }}</h3>
    <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500">
      {{ description }}
    </p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  icon: { type: String, default: '📭' },
});
</script>
