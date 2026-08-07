<template>
  <div class="receipt-ticket font-mono text-xs text-black">
    <div class="mb-3 text-center">
      <img
        v-if="company?.logo_url"
        :src="company.logo_url"
        alt="Logo"
        class="mx-auto mb-2 h-14 w-auto object-contain"
      />
      <p class="text-sm font-bold uppercase">
        {{ company?.trade_name || company?.company_name || 'Cafeteria' }}
      </p>
      <p v-if="company?.address">{{ company.address }}</p>
      <p v-if="company?.city || company?.postal_code">
        {{ [company?.postal_code, company?.city].filter(Boolean).join(' ') }}
      </p>
      <p v-if="company?.phone">Tel: {{ company.phone }}</p>
      <p v-if="company?.tax_number">NIF: {{ company.tax_number }}</p>
    </div>

    <div class="mb-3 border-y border-dashed border-black py-2">
      <p>Pedido: {{ order?.reference || `#${order?.id}` }}</p>
      <p>Data: {{ formatDate(order?.created_at) }}</p>
      <p>Hora: {{ formatTime(order?.created_at) }}</p>
      <p>Operador: {{ order?.operator?.name || '—' }}</p>
      <p>Loja: {{ order?.store?.name || '—' }}</p>
    </div>

    <table class="mb-3 w-full">
      <thead>
        <tr class="border-b border-black text-left">
          <th class="py-1">Item</th>
          <th class="py-1 text-right">Qtd</th>
          <th class="py-1 text-right">Preco</th>
          <th class="py-1 text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in order?.items || []" :key="item.id">
          <td class="py-1 pr-2">{{ item.name }}</td>
          <td class="py-1 text-right">{{ item.quantity }}</td>
          <td class="py-1 text-right">{{ money(item.unit_price) }}</td>
          <td class="py-1 text-right">{{ money(item.line_total) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="mb-3 border-t border-dashed border-black pt-2">
      <div class="flex justify-between">
        <span>Subtotal</span>
        <span>{{ money(order?.total) }}</span>
      </div>
      <div class="flex justify-between font-bold">
        <span>TOTAL</span>
        <span>{{ money(order?.total) }}</span>
      </div>
      <div class="mt-2 flex justify-between">
        <span>Pagamento</span>
        <span>{{ order?.payment_label || '—' }}</span>
      </div>
      <div v-if="isCash" class="flex justify-between">
        <span>Recebido</span>
        <span>{{ money(amountReceived) }}</span>
      </div>
      <div v-if="isCash" class="flex justify-between">
        <span>Troco</span>
        <span>{{ money(changeAmount) }}</span>
      </div>
    </div>

    <p class="text-center">
      {{ company?.receipt_footer || 'Obrigado pela preferencia. Volte sempre!' }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  order: { type: Object, default: null },
  company: { type: Object, default: null },
  amountReceived: { type: Number, default: null },
  changeAmount: { type: Number, default: null },
});

const isCash = computed(() => props.order?.payment_method === 'cash');

function money(value) {
  return `€${Number(value || 0).toFixed(2)}`;
}

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleDateString('pt-PT');
}

function formatTime(value) {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
}
</script>
