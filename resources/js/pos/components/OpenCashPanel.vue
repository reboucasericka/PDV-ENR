<template>
  <div class="flex h-full items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">
      <h2 class="mb-2 text-xl font-bold text-slate-900">Caixa fechado</h2>
      <p class="mb-6 text-sm text-slate-500">Abra o caixa para iniciar as vendas.</p>
      <button
        type="button"
        :disabled="opening"
        class="rounded-xl bg-blue-600 px-6 py-3 font-bold text-white transition hover:bg-blue-700"
        @click="$emit('open-cash')"
      >
        {{ opening ? 'Abrindo...' : 'Abrir caixa' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  opening: { type: Boolean, default: false },
});

defineEmits(['open-cash']);
</script>
