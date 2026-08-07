<template>
  <div class="grid gap-4 xl:grid-cols-2">
    <div class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
        Relatorio de pagamentos
      </h3>
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
              <th class="px-3 py-3">Forma</th>
              <th class="px-3 py-3 text-right">Pedidos</th>
              <th class="px-3 py-3 text-right">Percentual</th>
              <th class="px-3 py-3 text-right">Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="px-3 py-8 text-center text-slate-500">A carregar...</td>
            </tr>
            <tr v-else-if="rows.length === 0">
              <td colspan="4" class="px-3 py-8 text-center text-slate-500">Sem dados.</td>
            </tr>
            <tr v-for="row in rows" :key="row.method" class="border-t border-slate-100">
              <td class="px-3 py-2.5 font-semibold text-slate-900">{{ row.label }}</td>
              <td class="px-3 py-2.5 text-right text-slate-700">{{ row.count }}</td>
              <td class="px-3 py-2.5 text-right text-slate-700">{{ formatMoney(row.percent) }}%</td>
              <td class="px-3 py-2.5 text-right font-semibold text-slate-900">
                €{{ formatMoney(row.total) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
        Distribuicao por pagamento
      </h3>
      <div class="mx-auto h-64 max-w-sm">
        <canvas ref="chartRef" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { ArcElement, Chart, DoughnutController, Legend, Tooltip } from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const props = defineProps({
  rows: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const chartRef = ref(null);
let chart = null;

onMounted(renderChart);
onBeforeUnmount(() => chart?.destroy());

watch(
  () => props.rows,
  () => renderChart(),
  { deep: true }
);

function renderChart() {
  if (!chartRef.value) return;
  chart?.destroy();

  chart = new Chart(chartRef.value, {
    type: 'doughnut',
    data: {
      labels: props.rows.map((row) => row.label),
      datasets: [
        {
          data: props.rows.map((row) => row.total),
          backgroundColor: ['#16a34a', '#2563eb', '#db2777', '#ca8a04'],
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } },
    },
  });
}

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}
</script>
