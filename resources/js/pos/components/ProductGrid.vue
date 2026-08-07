<template>
  <section class="flex h-full flex-col rounded-2xl border-0 bg-white p-4 shadow-md md:p-6">
    <div class="mb-4 flex items-center justify-between gap-3">
      <h1 class="text-2xl font-extrabold text-slate-900 md:text-3xl">Produtos</h1>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">
        {{ products.length }} itens
      </span>
    </div>

    <div class="mb-3">
      <SearchBar
        ref="searchBarRef"
        :model-value="searchQuery"
        placeholder="Pesquisar por nome, SKU ou codigo..."
        @update:model-value="$emit('update:searchQuery', $event)"
      />
    </div>

    <div class="mb-4">
      <CategoryFilter
        :categories="categories"
        :selected-category-id="selectedCategoryId"
        @select="$emit('select-category', $event)"
      />
    </div>

    <div class="grid min-h-0 flex-1 grid-cols-2 content-start gap-3 overflow-y-auto md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
      <div v-if="products.length === 0" class="col-span-full">
        <EmptyState
          title="Nenhum produto encontrado"
          description="Tente outra pesquisa ou categoria."
          icon="🔍"
        />
      </div>
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        :is-clicked="product.isClicked"
        @add="$emit('add-product', $event)"
      />
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import CategoryFilter from './CategoryFilter.vue';
import ProductCard from './ProductCard.vue';
import SearchBar from './SearchBar.vue';
import EmptyState from './ui/EmptyState.vue';

defineProps({
  products: { type: Array, required: true },
  categories: { type: Array, default: () => [] },
  selectedCategoryId: { type: [Number, String], default: null },
  searchQuery: { type: String, default: '' },
});

defineEmits(['add-product', 'select-category', 'update:searchQuery']);

const searchBarRef = ref(null);

defineExpose({
  focusSearch: () => searchBarRef.value?.focus(),
});
</script>
