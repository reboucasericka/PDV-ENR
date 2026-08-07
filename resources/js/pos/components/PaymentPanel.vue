<template>
  <div class="space-y-3">
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
      <p id="payment-method-label" class="mb-2 text-xs font-semibold uppercase text-slate-500">
        Forma de pagamento
      </p>
      <div
        class="grid grid-cols-2 gap-2"
        role="group"
        aria-labelledby="payment-method-label"
      >
        <button
          v-for="method in paymentMethods"
          :key="method.value"
          type="button"
          class="rounded-lg border px-3 py-2 text-left text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
          :class="
            paymentMethod === method.value
              ? 'border-slate-900 bg-slate-900 text-white'
              : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
          "
          :aria-pressed="paymentMethod === method.value ? 'true' : 'false'"
          @click="$emit('update:paymentMethod', method.value)"
        >
          {{ method.label }}
        </button>
      </div>
    </div>

    <div v-if="isCash" class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
      <p class="text-xs font-semibold uppercase text-slate-500">Valor recebido</p>
      <div
        class="rounded-lg bg-white p-3 text-right text-2xl font-extrabold text-slate-900 shadow-sm"
        aria-live="polite"
      >
        €{{ receivedAmount.toFixed(2) }}
      </div>
      <div class="grid grid-cols-3 gap-2" role="group" aria-label="Teclado numérico">
        <button
          v-for="key in keypadKeys"
          :key="key.label"
          type="button"
          class="rounded-lg border border-slate-300 bg-white py-3 text-lg font-bold text-slate-800 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
          :aria-label="keypadAriaLabel(key)"
          @click="$emit('keypad-press', key)"
        >
          {{ key.label }}
        </button>
      </div>
      <div class="rounded-lg bg-white px-3 py-2 text-sm text-slate-600" aria-live="polite">
        Troco:
        <span class="font-bold text-slate-900">€{{ change.toFixed(2) }}</span>
      </div>
    </div>

    <div
      v-else
      class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600"
    >
      Pagamento por <strong class="text-slate-900">{{ selectedPaymentLabel }}</strong>.
      Confirme para finalizar a venda sem cálculo de troco.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  paymentMethod: { type: String, default: 'cash' },
  paymentMethods: { type: Array, required: true },
  receivedAmount: { type: Number, required: true },
  change: { type: Number, required: true },
  keypadKeys: { type: Array, required: true },
});

defineEmits(['update:paymentMethod', 'keypad-press']);

const isCash = computed(() => props.paymentMethod === 'cash');

const selectedPaymentLabel = computed(() => {
  const found = props.paymentMethods.find((method) => method.value === props.paymentMethod);
  return found?.label || props.paymentMethod;
});

function keypadAriaLabel(key) {
  if (key.label === '⌫' || key.action === 'backspace') {
    return 'Apagar último dígito';
  }
  if (key.label === 'C' || key.action === 'clear') {
    return 'Limpar valor recebido';
  }
  if (key.label === ',' || key.label === '.') {
    return 'Vírgula decimal';
  }
  return `Tecla ${key.label}`;
}
</script>
