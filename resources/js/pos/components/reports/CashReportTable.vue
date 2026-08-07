<template>
  <div class="rounded-2xl bg-white p-4 shadow-md">
    <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
      Relatorio de caixas
    </h3>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-3 py-3">Nº</th>
            <th class="px-3 py-3">Data</th>
            <th class="px-3 py-3">Loja</th>
            <th class="px-3 py-3">Operador</th>
            <th class="px-3 py-3 text-right">Saldo inicial</th>
            <th class="px-3 py-3 text-right">Total vendido</th>
            <th class="px-3 py-3 text-right">Saldo esperado</th>
            <th class="px-3 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500">A carregar...</td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500">Sem caixas no periodo.</td>
          </tr>
          <tr v-for="row in rows" :key="row.id" class="border-t border-slate-100">
            <td class="px-3 py-2.5 font-semibold text-slate-900">#{{ row.id }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ formatDateTime(row.opened_at) }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ row.store?.name || '—' }}</td>
            <td class="px-3 py-2.5 text-slate-700">{{ row.operator?.name || '—' }}</td>
            <td class="px-3 py-2.5 text-right text-slate-700">
              €{{ formatMoney(row.opening_balance) }}
            </td>
            <td class="px-3 py-2.5 text-right font-semibold text-slate-900">
              €{{ formatMoney(row.sales_total) }}
            </td>
            <td class="px-3 py-2.5 text-right text-slate-700">
              €{{ formatMoney(row.expected_balance) }}
            </td>
            <td class="px-3 py-2.5">
              <span
                class="rounded-full px-2 py-1 text-xs font-bold"
                :class="
                  row.status === 'OPEN'
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-slate-200 text-slate-700'
                "
              >
                {{ row.status }}
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
  loading: { type: Boolean, default: false },
});

defineEmits(['page-change']);

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
