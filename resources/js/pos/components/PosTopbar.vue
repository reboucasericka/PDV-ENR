<template>
  <header class="border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
    <div class="mx-auto flex w-full max-w-[1800px] items-center justify-between gap-4">
      <div class="flex min-w-0 flex-wrap items-center gap-x-6 gap-y-1">
        <span class="text-lg font-bold text-slate-900">☕ Cafeteria Joana POS</span>

        <span class="text-sm text-slate-500">
          Loja:
          <strong class="text-slate-800">{{ storeName || '—' }}</strong>
        </span>

        <span class="text-sm text-slate-500">
          Operador:
          <strong class="text-slate-800">{{ operatorName }}</strong>
        </span>

        <span class="text-sm text-slate-500">
          Caixa:
          <strong class="text-slate-800">{{ cashOpen ? 'Aberto' : 'Fechado' }}</strong>
        </span>
      </div>

      <form :action="logoutUrl" method="POST" class="shrink-0">
        <input type="hidden" name="_token" :value="csrfToken" />
        <button
          type="submit"
          class="text-sm font-semibold text-red-500 transition hover:text-red-600"
        >
          Logout
        </button>
      </form>
    </div>
  </header>
</template>

<script setup>
defineProps({
  cashOpen: { type: Boolean, required: true },
  operatorName: { type: String, required: true },
  storeName: { type: String, default: '' },
  logoutUrl: { type: String, default: '/logout' },
  csrfToken: { type: String, required: true },
});
</script>
