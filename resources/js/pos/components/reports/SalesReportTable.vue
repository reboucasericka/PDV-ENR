<template>
  <div class="rounded-2xl bg-white p-4 shadow-md">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Relatorio de vendas</h3>
      <input
        :value="search"
        type="search"
        placeholder="Pesquisar pedido, loja, operador..."
        class="input-field w-full max-w-xs"
        @input="$emit('update:search', $event.target.value)"
      />
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-3 py-3">Numero</th>
            <th class="px-3 py-3">Data</th>
            <th class="px-3 py-3">Operador</th>
            <th class="px-3 py-3">Loja</th>
            <th class="px-3 py-3">Pagamento</th>
            <th class="px-3 py-3 text-right">Valor</th>
            <th class="px-3 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500">A carregar...</td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500">Sem vendas no periodo.</td>
          </tr>
          <tr v-for="row in rows" :key="row.id" class="border-t border-slate-100">
            <td class="px-3 py-2.5 font-semibold text-slate-900">
              {{ row.reference || `#${row.id}` }}
            </td>
            <td class="px-3 py-2.5 text-slate-700">{{ formatDateTime(row.created_at) }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ row.operator?.name || '—' }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ row.store?.name || '—' }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ row.payment_label }}</td>
            <td class="px-3 py-2.5 text-right font-semibold text-slate-900">
              €{{ formatMoney(row.total) }}
            </td>
            <td class="px-3 py-2.5">
              <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800">
                {{ String(row.status || '').toUpperCase() }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-slate-500">
        {{ meta.total || 0 }} registo(s) · pagina {{ meta.current_page || 1 }} de
        {{ meta.last_page || 1 }}
      </p>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40"
          :disabled="loading || (meta.current_page || 1) <= 1"
          @click="$emit('page-change', (meta.current_page || 1) - 1)"
        >
          Anterior
        </button>
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40"
          :disabled="loading || (meta.current_page || 1) >= (meta.last_page || 1)"
          @click="$emit('page-change', (meta.current_page || 1) + 1)"
        >
          Seguinte
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  search: { type: String, default: '' },
  loading: { type: Boolean, default: false },
});

defineEmits(['update:search', 'page-change']);

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

function formatDateTime(value) {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<style scoped>
.input-field {
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  background: #fff;
  padding: 0.55rem 0.75rem;
  font-size: 0.875rem;
}
</style>
