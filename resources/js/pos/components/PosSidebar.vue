<template>
  <aside class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-slate-500">Menu</h2>

    <nav class="space-y-2">
      <button
        v-for="item in items"
        :key="item.id"
        type="button"
        class="w-full rounded-xl px-4 py-3 text-left text-sm font-semibold transition"
        :class="
          activeItem === item.id
            ? 'bg-slate-900 text-white shadow-sm'
            : !cashOpen && item.id !== 'caixa'
              ? 'bg-slate-100 text-slate-400'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
        "
        :disabled="!cashOpen && item.id !== 'caixa'"
        @click="$emit('select', item.id)"
      >
        {{ item.label }}
      </button>
    </nav>
  </aside>
</template>

<script setup>
defineProps({
  activeItem: { type: String, required: true },
  cashOpen: { type: Boolean, required: true },
});

defineEmits(['select']);

const items = [
  { id: 'vendas', label: 'Vendas' },
  { id: 'caixa', label: 'Caixa' },
  { id: 'produtos', label: 'Produtos' },
  { id: 'relatorios', label: 'Relatorios' },
];
</script>
