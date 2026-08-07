<template>
  <div :class="wrapperClass" aria-busy="true" aria-live="polite">
    <div
      v-for="n in rows"
      :key="n"
      class="animate-pulse rounded-xl bg-slate-200"
      :class="itemClass"
      :style="{ height: height }"
    />
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Number, default: 4 },
  height: { type: String, default: '3rem' },
  wrapperClass: { type: String, default: 'space-y-3' },
  itemClass: { type: String, default: 'w-full' },
});
</script>
