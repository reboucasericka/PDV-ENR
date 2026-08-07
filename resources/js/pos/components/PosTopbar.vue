<template>
  <header class="border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
    <div class="mx-auto flex w-full max-w-[1800px] items-center justify-between">
      <div class="flex items-center gap-4">
        <span class="text-lg font-bold text-slate-900">☕ Dona Joana POS</span>
        <span class="text-sm text-slate-500">
          Caixa: <strong>{{ cashOpen ? 'ABERTO' : 'FECHADO' }}</strong>
        </span>
      </div>

      <div class="flex items-center gap-4">
        <span class="text-sm text-slate-700">👤 {{ operatorName }}</span>
        <form :action="logoutUrl" method="POST">
          <input type="hidden" name="_token" :value="csrfToken" />
          <button
            type="submit"
            class="text-sm font-semibold text-red-500 transition hover:text-red-600"
          >
            Logout
          </button>
        </form>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps({
  cashOpen: { type: Boolean, required: true },
  operatorName: { type: String, required: true },
  logoutUrl: { type: String, default: '/logout' },
  csrfToken: { type: String, required: true },
});
</script>
