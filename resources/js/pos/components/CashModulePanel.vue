<template>
  <section class="flex h-full flex-col gap-4">
    <div class="flex flex-wrap items-center gap-2 rounded-2xl bg-white p-2 shadow-md">
      <button
        type="button"
        class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
        :class="
          activeTab === 'atual'
            ? 'bg-slate-900 text-white shadow-sm'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
        "
        @click="activeTab = 'atual'"
      >
        Caixa Atual
      </button>
      <button
        type="button"
        class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
        :class="
          activeTab === 'historico'
            ? 'bg-slate-900 text-white shadow-sm'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
        "
        @click="switchToHistory"
      >
        Historico
      </button>
    </div>

    <div class="min-h-0 flex-1">
      <template v-if="activeTab === 'atual'">
        <CloseCashPanel
          v-if="cashOpen"
          :summary="summary"
          :store-name="storeName"
          :operator-name="operatorName"
          :closing="closing"
          @confirm-close="$emit('confirm-close')"
          @keep-open="$emit('keep-open')"
        />

        <OpenCashPanel
          v-else-if="canOpenCash"
          :opening="opening"
          :store-name="storeName"
          @open-cash="$emit('open-cash', $event)"
        />

        <div
          v-else
          class="flex h-full items-center justify-center rounded-2xl bg-white p-8 text-center shadow-md"
        >
          <div>
            <h2 class="text-xl font-bold text-slate-900">Caixa fechado</h2>
            <p class="mt-2 text-sm text-slate-500">
              Selecione uma loja para abrir o caixa, ou consulte o Historico.
            </p>
          </div>
        </div>
      </template>

      <CashHistoryPanel v-else ref="historyPanelRef" />
    </div>
  </section>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import CashHistoryPanel from './CashHistoryPanel.vue';
import CloseCashPanel from './CloseCashPanel.vue';
import OpenCashPanel from './OpenCashPanel.vue';

defineProps({
  cashOpen: { type: Boolean, required: true },
  canOpenCash: { type: Boolean, default: false },
  summary: { type: Object, default: () => ({}) },
  storeName: { type: String, default: '' },
  operatorName: { type: String, default: '' },
  opening: { type: Boolean, default: false },
  closing: { type: Boolean, default: false },
});

defineEmits(['confirm-close', 'keep-open', 'open-cash']);

const activeTab = ref('atual');
const historyPanelRef = ref(null);

async function switchToHistory() {
  activeTab.value = 'historico';
  await nextTick();
  historyPanelRef.value?.reload?.();
}

defineExpose({
  showHistory() {
    switchToHistory();
  },
  showCurrent() {
    activeTab.value = 'atual';
  },
});
</script>
