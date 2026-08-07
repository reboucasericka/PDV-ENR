<template>
  <div class="relative">
    <input
      :id="inputId"
      ref="inputRef"
      :value="modelValue"
      type="search"
      :placeholder="placeholder"
      :aria-label="placeholder"
      class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm outline-none focus:border-slate-500 focus-visible:ring-2 focus-visible:ring-slate-400"
      autocomplete="off"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span
      class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
      aria-hidden="true"
    >🔍</span>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Pesquisar produto...' },
  inputId: { type: String, default: 'product-search' },
});

defineEmits(['update:modelValue']);

const inputRef = ref(null);

defineExpose({
  focus: () => inputRef.value?.focus(),
});
</script>
