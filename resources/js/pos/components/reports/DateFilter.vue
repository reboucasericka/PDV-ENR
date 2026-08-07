<template>
  <div class="rounded-2xl bg-white p-4 shadow-md">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Data inicial</span>
        <input v-model="local.date_from" type="date" class="input-field w-full" />
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Data final</span>
        <input v-model="local.date_to" type="date" class="input-field w-full" />
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Loja</span>
        <select v-model="local.store_id" class="input-field w-full">
          <option value="">Todas</option>
          <option v-for="store in options.stores || []" :key="store.id" :value="String(store.id)">
            {{ store.name }}
          </option>
        </select>
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Operador</span>
        <select v-model="local.user_id" class="input-field w-full">
          <option value="">Todos</option>
          <option
            v-for="operator in options.operators || []"
            :key="operator.id"
            :value="String(operator.id)"
          >
            {{ operator.name }}
          </option>
        </select>
      </label>
      <label class="text-sm text-slate-600">
        <span class="mb-1 block text-xs font-semibold uppercase text-slate-500">Pagamento</span>
        <select v-model="local.payment_method" class="input-field w-full">
          <option value="">Todas</option>
          <option
            v-for="method in options.payment_methods || []"
            :key="method.value"
            :value="method.value"
          >
            {{ method.label }}
          </option>
        </select>
      </label>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
        @click="emitApply"
      >
        Aplicar filtros
      </button>
      <button
        type="button"
        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
        @click="emitReset"
      >
        Limpar
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue', 'apply', 'reset']);

const local = reactive({
  date_from: '',
  date_to: '',
  store_id: '',
  user_id: '',
  payment_method: '',
});

watch(
  () => props.modelValue,
  (value) => {
    Object.assign(local, {
      date_from: value?.date_from || '',
      date_to: value?.date_to || '',
      store_id: value?.store_id ? String(value.store_id) : '',
      user_id: value?.user_id ? String(value.user_id) : '',
      payment_method: value?.payment_method || '',
    });
  },
  { immediate: true, deep: true }
);

function emitApply() {
  const payload = { ...local };
  emit('update:modelValue', payload);
  emit('apply', payload);
}

function emitReset() {
  Object.assign(local, {
    date_from: '',
    date_to: '',
    store_id: '',
    user_id: '',
    payment_method: '',
  });
  const payload = { ...local };
  emit('update:modelValue', payload);
  emit('reset', payload);
}
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
