<template>
  <div
    role="button"
    tabindex="0"
    class="rounded-lg border px-3 py-2 text-sm shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
    :class="selected ? 'border-slate-900 bg-slate-100 ring-2 ring-slate-300' : 'border-transparent bg-white'"
    :aria-pressed="selected ? 'true' : 'false'"
    :aria-label="`Selecionar ${item.name}`"
    @click="$emit('select', item.id)"
    @keydown.enter.prevent="$emit('select', item.id)"
    @keydown.space.prevent="$emit('select', item.id)"
  >
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0 flex-1">
        <p class="truncate font-semibold text-slate-800">{{ item.name }}</p>
        <p class="text-slate-500">
          {{ item.quantity }} x €{{ Number(item.price).toFixed(2) }}
        </p>
        <p class="mt-1 text-xs font-semibold text-slate-700">
          Subtotal: €{{ subtotal.toFixed(2) }}
        </p>
      </div>

      <div class="flex flex-col items-end gap-2">
        <div class="flex items-center gap-1">
          <button
            type="button"
            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-bold hover:bg-slate-100"
            :aria-label="`Diminuir quantidade de ${item.name}`"
            @click.stop="$emit('decrease', item.id)"
          >
            -
          </button>
          <input
            :value="item.quantity"
            type="number"
            min="1"
            class="w-12 rounded-md border border-slate-300 px-1 py-1 text-center text-xs font-bold"
            :aria-label="`Quantidade de ${item.name}`"
            @click.stop
            @change="onQuantityChange"
          />
          <button
            type="button"
            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-bold hover:bg-slate-100"
            :aria-label="`Aumentar quantidade de ${item.name}`"
            @click.stop="$emit('increase', item.id)"
          >
            +
          </button>
        </div>
        <button
          type="button"
          class="text-xs font-semibold text-red-600 hover:text-red-700"
          :aria-label="`Remover ${item.name} do carrinho`"
          @click.stop="$emit('remove', item.id)"
        >
          Remover
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  item: { type: Object, required: true },
  selected: { type: Boolean, default: false },
});

const emit = defineEmits(['increase', 'decrease', 'remove', 'select', 'set-quantity']);

const subtotal = computed(() => Number(props.item.price) * Number(props.item.quantity));

function onQuantityChange(event) {
  const value = Number(event.target.value);
  emit('set-quantity', props.item.id, value);
}
</script>
