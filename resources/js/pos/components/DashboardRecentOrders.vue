<template>
  <div class="grid gap-4 xl:grid-cols-2">
    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Ultimos pedidos</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
              <th class="px-2 py-2">Pedido</th>
              <th class="px-2 py-2">Loja</th>
              <th class="px-2 py-2">Pagamento</th>
              <th class="px-2 py-2 text-right">Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="orders.length === 0">
              <td colspan="4" class="px-2 py-6 text-center text-slate-500">Sem pedidos recentes.</td>
            </tr>
            <tr
              v-for="order in orders"
              :key="order.id"
              class="border-b border-slate-100"
            >
              <td class="px-2 py-2.5">
                <p class="font-semibold text-slate-900">{{ order.reference || `#${order.id}` }}</p>
                <p class="text-xs text-slate-500">{{ formatDateTime(order.created_at) }}</p>
              </td>
              <td class="px-2 py-2.5 text-slate-700">{{ order.store_name || '—' }}</td>
              <td class="px-2 py-2.5 text-slate-700">{{ order.payment_label }}</td>
              <td class="px-2 py-2.5 text-right font-semibold text-slate-900">
                €{{ formatMoney(order.total) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
        Ultimos caixas fechados
      </h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
              <th class="px-2 py-2">Caixa</th>
              <th class="px-2 py-2">Loja</th>
              <th class="px-2 py-2">Operador</th>
              <th class="px-2 py-2 text-right">Saldo final</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="closedCash.length === 0">
              <td colspan="4" class="px-2 py-6 text-center text-slate-500">
                Sem caixas fechados recentes.
              </td>
            </tr>
            <tr
              v-for="cash in closedCash"
              :key="cash.id"
              class="border-b border-slate-100"
            >
              <td class="px-2 py-2.5">
                <p class="font-semibold text-slate-900">#{{ cash.id }}</p>
                <p class="text-xs text-slate-500">{{ formatDateTime(cash.closed_at) }}</p>
              </td>
              <td class="px-2 py-2.5 text-slate-700">{{ cash.store_name || '—' }}</td>
              <td class="px-2 py-2.5 text-slate-700">{{ cash.operator_name || '—' }}</td>
              <td class="px-2 py-2.5 text-right font-semibold text-slate-900">
                €{{ formatMoney(cash.closing_balance) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </div>
</template>

<script setup>
defineProps({
  orders: { type: Array, default: () => [] },
  closedCash: { type: Array, default: () => [] },
});

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
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
