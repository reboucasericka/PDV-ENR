<template>
  <div class="rounded-2xl bg-white p-4 shadow-md">
    <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
      Relatorio de produtos
    </h3>
    <div class="overflow-x-auto rounded-xl border border-slate-200">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-3 py-3">Produto</th>
            <th class="px-3 py-3 text-right">Qtd vendida</th>
            <th class="px-3 py-3 text-right">Receita</th>
            <th class="px-3 py-3 text-right">Preco medio</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="px-3 py-8 text-center text-slate-500">A carregar...</td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td colspan="4" class="px-3 py-8 text-center text-slate-500">Sem produtos no periodo.</td>
          </tr>
          <tr v-for="row in rows" :key="row.product_id || row.name" class="border-t border-slate-100">
            <td class="px-3 py-2.5 font-semibold text-slate-900">{{ row.name }}</td>
            <td class="px-3 py-2.5 text-right text-slate-700">{{ row.quantity }}</td>
            <td class="px-3 py-2.5 text-right font-semibold text-slate-900">
              €{{ formatMoney(row.revenue) }}
            </td>
            <td class="px-3 py-2.5 text-right text-slate-700">€{{ formatMoney(row.average_price) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}
</script>
