<template>
  <aside class="flex h-full flex-col rounded-2xl border-0 bg-white p-4 shadow-2xl md:p-6">
    <div class="mb-3 flex items-center justify-between gap-2">
      <h2 class="text-xl font-bold text-slate-900 md:text-2xl">Carrinho</h2>
      <button
        type="button"
        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
        :disabled="cart.length === 0 || processing"
        @click="$emit('cancel-sale')"
      >
        Cancelar venda
      </button>
    </div>

    <CartSummary
      :items-count="itemsCount"
      :units-count="unitsCount"
      :total="total"
    />

    <div class="mt-4 min-h-0 flex-1 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
      <div v-if="cart.length === 0" class="py-2">
        <EmptyState
          title="Carrinho vazio"
          description="Toque num produto para adicionar à venda."
          icon="🛒"
        />
      </div>

      <CartItem
        v-for="item in cart"
        :key="item.id"
        :item="item"
        :selected="selectedItemId === item.id"
        @select="$emit('select-item', $event)"
        @increase="$emit('increase-item', $event)"
        @decrease="$emit('decrease-item', $event)"
        @remove="$emit('remove-item', $event)"
        @set-quantity="(id, qty) => $emit('set-quantity', id, qty)"
      />
    </div>

    <div class="mt-4 rounded-xl bg-slate-900 p-4 text-white">
      <p class="text-sm uppercase text-slate-300">Total</p>
      <p class="text-4xl font-black tracking-tight">€{{ total.toFixed(2) }}</p>
    </div>

    <div class="mt-4">
      <PaymentPanel
        :payment-method="paymentMethod"
        :payment-methods="paymentMethods"
        :received-amount="receivedAmount"
        :change="change"
        :keypad-keys="keypadKeys"
        @update:payment-method="$emit('update:payment-method', $event)"
        @keypad-press="$emit('keypad-press', $event)"
      />
    </div>

    <button
      type="button"
      :disabled="!canPay"
      class="mt-4 w-full scale-[1.02] rounded-xl bg-green-600 py-4 text-xl font-black text-white shadow-xl transition hover:bg-green-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-300 disabled:cursor-not-allowed disabled:bg-slate-400"
      @click="$emit('pay')"
    >
      {{ processing ? 'Processando...' : 'Finalizar venda' }}
    </button>
  </aside>
</template>

<script setup>
import CartItem from './CartItem.vue';
import CartSummary from './CartSummary.vue';
import PaymentPanel from './PaymentPanel.vue';
import EmptyState from './ui/EmptyState.vue';

defineProps({
  cart: { type: Array, required: true },
  receivedAmount: { type: Number, required: true },
  total: { type: Number, required: true },
  change: { type: Number, required: true },
  keypadKeys: { type: Array, required: true },
  canPay: { type: Boolean, required: true },
  processing: { type: Boolean, required: true },
  paymentMethod: { type: String, default: 'cash' },
  paymentMethods: { type: Array, required: true },
  itemsCount: { type: Number, required: true },
  unitsCount: { type: Number, required: true },
  selectedItemId: { type: [Number, String], default: null },
});

defineEmits([
  'decrease-item',
  'increase-item',
  'remove-item',
  'set-quantity',
  'select-item',
  'keypad-press',
  'pay',
  'cancel-sale',
  'update:payment-method',
]);
</script>
