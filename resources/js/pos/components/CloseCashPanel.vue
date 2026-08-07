<template>
  <section class="flex h-full items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-md">
      <h2 class="text-2xl font-bold text-slate-900">Fechar caixa</h2>
      <p class="mt-1 text-sm text-slate-500">
        Resumo do dia antes de confirmar o fechamento.
      </p>

      <div class="mt-6 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Loja</span>
          <span class="font-semibold text-slate-900">{{ storeName || '—' }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Operador</span>
          <span class="font-semibold text-slate-900">{{ operatorName || '—' }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Saldo inicial</span>
          <span class="font-semibold text-slate-900">€{{ formatMoney(summary.opening_balance) }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Pedidos</span>
          <span class="font-semibold text-slate-900">{{ summary.orders_count ?? 0 }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Total vendido</span>
          <span class="font-semibold text-slate-900">€{{ formatMoney(summary.sales_total) }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Saldo esperado</span>
          <span class="font-semibold text-slate-900">€{{ formatMoney(summary.expected_balance) }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Abertura</span>
          <span class="font-semibold text-slate-900">{{ formatDateTime(summary.opened_at) }}</span>
        </div>
        <div class="flex justify-between gap-4">
          <span class="text-slate-500">Fechamento</span>
          <span class="font-semibold text-slate-900">{{ formatDateTime(summary.closed_at) }}</span>
        </div>
      </div>

      <div class="mt-4 rounded-xl border border-slate-200 p-4">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
          Por forma de pagamento
        </p>
        <div
          v-for="item in paymentTotals"
          :key="item.method"
          class="flex justify-between py-1 text-sm"
        >
          <span class="text-slate-600">{{ item.label }}</span>
          <span class="font-semibold text-slate-900">€{{ formatMoney(item.total) }}</span>
        </div>
      </div>

      <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <button
          type="button"
          class="flex-1 rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          :disabled="closing"
          @click="$emit('keep-open')"
        >
          Continuar vendendo
        </button>
        <button
          type="button"
          class="flex-1 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
          :disabled="closing"
          @click="$emit('confirm-close')"
        >
          {{ closing ? 'A fechar...' : 'Confirmar Fechamento' }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({}),
  },
  storeName: {
    type: String,
    default: '',
  },
  operatorName: {
    type: String,
    default: '',
  },
  closing: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['confirm-close', 'keep-open']);

const paymentTotals = computed(() => props.summary?.payment_totals ?? []);

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
</script>
