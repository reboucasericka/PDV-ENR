<template>
  <section class="flex h-full flex-col overflow-hidden">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 md:text-3xl">Relatórios</h1>
        <p class="text-sm text-slate-500">Analise de vendas, produtos, pagamentos e caixas.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          type="button"
          class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          :disabled="loading"
          @click="exportCsv"
        >
          Exportar CSV
        </button>
        <button
          type="button"
          class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          @click="exportPdfPlaceholder"
        >
          Exportar PDF
        </button>
        <button
          type="button"
          class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
          :disabled="loading"
          @click="loadAll"
        >
          {{ loading ? 'A atualizar...' : 'Atualizar' }}
        </button>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        class="rounded-xl px-4 py-2 text-sm font-semibold transition"
        :class="
          activeTab === tab.id
            ? 'bg-slate-900 text-white'
            : 'bg-white text-slate-700 shadow-sm hover:bg-slate-100'
        "
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto pb-2">
      <DateFilter
        v-model="filters"
        :options="filterOptions"
        @apply="onFilterApply"
        @reset="onFilterApply"
      />

      <SummaryCards v-if="activeTab === 'resumo'" :summary="summary" />

      <SalesReportTable
        v-else-if="activeTab === 'vendas'"
        :rows="salesRows"
        :meta="salesMeta"
        :search="salesSearch"
        :loading="loadingSales"
        @update:search="onSalesSearch"
        @page-change="onSalesPage"
      />

      <div v-else-if="activeTab === 'produtos'" class="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
        <ProductReportTable :rows="productRows" :loading="loadingProducts" />
        <div class="rounded-2xl bg-white p-4 shadow-md">
          <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
            Top produtos
          </h3>
          <div class="h-72">
            <canvas ref="productsChartRef" />
          </div>
        </div>
      </div>

      <div v-else-if="activeTab === 'categorias'" class="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
        <CategoryReportTable :rows="categoryRows" :loading="loadingCategories" />
        <div class="rounded-2xl bg-white p-4 shadow-md">
          <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
            Top categorias
          </h3>
          <div class="h-72">
            <canvas ref="categoriesChartRef" />
          </div>
        </div>
      </div>

      <PaymentReportTable
        v-else-if="activeTab === 'pagamentos'"
        :rows="paymentRows"
        :loading="loadingPayments"
      />

      <CashReportTable
        v-else-if="activeTab === 'caixas'"
        :rows="cashRows"
        :meta="cashMeta"
        :loading="loadingCash"
        @page-change="onCashPage"
      />
    </div>
  </section>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import {
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js';
import { toast } from 'vue-sonner';
import CashReportTable from '../components/reports/CashReportTable.vue';
import CategoryReportTable from '../components/reports/CategoryReportTable.vue';
import DateFilter from '../components/reports/DateFilter.vue';
import PaymentReportTable from '../components/reports/PaymentReportTable.vue';
import ProductReportTable from '../components/reports/ProductReportTable.vue';
import SalesReportTable from '../components/reports/SalesReportTable.vue';
import SummaryCards from '../components/reports/SummaryCards.vue';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const tabs = [
  { id: 'resumo', label: 'Resumo' },
  { id: 'vendas', label: 'Vendas' },
  { id: 'produtos', label: 'Produtos' },
  { id: 'categorias', label: 'Categorias' },
  { id: 'pagamentos', label: 'Pagamentos' },
  { id: 'caixas', label: 'Caixas' },
];

const activeTab = ref('resumo');
const loading = ref(false);
const loadingSales = ref(false);
const loadingProducts = ref(false);
const loadingCategories = ref(false);
const loadingPayments = ref(false);
const loadingCash = ref(false);

const filters = reactive({
  date_from: '',
  date_to: '',
  store_id: '',
  user_id: '',
  payment_method: '',
});

const filterOptions = reactive({
  stores: [],
  operators: [],
  payment_methods: [],
});

const summary = ref({});
const salesRows = ref([]);
const salesMeta = reactive({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const salesSearch = ref('');
const salesPage = ref(1);
const productRows = ref([]);
const categoryRows = ref([]);
const paymentRows = ref([]);
const cashRows = ref([]);
const cashMeta = reactive({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
const cashPage = ref(1);

const productsChartRef = ref(null);
const categoriesChartRef = ref(null);
let productsChart = null;
let categoriesChart = null;
let searchTimer = null;

onMounted(loadAll);
onBeforeUnmount(() => {
  productsChart?.destroy();
  categoriesChart?.destroy();
  if (searchTimer) clearTimeout(searchTimer);
});

watch(activeTab, async () => {
  await nextTick();
  if (activeTab.value === 'produtos') renderProductsChart();
  if (activeTab.value === 'categorias') renderCategoriesChart();
});

function queryParams(extra = {}) {
  const params = { ...extra };
  if (filters.date_from) params.date_from = filters.date_from;
  if (filters.date_to) params.date_to = filters.date_to;
  if (filters.store_id) params.store_id = filters.store_id;
  if (filters.user_id) params.user_id = filters.user_id;
  if (filters.payment_method) params.payment_method = filters.payment_method;
  return params;
}

function applyFilterOptions(payload) {
  if (!payload) return;
  filterOptions.stores = payload.stores || [];
  filterOptions.operators = payload.operators || [];
  filterOptions.payment_methods = payload.payment_methods || [];
}

async function loadAll() {
  loading.value = true;
  try {
    await Promise.all([
      loadSummary(),
      loadSales(),
      loadProducts(),
      loadCategories(),
      loadPayments(),
      loadCash(),
    ]);
  } finally {
    loading.value = false;
  }
}

async function onFilterApply() {
  salesPage.value = 1;
  cashPage.value = 1;
  await loadAll();
}

async function loadSummary() {
  const { data } = await axios.get('/api/v1/reports/summary', { params: queryParams() });
  summary.value = data.data || {};
  applyFilterOptions(data.data?.filters);
}

async function loadSales() {
  loadingSales.value = true;
  try {
    const { data } = await axios.get('/api/v1/reports/sales', {
      params: queryParams({
        page: salesPage.value,
        per_page: 15,
        search: salesSearch.value.trim() || undefined,
      }),
    });
    salesRows.value = data.data || [];
    Object.assign(salesMeta, data.meta || {});
    applyFilterOptions(data.filters);
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar vendas.');
  } finally {
    loadingSales.value = false;
  }
}

async function loadProducts() {
  loadingProducts.value = true;
  try {
    const { data } = await axios.get('/api/v1/reports/products', { params: queryParams() });
    productRows.value = data.data || [];
    applyFilterOptions(data.filters);
    await nextTick();
    renderProductsChart();
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar produtos.');
  } finally {
    loadingProducts.value = false;
  }
}

async function loadCategories() {
  loadingCategories.value = true;
  try {
    const { data } = await axios.get('/api/v1/reports/categories', { params: queryParams() });
    categoryRows.value = data.data || [];
    applyFilterOptions(data.filters);
    await nextTick();
    renderCategoriesChart();
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar categorias.');
  } finally {
    loadingCategories.value = false;
  }
}

async function loadPayments() {
  loadingPayments.value = true;
  try {
    const { data } = await axios.get('/api/v1/reports/payments', { params: queryParams() });
    paymentRows.value = data.data || [];
    applyFilterOptions(data.filters);
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar pagamentos.');
  } finally {
    loadingPayments.value = false;
  }
}

async function loadCash() {
  loadingCash.value = true;
  try {
    const { data } = await axios.get('/api/v1/reports/cash-registers', {
      params: queryParams({ page: cashPage.value, per_page: 15 }),
    });
    cashRows.value = data.data || [];
    Object.assign(cashMeta, data.meta || {});
    applyFilterOptions(data.filters);
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar caixas.');
  } finally {
    loadingCash.value = false;
  }
}

function onSalesSearch(value) {
  salesSearch.value = value;
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    salesPage.value = 1;
    loadSales();
  }, 350);
}

function onSalesPage(page) {
  salesPage.value = page;
  loadSales();
}

function onCashPage(page) {
  cashPage.value = page;
  loadCash();
}

function renderProductsChart() {
  if (!productsChartRef.value) return;
  productsChart?.destroy();
  const rows = productRows.value.slice(0, 10);
  productsChart = new Chart(productsChartRef.value, {
    type: 'bar',
    data: {
      labels: rows.map((row) => row.name),
      datasets: [
        {
          label: 'Qtd',
          data: rows.map((row) => row.quantity),
          backgroundColor: '#0369a1',
          borderRadius: 6,
        },
      ],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
        y: { grid: { display: false } },
      },
    },
  });
}

function renderCategoriesChart() {
  if (!categoriesChartRef.value) return;
  categoriesChart?.destroy();
  const rows = categoryRows.value.slice(0, 10);
  categoriesChart = new Chart(categoriesChartRef.value, {
    type: 'bar',
    data: {
      labels: rows.map((row) => row.name),
      datasets: [
        {
          label: 'Receita',
          data: rows.map((row) => row.revenue),
          backgroundColor: '#7c2d12',
          borderRadius: 6,
        },
      ],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
        y: { grid: { display: false } },
      },
    },
  });
}

function exportCsv() {
  const map = {
    resumo: {
      filename: 'relatorio-resumo.csv',
      headers: ['Indicador', 'Valor'],
      rows: [
        ['Total vendido', summary.value.sales_total ?? 0],
        ['Pedidos', summary.value.orders_count ?? 0],
        ['Ticket medio', summary.value.average_ticket ?? 0],
        ['Produto mais vendido', summary.value.top_product?.name || ''],
        ['Categoria mais vendida', summary.value.top_category?.name || ''],
        ['Pagamento mais utilizado', summary.value.top_payment?.label || ''],
      ],
    },
    vendas: {
      filename: 'relatorio-vendas.csv',
      headers: ['Numero', 'Data', 'Operador', 'Loja', 'Pagamento', 'Valor', 'Status'],
      rows: salesRows.value.map((row) => [
        row.reference || row.id,
        row.created_at || '',
        row.operator?.name || '',
        row.store?.name || '',
        row.payment_label || '',
        row.total ?? 0,
        row.status || '',
      ]),
    },
    produtos: {
      filename: 'relatorio-produtos.csv',
      headers: ['Produto', 'Quantidade', 'Receita', 'Preco medio'],
      rows: productRows.value.map((row) => [
        row.name,
        row.quantity,
        row.revenue,
        row.average_price,
      ]),
    },
    categorias: {
      filename: 'relatorio-categorias.csv',
      headers: ['Categoria', 'Quantidade', 'Receita'],
      rows: categoryRows.value.map((row) => [row.name, row.quantity, row.revenue]),
    },
    pagamentos: {
      filename: 'relatorio-pagamentos.csv',
      headers: ['Forma', 'Pedidos', 'Percentual', 'Valor'],
      rows: paymentRows.value.map((row) => [row.label, row.count, row.percent, row.total]),
    },
    caixas: {
      filename: 'relatorio-caixas.csv',
      headers: [
        'Numero',
        'Data',
        'Loja',
        'Operador',
        'Saldo inicial',
        'Total vendido',
        'Saldo esperado',
        'Status',
      ],
      rows: cashRows.value.map((row) => [
        row.id,
        row.opened_at || '',
        row.store?.name || '',
        row.operator?.name || '',
        row.opening_balance ?? 0,
        row.sales_total ?? 0,
        row.expected_balance ?? 0,
        row.status || '',
      ]),
    },
  };

  const config = map[activeTab.value];
  if (!config) return;

  const lines = [
    config.headers.join(';'),
    ...config.rows.map((row) =>
      row
        .map((cell) => `"${String(cell ?? '').replaceAll('"', '""')}"`)
        .join(';')
    ),
  ];

  const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = config.filename;
  link.click();
  URL.revokeObjectURL(url);
  toast.success('CSV exportado.');
}

function exportPdfPlaceholder() {
  toast.message('Exportar PDF (em breve)');
}

defineExpose({ reload: loadAll });
</script>
