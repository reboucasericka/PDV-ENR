<template>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <article
      v-for="card in cards"
      :key="card.key"
      class="rounded-2xl bg-white p-5 shadow-md"
    >
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
      <p class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ card.value }}</p>
      <p v-if="card.hint" class="mt-1 text-xs text-slate-400">{{ card.hint }}</p>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  summary: { type: Object, default: () => ({}) },
});

const cards = computed(() => {
  const data = props.summary || {};

  return [
    {
      key: 'total',
      label: 'Total vendido',
      value: `€${formatMoney(data.sales_total)}`,
      hint: 'Periodo filtrado',
    },
    {
      key: 'orders',
      label: 'Numero de pedidos',
      value: String(data.orders_count ?? 0),
      hint: 'Pedidos pagos',
    },
    {
      key: 'ticket',
      label: 'Ticket medio',
      value: `€${formatMoney(data.average_ticket)}`,
      hint: 'Media por pedido',
    },
    {
      key: 'product',
      label: 'Produto mais vendido',
      value: data.top_product?.name || '—',
      hint: data.top_product
        ? `${data.top_product.quantity} un. · €${formatMoney(data.top_product.revenue)}`
        : 'Sem dados',
    },
    {
      key: 'category',
      label: 'Categoria mais vendida',
      value: data.top_category?.name || '—',
      hint: data.top_category
        ? `${data.top_category.quantity} un. · €${formatMoney(data.top_category.revenue)}`
        : 'Sem dados',
    },
    {
      key: 'payment',
      label: 'Pagamento mais utilizado',
      value: data.top_payment?.label || '—',
      hint: data.top_payment
        ? `€${formatMoney(data.top_payment.total)} · ${formatMoney(data.top_payment.percent)}%`
        : 'Sem dados',
    },
  ];
});

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}
</script>
