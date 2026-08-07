<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex justify-end">
      <button
        type="button"
        class="absolute inset-0 bg-slate-900/40"
        aria-label="Fechar detalhe"
        @click="$emit('close')"
      />

      <aside
        class="relative flex h-full w-full max-w-xl flex-col bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cash-detail-title"
      >
        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
          <div>
            <h2 id="cash-detail-title" class="text-xl font-bold text-slate-900">
              Caixa #{{ detail?.id ?? '—' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Detalhe do fechamento e pedidos associados.
            </p>
          </div>
          <button
            type="button"
            class="rounded-lg px-2 py-1 text-sm font-semibold text-slate-500 hover:bg-slate-100"
            @click="$emit('close')"
          >
            Fechar
          </button>
        </div>

        <div v-if="loading" class="flex flex-1 items-center justify-center text-slate-500">
          A carregar detalhe...
        </div>

        <div v-else-if="!detail" class="flex flex-1 items-center justify-center text-slate-500">
          Sem dados para este caixa.
        </div>

        <div v-else class="flex-1 space-y-5 overflow-y-auto p-5">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Loja</p>
              <p class="mt-1 font-semibold text-slate-900">{{ detail.store?.name || '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Operador</p>
              <p class="mt-1 font-semibold text-slate-900">{{ detail.operator?.name || '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Saldo inicial</p>
              <p class="mt-1 font-semibold text-slate-900">€{{ formatMoney(detail.opening_balance) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Saldo final</p>
              <p class="mt-1 font-semibold text-slate-900">€{{ formatMoney(detail.closing_balance) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Abertura</p>
              <p class="mt-1 font-semibold text-slate-900">{{ formatDateTime(detail.opened_at) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Fechamento</p>
              <p class="mt-1 font-semibold text-slate-900">{{ formatDateTime(detail.closed_at) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Tempo aberto</p>
              <p class="mt-1 font-semibold text-slate-900">{{ detail.duration_label || '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Pedidos / Vendido</p>
              <p class="mt-1 font-semibold text-slate-900">
                {{ detail.orders_count ?? 0 }} · €{{ formatMoney(detail.sales_total) }}
              </p>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
              Totais por forma de pagamento
            </p>
            <div
              v-for="item in detail.payment_totals || []"
              :key="item.method"
              class="flex justify-between py-1 text-sm"
            >
              <span class="text-slate-600">{{ item.label }}</span>
              <span class="font-semibold text-slate-900">€{{ formatMoney(item.total) }}</span>
            </div>
          </div>

          <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
              Pedidos do caixa
            </p>
            <div class="overflow-hidden rounded-xl border border-slate-200">
              <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                  <tr>
                    <th class="px-3 py-2">Pedido</th>
                    <th class="px-3 py-2">Hora</th>
                    <th class="px-3 py-2">Pagamento</th>
                    <th class="px-3 py-2 text-right">Valor</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="(detail.orders || []).length === 0">
                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">
                      Nenhum pedido neste caixa.
                    </td>
                  </tr>
                  <tr
                    v-for="order in detail.orders || []"
                    :key="order.id"
                    class="border-t border-slate-100"
                  >
                    <td class="px-3 py-2 font-semibold text-slate-900">
                      {{ order.reference || `#${order.id}` }}
                    </td>
                    <td class="px-3 py-2 text-slate-600">{{ formatTime(order.created_at) }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ order.payment_label }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-slate-900">
                      €{{ formatMoney(order.total) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="flex gap-3 border-t border-slate-200 p-4">
          <button
            type="button"
            class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            @click="$emit('reprint')"
          >
            Reimprimir Fechamento
          </button>
          <button
            type="button"
            class="flex-1 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800"
            @click="$emit('export-pdf')"
          >
            Exportar PDF
          </button>
        </div>
      </aside>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  detail: { type: Object, default: null },
});

defineEmits(['close', 'reprint', 'export-pdf']);

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

function formatDateTime(value) {
  if (!value) {
    return '—';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return '—';
  }

  return date.toLocaleString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatTime(value) {
  if (!value) {
    return '—';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return '—';
  }

  return date.toLocaleTimeString('pt-PT', {
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
