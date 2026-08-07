<template>
  <div class="flex h-full items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-lg">
      <div class="text-center">
        <h2 class="mb-2 text-xl font-bold text-slate-900">Selecionar Loja</h2>
        <p class="mb-6 text-sm text-slate-500">
          Escolha a loja para iniciar a operacao do caixa.
        </p>
      </div>

      <div v-if="stores.length === 0" class="rounded-xl bg-slate-50 py-8 text-center text-sm text-slate-500">
        Nenhuma loja disponivel.
      </div>

      <ul v-else class="max-h-[22rem] space-y-2 overflow-y-auto">
        <li v-for="store in stores" :key="store.id">
          <button
            type="button"
            :disabled="selecting || store.is_active === false"
            class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition hover:border-slate-400 hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
            @click="$emit('select-store', store)"
          >
            <span>
              <span class="block text-sm font-bold text-slate-900">{{ store.name }}</span>
              <span class="mt-0.5 block text-xs text-slate-500">
                {{ store.city }}{{ store.state ? `, ${store.state}` : '' }}
              </span>
            </span>
            <span class="text-xs font-semibold uppercase text-slate-400">
              {{ selecting ? '...' : 'Selecionar' }}
            </span>
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stores: {
    type: Array,
    default: () => [],
  },
  selecting: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['select-store']);
</script>
