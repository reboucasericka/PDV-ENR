<template>
  <div class="kitchen-ticket font-mono text-sm text-black">
    <div class="mb-3 border-b-2 border-black pb-2 text-center">
      <p class="text-base font-black uppercase">COMANDA COZINHA</p>
      <p class="mt-1 font-bold">Pedido: {{ order?.reference || `#${order?.id}` }}</p>
      <p>Horario: {{ formatDateTime(order?.created_at) }}</p>
      <p>Loja: {{ order?.store?.name || '—' }}</p>
    </div>

    <table class="w-full">
      <thead>
        <tr class="border-b border-black text-left">
          <th class="py-1">Qtd</th>
          <th class="py-1">Item</th>
          <th class="py-1">Obs.</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in order?.items || []" :key="item.id" class="align-top">
          <td class="py-2 pr-3 text-lg font-black">{{ item.quantity }}x</td>
          <td class="py-2 pr-3 text-base font-bold uppercase">{{ item.name }}</td>
          <td class="py-2 text-xs">{{ item.notes || '—' }}</td>
        </tr>
      </tbody>
    </table>

    <p class="mt-4 border-t border-dashed border-black pt-2 text-center text-xs">
      *** FIM DA COMANDA ***
    </p>
  </div>
</template>

<script setup>
defineProps({
  order: { type: Object, default: null },
});

function formatDateTime(value) {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
