<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <button
        type="button"
        class="absolute inset-0 bg-slate-900/40 no-print"
        aria-label="Fechar"
        @click="$emit('close')"
      />

      <div
        class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="order-detail-title"
      >
        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 no-print">
          <div>
            <h2 id="order-detail-title" class="text-xl font-bold text-slate-900">
              Pedido {{ order?.reference || (order?.id ? `#${order.id}` : '') }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">Detalhe da venda e impressao.</p>
          </div>
          <button
            type="button"
            class="rounded-lg px-2 py-1 text-sm font-semibold text-slate-500 hover:bg-slate-100"
            @click="$emit('close')"
          >
            Fechar
          </button>
        </div>

        <div v-if="loading" class="p-8 text-center text-slate-500 no-print">
          A carregar pedido...
        </div>

        <div v-else-if="!order" class="p-8 text-center text-slate-500 no-print">
          Pedido nao encontrado.
        </div>

        <div v-else class="min-h-0 flex-1 overflow-y-auto p-5">
          <div class="mb-5 grid gap-3 text-sm md:grid-cols-2 no-print">
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Loja</p>
              <p class="font-semibold text-slate-900">{{ order.store?.name || '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Operador</p>
              <p class="font-semibold text-slate-900">{{ order.operator?.name || '—' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Caixa</p>
              <p class="font-semibold text-slate-900">
                {{ order.cash_register?.id ? `#${order.cash_register.id}` : '—' }}
              </p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs uppercase text-slate-500">Pagamento</p>
              <p class="font-semibold text-slate-900">{{ order.payment_label || '—' }}</p>
            </div>
          </div>

          <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 no-print">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                  <th class="px-3 py-2">Produto</th>
                  <th class="px-3 py-2 text-right">Qtd</th>
                  <th class="px-3 py-2 text-right">Preco</th>
                  <th class="px-3 py-2 text-right">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in order.items || []"
                  :key="item.id"
                  class="border-t border-slate-100"
                >
                  <td class="px-3 py-2 font-semibold text-slate-900">{{ item.name }}</td>
                  <td class="px-3 py-2 text-right">{{ item.quantity }}</td>
                  <td class="px-3 py-2 text-right">€{{ money(item.unit_price) }}</td>
                  <td class="px-3 py-2 text-right font-semibold">€{{ money(item.line_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="mb-4 text-right text-lg font-black text-slate-900 no-print">
            Total: €{{ money(order.total) }}
          </p>

          <div class="grid gap-4 md:grid-cols-2">
            <div id="print-receipt" class="rounded-xl border border-slate-200 p-4">
              <Receipt
                :order="order"
                :company="company"
                :amount-received="amountReceived"
                :change-amount="changeAmount"
              />
            </div>
            <div id="print-kitchen" class="rounded-xl border border-slate-200 p-4">
              <KitchenTicket :order="order" />
            </div>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-slate-200 p-4 no-print">
          <PrintButton
            target-id="print-receipt"
            label="Imprimir Recibo"
            :disabled="!order"
          />
          <PrintButton
            target-id="print-kitchen"
            label="Imprimir Comanda"
            variant="secondary"
            :disabled="!order"
          />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import KitchenTicket from './KitchenTicket.vue';
import PrintButton from './PrintButton.vue';
import Receipt from './Receipt.vue';

defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  order: { type: Object, default: null },
  company: { type: Object, default: null },
  amountReceived: { type: Number, default: null },
  changeAmount: { type: Number, default: null },
});

defineEmits(['close']);

function money(value) {
  return Number(value || 0).toFixed(2);
}
</script>
