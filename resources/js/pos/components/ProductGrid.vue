<template>
  <section class="h-full rounded-2xl border-0 bg-white p-4 shadow-md md:p-6">
    <div class="mb-5 flex items-center justify-between">
      <h1 class="text-2xl font-extrabold text-slate-900 md:text-3xl">Produtos</h1>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">
        {{ products.length }} itens
      </span>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
      <button
        v-for="product in products"
        :key="product.id"
        type="button"
        class="group rounded-2xl border p-4 text-left shadow-sm transition hover:scale-[1.01] hover:shadow-md active:scale-[0.99]"
        :class="[product.categoryClass, product.isClicked ? 'ring-4 ring-green-300 ring-offset-1' : '']"
        @click="$emit('add-product', product)"
      >
        <div class="line-clamp-2 min-h-12 text-lg font-bold">
          {{ product.name }}
        </div>
        <div class="mt-3 text-xl font-extrabold tracking-tight">
          €{{ Number(product.price).toFixed(2) }}
        </div>
        <div class="mt-1 text-xs font-semibold uppercase opacity-80">
          {{ product.categoryLabel }}
        </div>
      </button>
    </div>
  </section>
</template>

<script setup>
defineProps({
  products: { type: Array, required: true },
});

defineEmits(['add-product']);
</script>
