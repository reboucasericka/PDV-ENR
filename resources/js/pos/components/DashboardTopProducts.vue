<template>
  <div class="grid gap-4 xl:grid-cols-2">
    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
        Top produtos (hoje)
      </h3>
      <div class="space-y-2">
        <div
          v-if="products.length === 0"
          class="py-8 text-center text-sm text-slate-500"
        >
          Sem vendas de produtos hoje.
        </div>
        <div
          v-for="(product, index) in products"
          :key="product.product_id || product.name"
          class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5"
        >
          <div class="flex min-w-0 items-center gap-3">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white"
            >
              {{ index + 1 }}
            </span>
            <div class="min-w-0">
              <p class="truncate font-semibold text-slate-900">{{ product.name }}</p>
              <p class="text-xs text-slate-500">{{ product.quantity }} un.</p>
            </div>
          </div>
          <p class="shrink-0 font-bold text-slate-900">€{{ formatMoney(product.total) }}</p>
        </div>
      </div>
    </article>

    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Indicadores</h3>
      <div class="grid grid-cols-2 gap-3">
        <div
          v-for="item in indicatorCards"
          :key="item.label"
          class="rounded-xl border border-slate-200 p-3"
        >
          <p class="text-xs uppercase text-slate-500">{{ item.label }}</p>
          <p class="mt-1 text-2xl font-black text-slate-900">{{ item.value }}</p>
        </div>
      </div>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  products: { type: Array, default: () => [] },
  indicators: { type: Object, default: () => ({}) },
});

const indicatorCards = computed(() => [
  {
    label: 'Produtos ativos',
    value: String(props.indicators.products_active ?? 0),
  },
  {
    label: 'Produtos inativos',
    value: String(props.indicators.products_inactive ?? 0),
  },
  {
    label: 'Categorias',
    value: String(props.indicators.categories ?? 0),
  },
  {
    label: 'Pedidos do dia',
    value: String(props.indicators.orders_today ?? 0),
  },
  {
    label: 'Valor vendido hoje',
    value: `€${formatMoney(props.indicators.revenue_today)}`,
  },
]);

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}
</script>
