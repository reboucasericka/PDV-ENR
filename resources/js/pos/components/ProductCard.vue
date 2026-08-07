<template>
  <button
    type="button"
    class="group relative rounded-2xl border p-3 text-left shadow-sm transition hover:scale-[1.01] hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 active:scale-[0.99]"
    :class="isClicked ? 'ring-4 ring-green-300 ring-offset-1' : ''"
    :style="cardStyle"
    :aria-label="`Adicionar ${product.name} ao carrinho`"
    @click="$emit('add', product)"
  >
    <span
      v-if="product.is_favorite"
      class="absolute right-2 top-2 text-sm"
      title="Favorito"
      aria-label="Produto favorito"
    >
      <span aria-hidden="true">⭐</span>
    </span>

    <div
      v-if="product.image"
      class="mb-2 h-16 w-full overflow-hidden rounded-xl bg-white/60"
    >
      <img :src="product.image" :alt="product.name" class="h-full w-full object-cover" />
    </div>

    <div class="line-clamp-2 min-h-12 pr-5 text-base font-bold md:text-lg">
      {{ product.name }}
    </div>
    <div class="mt-2 text-xl font-extrabold tracking-tight">
      €{{ Number(product.price).toFixed(2) }}
    </div>
    <div class="mt-1 text-xs font-semibold uppercase opacity-80">
      {{ product.category?.name || 'Sem categoria' }}
    </div>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  product: { type: Object, required: true },
  isClicked: { type: Boolean, default: false },
});

defineEmits(['add']);

const cardStyle = computed(() => {
  const color = props.product.button_color || props.product.category?.color || '#ecfdf5';
  return {
    backgroundColor: `${color}22`,
    borderColor: color,
    color: '#0f172a',
  };
});
</script>
