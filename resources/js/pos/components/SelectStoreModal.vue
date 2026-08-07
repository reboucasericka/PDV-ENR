<template>
  <div
    class="flex h-full items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="select-store-title"
  >
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-lg sm:p-8">
      <div class="text-center">
        <h2 id="select-store-title" class="mb-2 text-xl font-bold text-slate-900">
          Selecionar Loja
        </h2>
        <p class="mb-6 text-sm text-slate-500">
          Escolha a loja para iniciar a operação do caixa.
        </p>
      </div>

      <div
        v-if="loading"
        class="rounded-xl bg-slate-50 py-8 text-center text-sm text-slate-500"
        role="status"
        aria-live="polite"
      >
        A carregar lojas...
      </div>

      <EmptyState
        v-else-if="stores.length === 0"
        title="Nenhuma loja disponível"
        description="Peça a um administrador para ativar uma loja."
        icon="🏪"
      />

      <template v-else>
        <div
          v-if="previewStore"
          class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center"
          role="status"
          aria-live="polite"
        >
          <p class="text-sm font-semibold text-emerald-800">✓ Loja selecionada</p>
          <p class="mt-1 text-base font-bold text-slate-900">{{ previewStore.name }}</p>
          <p class="text-xs text-slate-500">
            {{ previewStore.city }}{{ previewStore.state ? `, ${previewStore.state}` : '' }}
          </p>
        </div>

        <ul class="max-h-[18rem] space-y-2 overflow-y-auto" aria-label="Lista de lojas">
          <li v-for="store in stores" :key="store.id">
            <button
              type="button"
              :disabled="selecting || store.is_active === false"
              class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 disabled:cursor-not-allowed disabled:opacity-50"
              :class="
                previewStore?.id === store.id
                  ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-200'
                  : 'border-slate-200 bg-slate-50 hover:border-slate-400 hover:bg-white'
              "
              :aria-pressed="previewStore?.id === store.id ? 'true' : 'false'"
              @click="previewStore = store"
            >
              <span>
                <span class="block text-sm font-bold text-slate-900">{{ store.name }}</span>
                <span class="mt-0.5 block text-xs text-slate-500">
                  {{ store.city }}{{ store.state ? `, ${store.state}` : '' }}
                </span>
              </span>
              <span
                class="text-xs font-semibold uppercase"
                :class="previewStore?.id === store.id ? 'text-emerald-700' : 'text-slate-400'"
              >
                {{ previewStore?.id === store.id ? 'Escolhida' : 'Selecionar' }}
              </span>
            </button>
          </li>
        </ul>

        <button
          type="button"
          :disabled="!previewStore || selecting"
          class="mt-6 w-full rounded-xl bg-slate-900 py-3 text-sm font-bold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:cursor-not-allowed disabled:bg-slate-400"
          @click="confirmSelection"
        >
          {{ selecting ? 'A confirmar...' : previewStore ? 'Continuar' : 'Selecione uma loja' }}
        </button>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import EmptyState from './ui/EmptyState.vue';

const props = defineProps({
  stores: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  selecting: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['select-store']);

const previewStore = ref(null);

watch(
  () => props.stores,
  () => {
    previewStore.value = null;
  }
);

function confirmSelection() {
  if (!previewStore.value || props.selecting) {
    return;
  }

  emit('select-store', previewStore.value);
}
</script>
