<template>
  <div class="flex h-full items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">
      <h2 class="mb-2 text-xl font-bold text-slate-900">Abrir caixa</h2>
      <p class="mb-2 text-sm text-slate-500">
        Loja: <strong class="text-slate-800">{{ storeName || '—' }}</strong>
      </p>
      <p class="mb-6 text-sm text-slate-500">
        Informe o fundo de caixa (saldo inicial) para comecar as vendas.
      </p>

      <label class="mb-2 block text-left text-xs font-semibold uppercase text-slate-500" for="opening-balance">
        Saldo inicial (€)
      </label>
      <input
        id="opening-balance"
        v-model="balanceInput"
        type="number"
        min="0"
        step="0.01"
        inputmode="decimal"
        class="mb-5 w-full rounded-xl border border-slate-300 px-4 py-3 text-center text-2xl font-bold text-slate-900 outline-none focus:border-slate-500"
        placeholder="50.00"
        :disabled="opening"
      />

      <div class="mb-5 flex justify-center gap-2">
        <button
          v-for="preset in presets"
          :key="preset"
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          :disabled="opening"
          @click="balanceInput = String(preset)"
        >
          €{{ preset }}
        </button>
      </div>

      <button
        type="button"
        :disabled="opening || !isValidBalance"
        class="w-full rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
        @click="submit"
      >
        {{ opening ? 'Abrindo...' : 'Abrir caixa' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

defineProps({
  opening: { type: Boolean, default: false },
  storeName: { type: String, default: '' },
});

const emit = defineEmits(['open-cash']);

const presets = [50, 100, 200];
const balanceInput = ref('50');

const isValidBalance = computed(() => {
  const value = Number(balanceInput.value);
  return Number.isFinite(value) && value >= 0;
});

function submit() {
  if (!isValidBalance.value) {
    return;
  }

  emit('open-cash', Number(balanceInput.value));
}
</script>
