<template>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article
      v-for="card in cards"
      :key="card.key"
      class="rounded-2xl border-0 bg-white p-5 shadow-md"
    >
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
      <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ card.value }}</p>
      <p v-if="card.hint" class="mt-1 text-xs text-slate-400">{{ card.hint }}</p>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
});

const cards = computed(() => {
  const source = props.data || {};
  const cashOpen = source.cash_status === 'OPEN';

  return [
    {
      key: 'sales',
      label: 'Vendas Hoje',
      value: String(source.sales_count_today ?? 0),
      hint: 'Pedidos pagos',
    },
    {
      key: 'revenue',
      label: 'Faturamento Hoje',
      value: `€${formatMoney(source.revenue_today)}`,
      hint: 'Total liquido do dia',
    },
    {
      key: 'ticket',
      label: 'Ticket Medio',
      value: `€${formatMoney(source.average_ticket)}`,
      hint: 'Media por pedido',
    },
    {
      key: 'cash',
      label: 'Estado do Caixa',
      value: cashOpen ? 'ABERTO' : 'FECHADO',
      hint: cashOpen ? 'Operacao em curso' : 'Sem caixa ativo',
    },
  ];
});

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}
</script>
