<template>
  <div class="flex gap-2 overflow-x-auto pb-1">
    <button
      type="button"
      class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition"
      :class="
        !selectedCategoryId
          ? 'bg-slate-900 text-white'
          : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
      "
      @click="$emit('select', null)"
    >
      Todas
    </button>
    <button
      v-for="category in categories"
      :key="category.id"
      type="button"
      class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition"
      :class="
        selectedCategoryId === category.id
          ? 'text-white'
          : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
      "
      :style="
        selectedCategoryId === category.id
          ? { backgroundColor: category.color || '#0f172a' }
          : undefined
      "
      @click="$emit('select', category.id)"
    >
      {{ category.name }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  categories: { type: Array, default: () => [] },
  selectedCategoryId: { type: [Number, String], default: null },
});

defineEmits(['select']);
</script>
