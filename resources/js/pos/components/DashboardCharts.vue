<template>
  <div class="grid gap-4 xl:grid-cols-2">
    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Vendas por hora</h3>
      <div class="h-64">
        <canvas ref="salesByHourRef" />
      </div>
    </article>

    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Formas de pagamento</h3>
      <div class="mx-auto h-64 max-w-sm">
        <canvas ref="paymentsRef" />
      </div>
    </article>

    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
        Produtos mais vendidos
      </h3>
      <div class="h-64">
        <canvas ref="productsRef" />
      </div>
    </article>

    <article class="rounded-2xl bg-white p-4 shadow-md">
      <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
        Categorias mais vendidas
      </h3>
      <div class="h-64">
        <canvas ref="categoriesRef" />
      </div>
    </article>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
  ArcElement,
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  DoughnutController,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js';

Chart.register(
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend
);

const props = defineProps({
  charts: {
    type: Object,
    default: () => ({}),
  },
});

const salesByHourRef = ref(null);
const paymentsRef = ref(null);
const productsRef = ref(null);
const categoriesRef = ref(null);

const charts = {
  salesByHour: null,
  payments: null,
  products: null,
  categories: null,
};

onMounted(() => {
  renderAll();
});

onBeforeUnmount(() => {
  destroyAll();
});

watch(
  () => props.charts,
  () => {
    renderAll();
  },
  { deep: true }
);

function destroyAll() {
  Object.keys(charts).forEach((key) => {
    charts[key]?.destroy();
    charts[key] = null;
  });
}

function renderAll() {
  destroyAll();

  const salesByHour = props.charts?.sales_by_hour ?? [];
  const payments = props.charts?.payment_methods ?? [];
  const topProducts = props.charts?.top_products ?? [];
  const topCategories = props.charts?.top_categories ?? [];

  if (salesByHourRef.value) {
    charts.salesByHour = new Chart(salesByHourRef.value, {
      type: 'bar',
      data: {
        labels: salesByHour.map((row) => row.label),
        datasets: [
          {
            label: '€ vendido',
            data: salesByHour.map((row) => row.total),
            backgroundColor: '#0f172a',
            borderRadius: 6,
          },
        ],
      },
      options: chartOptions('€'),
    });
  }

  if (paymentsRef.value) {
    charts.payments = new Chart(paymentsRef.value, {
      type: 'doughnut',
      data: {
        labels: payments.map((row) => row.label),
        datasets: [
          {
            data: payments.map((row) => row.total),
            backgroundColor: ['#16a34a', '#2563eb', '#ca8a04', '#db2777'],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
        },
      },
    });
  }

  if (productsRef.value) {
    charts.products = new Chart(productsRef.value, {
      type: 'bar',
      data: {
        labels: topProducts.map((row) => row.name),
        datasets: [
          {
            label: 'Qtd',
            data: topProducts.map((row) => row.quantity),
            backgroundColor: '#0369a1',
            borderRadius: 6,
          },
        ],
      },
      options: {
        ...chartOptions('un'),
        indexAxis: 'y',
      },
    });
  }

  if (categoriesRef.value) {
    charts.categories = new Chart(categoriesRef.value, {
      type: 'bar',
      data: {
        labels: topCategories.map((row) => row.name),
        datasets: [
          {
            label: 'Qtd',
            data: topCategories.map((row) => row.quantity),
            backgroundColor: '#7c2d12',
            borderRadius: 6,
          },
        ],
      },
      options: {
        ...chartOptions('un'),
        indexAxis: 'y',
      },
    });
  }
}

function chartOptions(unit) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label(context) {
            const value = context.parsed.y ?? context.parsed.x ?? context.parsed;
            return unit === '€' ? `€${Number(value).toFixed(2)}` : `${value} ${unit}`;
          },
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 },
      },
      y: {
        beginAtZero: true,
        grid: { color: '#e2e8f0' },
      },
    },
  };
}
</script>
