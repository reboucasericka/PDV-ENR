<template>
  <aside class="flex h-full flex-col rounded-2xl border-0 bg-white p-4 shadow-2xl md:p-6">
    <div class="flex items-end justify-between">
      <h2 class="text-xl font-bold text-slate-900 md:text-2xl">Carrinho</h2>
      <p class="text-xs text-gray-400">Itens: {{ cart.length }}</p>
    </div>

    <div class="mt-4 flex-1 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
      <div v-if="cart.length === 0" class="py-6 text-center text-slate-500">
        Sem itens no carrinho.
      </div>

      <div
        v-for="item in cart"
        :key="item.id"
        class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm shadow-sm"
      >
        <div>
          <p class="font-semibold text-slate-800">{{ item.name }}</p>
          <p class="text-slate-500">€{{ item.price.toFixed(2) }} x {{ item.quantity }}</p>
        </div>

        <div class="flex items-center gap-2">
          <span class="text-base font-bold text-slate-900">€{{ (item.price * item.quantity).toFixed(2) }}</span>
          <button
            type="button"
            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100"
            @click="$emit('decrease-item', item.id)"
          >
            -
          </button>
        </div>
      </div>
    </div>

    <div class="mt-4 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
      <p class="text-xs font-semibold uppercase text-slate-500">Teclado numerico (valor recebido)</p>
      <div class="rounded-lg bg-white p-3 text-right text-2xl font-extrabold text-slate-900 shadow-sm">
        €{{ receivedAmount.toFixed(2) }}
      </div>
      <div class="grid grid-cols-3 gap-2">
        <button
          v-for="key in keypadKeys"
          :key="key.label"
          type="button"
          class="rounded-lg border border-slate-300 bg-white py-3 text-lg font-bold text-slate-800 hover:bg-slate-100"
          @click="$emit('keypad-press', key)"
        >
          {{ key.label }}
        </button>
      </div>
    </div>

    <div class="mt-4 rounded-xl bg-slate-900 p-4 text-white">
      <p class="text-sm uppercase text-slate-300">Total</p>
      <p class="text-4xl font-black tracking-tight">€{{ total.toFixed(2) }}</p>
      <p class="mt-1 text-sm text-slate-300">Troco: €{{ change.toFixed(2) }}</p>
    </div>

    <button
      type="button"
      :disabled="!canPay"
      class="mt-4 w-full scale-[1.02] rounded-xl bg-green-600 py-4 text-xl font-black text-white shadow-xl transition hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-slate-400"
      @click="$emit('pay')"
    >
      {{ processing ? 'Processando...' : 'Pagar (Enter)' }}
    </button>
  </aside>
</template>

<script setup>
defineProps({
  cart: { type: Array, required: true },
  receivedAmount: { type: Number, required: true },
  total: { type: Number, required: true },
  change: { type: Number, required: true },
  keypadKeys: { type: Array, required: true },
  canPay: { type: Boolean, required: true },
  processing: { type: Boolean, required: true },
});

defineEmits(['decrease-item', 'keypad-press', 'pay']);
</script>
