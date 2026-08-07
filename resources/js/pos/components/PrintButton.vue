<template>
  <button
    type="button"
    class="rounded-xl px-4 py-2.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50"
    :class="variantClass"
    :disabled="disabled || !canPrint"
    @click="print"
  >
    {{ label }}
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, default: 'Imprimir Recibo' },
  targetId: { type: String, required: true },
  disabled: { type: Boolean, default: false },
  variant: { type: String, default: 'primary' },
});

const emit = defineEmits(['printed']);

const canPrint = computed(() => typeof window !== 'undefined' && !!props.targetId);

const variantClass = computed(() => {
  if (props.variant === 'secondary') {
    return 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50';
  }
  return 'bg-slate-900 text-white hover:bg-slate-800';
});

function print() {
  const target = document.getElementById(props.targetId);
  if (!target) {
    return;
  }

  document.body.setAttribute('data-print-target', props.targetId);
  window.print();
  document.body.removeAttribute('data-print-target');
  emit('printed', props.targetId);
}
</script>
