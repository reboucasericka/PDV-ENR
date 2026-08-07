<template>
  <div class="grid grid-cols-3 gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
    <div>
      <p class="text-[10px] font-semibold uppercase text-slate-500">Itens</p>
      <p class="text-lg font-extrabold text-slate-900">{{ itemsCount }}</p>
    </div>
    <div>
      <p class="text-[10px] font-semibold uppercase text-slate-500">Produtos</p>
      <p class="text-lg font-extrabold text-slate-900">{{ unitsCount }}</p>
    </div>
    <div>
      <p class="text-[10px] font-semibold uppercase text-slate-500">Total</p>
      <p class="text-lg font-extrabold text-slate-900">€{{ Number(total).toFixed(2) }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  itemsCount: { type: Number, required: true },
  unitsCount: { type: Number, required: true },
  total: { type: Number, required: true },
});
</script>
