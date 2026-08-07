<template>
  <section class="h-full overflow-y-auto">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 md:text-3xl">Dashboard</h1>
        <p class="text-sm text-slate-500">Visao geral da cafeteria Dona Joana.</p>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
        :disabled="loading"
        @click="load"
      >
        {{ loading ? 'A atualizar...' : 'Atualizar' }}
      </button>
    </div>

    <div v-if="loading && !dashboard" class="rounded-2xl bg-white p-10 text-center shadow-md">
      <p class="font-semibold text-slate-900">A carregar dashboard...</p>
    </div>

    <div v-else-if="error && !dashboard" class="rounded-2xl bg-white p-10 text-center shadow-md">
      <p class="font-semibold text-red-600">{{ error }}</p>
      <button
        type="button"
        class="mt-4 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
        @click="load"
      >
        Tentar novamente
      </button>
    </div>

    <div v-else class="space-y-4 pb-4">
      <DashboardCards :data="dashboard?.cards || {}" />
      <DashboardCharts :charts="dashboard?.charts || {}" />
      <DashboardTopProducts
        :products="dashboard?.charts?.top_products || []"
        :indicators="dashboard?.indicators || {}"
      />
      <DashboardRecentOrders
        :orders="dashboard?.lists?.recent_orders || []"
        :closed-cash="dashboard?.lists?.recent_closed_cash || []"
      />
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import DashboardCards from '../components/DashboardCards.vue';
import DashboardCharts from '../components/DashboardCharts.vue';
import DashboardRecentOrders from '../components/DashboardRecentOrders.vue';
import DashboardTopProducts from '../components/DashboardTopProducts.vue';

const dashboard = ref(null);
const loading = ref(false);
const error = ref('');

onMounted(load);

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const { data } = await axios.get('/api/v1/dashboard');
    dashboard.value = data.data ?? null;
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.message || 'Erro ao carregar o dashboard.';
    toast.error(error.value);
  } finally {
    loading.value = false;
  }
}

defineExpose({ reload: load });
</script>
