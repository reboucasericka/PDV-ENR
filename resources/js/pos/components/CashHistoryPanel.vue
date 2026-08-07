<template>
  <section class="flex h-full flex-col rounded-2xl bg-white p-4 shadow-md md:p-6">
    <div class="mb-4">
      <h2 class="text-xl font-bold text-slate-900">Historico de Caixas</h2>
      <p class="text-sm text-slate-500">Consulte caixas abertos e fechados com filtros e detalhe.</p>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Data inicial</span>
        <input v-model="filters.date_from" type="date" class="input-field w-full" @change="reloadFirstPage" />
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Data final</span>
        <input v-model="filters.date_to" type="date" class="input-field w-full" @change="reloadFirstPage" />
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Loja</span>
        <select v-model="filters.store_id" class="input-field w-full" @change="reloadFirstPage">
          <option value="">Todas</option>
          <option v-for="store in filterOptions.stores" :key="store.id" :value="String(store.id)">
            {{ store.name }}
          </option>
        </select>
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Operador</span>
        <select v-model="filters.user_id" class="input-field w-full" @change="reloadFirstPage">
          <option value="">Todos</option>
          <option v-for="operator in filterOptions.operators" :key="operator.id" :value="String(operator.id)">
            {{ operator.name }}
          </option>
        </select>
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Status</span>
        <select v-model="filters.status" class="input-field w-full" @change="reloadFirstPage">
          <option value="">Todos</option>
          <option value="OPEN">OPEN</option>
          <option value="CLOSED">CLOSED</option>
        </select>
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Pesquisa</span>
        <input
          v-model="filters.search"
          type="search"
          placeholder="Nº, loja, operador..."
          class="input-field w-full"
          @input="onSearchInput"
        />
      </label>
    </div>

    <div class="min-h-0 flex-1 overflow-auto rounded-xl border border-slate-200">
      <table class="min-w-full text-left text-sm">
        <thead class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-3 py-3">Nº Caixa</th>
            <th class="px-3 py-3">Loja</th>
            <th class="px-3 py-3">Operador</th>
            <th class="px-3 py-3">Data</th>
            <th class="px-3 py-3">Abertura</th>
            <th class="px-3 py-3">Fechamento</th>
            <th class="px-3 py-3">Pedidos</th>
            <th class="px-3 py-3">Total Vendido</th>
            <th class="px-3 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-10 text-center text-slate-500">A carregar historico...</td>
          </tr>
          <tr v-else-if="rows.length === 0">
            <td colspan="9" class="px-3 py-10 text-center text-slate-500">
              Nenhum caixa encontrado com estes filtros.
            </td>
          </tr>
          <tr
            v-for="row in rows"
            :key="row.id"
            class="cursor-pointer border-t border-slate-100 transition hover:bg-slate-50"
            @click="openDetail(row.id)"
          >
            <td class="px-3 py-3 font-semibold text-slate-900">#{{ row.id }}</td>
            <td class="px-3 py-3 text-slate-700">{{ row.store?.name || '—' }}</td>
            <td class="px-3 py-3 text-slate-700">{{ row.operator?.name || '—' }}</td>
            <td class="px-3 py-3 text-slate-700">{{ formatDate(row.opened_at) }}</td>
            <td class="px-3 py-3 text-slate-700">{{ formatTime(row.opened_at) }}</td>
            <td class="px-3 py-3 text-slate-700">{{ formatTime(row.closed_at) }}</td>
            <td class="px-3 py-3 text-slate-700">{{ row.orders_count ?? 0 }}</td>
            <td class="px-3 py-3 font-semibold text-slate-900">€{{ formatMoney(row.sales_total) }}</td>
            <td class="px-3 py-3">
              <span
                class="rounded-full px-2.5 py-1 text-xs font-bold"
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
        {{ meta.total }} registo(s) · pagina {{ meta.current_page }} de {{ meta.last_page || 1 }}
      </p>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 disabled:opacity-40"
          :disabled="loading || meta.current_page <= 1"
          @click="goToPage(meta.current_page - 1)"
        >
          Anterior
        </button>
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 disabled:opacity-40"
          :disabled="loading || meta.current_page >= meta.last_page"
          @click="goToPage(meta.current_page + 1)"
        >
          Seguinte
        </button>
      </div>
    </div>

    <CashHistoryDetailDrawer
      :open="drawerOpen"
      :loading="detailLoading"
      :detail="detail"
      @close="closeDetail"
      @reprint="onReprint"
      @export-pdf="onExportPdf"
    />
  </section>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import CashHistoryDetailDrawer from './CashHistoryDetailDrawer.vue';

const rows = ref([]);
const loading = ref(false);
const detailLoading = ref(false);
const drawerOpen = ref(false);
const detail = ref(null);
const searchTimer = ref(null);

const filters = reactive({
  date_from: '',
  date_to: '',
  store_id: '',
  user_id: '',
  status: '',
  search: '',
  page: 1,
  per_page: 15,
});

const filterOptions = reactive({
  stores: [],
  operators: [],
});

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

onMounted(loadHistory);
onUnmounted(() => {
  if (searchTimer.value) {
    clearTimeout(searchTimer.value);
  }
});

async function loadHistory() {
  loading.value = true;
  try {
    const params = {
      page: filters.page,
      per_page: filters.per_page,
    };

    if (filters.date_from) params.date_from = filters.date_from;
    if (filters.date_to) params.date_to = filters.date_to;
    if (filters.store_id) params.store_id = filters.store_id;
    if (filters.user_id) params.user_id = filters.user_id;
    if (filters.status) params.status = filters.status;
    if (filters.search.trim()) params.search = filters.search.trim();

    const { data } = await axios.get('/api/v1/cash/history', { params });
    rows.value = data.data ?? [];

    Object.assign(meta, {
      current_page: data.meta?.current_page ?? 1,
      last_page: data.meta?.last_page ?? 1,
      per_page: data.meta?.per_page ?? 15,
      total: data.meta?.total ?? 0,
    });

    filterOptions.stores = data.filters?.stores ?? [];
    filterOptions.operators = data.filters?.operators ?? [];
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar historico de caixas.');
  } finally {
    loading.value = false;
  }
}

function reloadFirstPage() {
  filters.page = 1;
  loadHistory();
}

function onSearchInput() {
  if (searchTimer.value) {
    clearTimeout(searchTimer.value);
  }
  searchTimer.value = setTimeout(() => {
    reloadFirstPage();
  }, 350);
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) {
    return;
  }
  filters.page = page;
  loadHistory();
}

async function openDetail(id) {
  drawerOpen.value = true;
  detailLoading.value = true;
  detail.value = null;

  try {
    const { data } = await axios.get(`/api/v1/cash/${id}`);
    detail.value = data.data ?? null;
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Erro ao carregar detalhe do caixa.');
    drawerOpen.value = false;
  } finally {
    detailLoading.value = false;
  }
}

function closeDetail() {
  drawerOpen.value = false;
  detail.value = null;
}

function onReprint() {
  toast.message('Reimprimir Fechamento (em breve)');
}

function onExportPdf() {
  toast.message('Exportar PDF (em breve)');
}

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

function formatDate(value) {
  if (!value) {
    return '—';
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return '—';
  }
  return date.toLocaleDateString('pt-PT');
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

defineExpose({ reload: loadHistory });
</script>

<style scoped>
.input-field {
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  background: #fff;
  padding: 0.55rem 0.75rem;
  font-size: 0.875rem;
  color: #0f172a;
}
.input-field:focus {
  outline: 2px solid #94a3b8;
  outline-offset: 1px;
}
</style>
