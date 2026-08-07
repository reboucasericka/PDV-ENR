<template>
  <section class="h-full overflow-y-auto rounded-2xl bg-white p-4 shadow-md md:p-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Produtos</h1>
        <p class="text-sm text-slate-500">Cadastre e atualize o cardapio sem alterar codigo.</p>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
        @click="startCreate"
      >
        Novo produto
      </button>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
      <select v-model="filterCategoryId" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" @change="load">
        <option value="">Todas as categorias</option>
        <option v-for="category in categories" :key="category.id" :value="String(category.id)">
          {{ category.name }}
        </option>
      </select>
      <select v-model="filterActive" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" @change="load">
        <option value="">Todos os estados</option>
        <option value="1">Ativos</option>
        <option value="0">Inativos</option>
      </select>
      <input
        v-model="search"
        type="search"
        placeholder="Buscar nome ou SKU"
        class="rounded-xl border border-slate-300 px-3 py-2 text-sm"
        @keyup.enter="load"
      />
      <button type="button" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" @click="load">
        Filtrar
      </button>
    </div>

    <div v-if="formOpen" class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
      <h2 class="mb-3 text-sm font-bold uppercase text-slate-500">
        {{ editingId ? 'Editar produto' : 'Novo produto' }}
      </h2>
      <div class="grid gap-3 md:grid-cols-2">
        <input v-model="form.name" type="text" placeholder="Nome" class="input-field" />
        <input v-model="form.sku" type="text" placeholder="SKU" class="input-field" />
        <select v-model="form.category_id" class="input-field">
          <option value="">Categoria</option>
          <option v-for="category in categories" :key="category.id" :value="String(category.id)">
            {{ category.name }}
          </option>
        </select>
        <input v-model.number="form.price" type="number" min="0" step="0.01" placeholder="Preco" class="input-field" />
        <input v-model.number="form.stock" type="number" min="0" placeholder="Stock" class="input-field" />
        <input v-model.number="form.sort_order" type="number" min="0" placeholder="Ordem" class="input-field" />
        <input v-model="form.button_color" type="text" placeholder="Cor do botao (#0ea5e9)" class="input-field" />
        <input v-model="form.image" type="text" placeholder="Imagem (URL ou caminho)" class="input-field" />
        <textarea
          v-model="form.description"
          rows="2"
          placeholder="Descricao"
          class="input-field md:col-span-2"
        />
      </div>
      <label class="mt-3 flex items-center gap-2 text-sm text-slate-700">
        <input v-model="form.is_active" type="checkbox" />
        Ativo no POS
      </label>
      <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
        <input v-model="form.is_favorite" type="checkbox" />
        ⭐ Favorito
      </label>
      <div class="mt-4 flex gap-2">
        <button
          type="button"
          class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
          :disabled="saving"
          @click="save"
        >
          {{ saving ? 'A guardar...' : 'Guardar' }}
        </button>
        <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm" @click="closeForm">
          Cancelar
        </button>
      </div>
    </div>

    <LoadingSkeleton v-if="loading" :rows="6" height="2.75rem" />
    <EmptyState
      v-else-if="products.length === 0"
      title="Nenhum produto"
      description="Ajuste os filtros ou cadastre um novo produto."
      icon="☕"
    />
    <div v-else class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-2 py-3">Produto</th>
            <th class="px-2 py-3">Categoria</th>
            <th class="px-2 py-3">Preco</th>
            <th class="px-2 py-3">Stock</th>
            <th class="px-2 py-3">Estado</th>
            <th class="px-2 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id" class="border-b border-slate-100">
            <td class="px-2 py-3">
              <p class="font-semibold text-slate-900">
                <span v-if="product.is_favorite">⭐ </span>{{ product.name }}
              </p>
              <p class="text-xs text-slate-400">{{ product.sku || 'sem SKU' }}</p>
            </td>
            <td class="px-2 py-3">{{ product.category?.name || '—' }}</td>
            <td class="px-2 py-3 font-semibold">€{{ Number(product.price).toFixed(2) }}</td>
            <td class="px-2 py-3">{{ product.stock }}</td>
            <td class="px-2 py-3">
              <span :class="product.is_active ? 'text-emerald-700' : 'text-slate-400'">
                {{ product.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-2 py-3">
              <div class="flex justify-end gap-2">
                <button type="button" class="text-xs font-semibold text-slate-700" @click="startEdit(product)">
                  Editar
                </button>
                <button type="button" class="text-xs font-semibold text-slate-700" @click="toggleActive(product)">
                  {{ product.is_active ? 'Desativar' : 'Ativar' }}
                </button>
                <button type="button" class="text-xs font-semibold text-red-600" @click="remove(product)">
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { friendlyError } from '../utils/errors';
import EmptyState from './ui/EmptyState.vue';
import LoadingSkeleton from './ui/LoadingSkeleton.vue';

const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
const formOpen = ref(false);
const editingId = ref(null);
const filterCategoryId = ref('');
const filterActive = ref('');
const search = ref('');

const form = reactive({
  name: '',
  sku: '',
  category_id: '',
  description: '',
  price: 0,
  stock: 0,
  image: '',
  button_color: '#0ea5e9',
  sort_order: 0,
  is_active: true,
  is_favorite: false,
});

onMounted(async () => {
  await loadCategories();
  await load();
});

async function loadCategories() {
  try {
    const { data } = await axios.get('/api/v1/categories');
    categories.value = data.data ?? [];
  } catch (error) {
    console.error(error);
  }
}

async function load() {
  loading.value = true;
  try {
    const params = { all: 1 };
    if (filterCategoryId.value) {
      params.category_id = filterCategoryId.value;
    }
    if (filterActive.value !== '') {
      params.is_active = filterActive.value;
    }
    if (search.value.trim()) {
      params.search = search.value.trim();
    }

    const { data } = await axios.get('/api/v1/products', { params });
    products.value = data.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao carregar produtos.'));
  } finally {
    loading.value = false;
  }
}

function startCreate() {
  editingId.value = null;
  form.name = '';
  form.sku = '';
  form.category_id = categories.value[0] ? String(categories.value[0].id) : '';
  form.description = '';
  form.price = 0;
  form.stock = 0;
  form.image = '';
  form.button_color = categories.value[0]?.color || '#0ea5e9';
  form.sort_order = 0;
  form.is_active = true;
  form.is_favorite = false;
  formOpen.value = true;
}

function startEdit(product) {
  editingId.value = product.id;
  form.name = product.name;
  form.sku = product.sku || '';
  form.category_id = product.category_id ? String(product.category_id) : '';
  form.description = product.description || '';
  form.price = Number(product.price);
  form.stock = Number(product.stock ?? 0);
  form.image = product.image || '';
  form.button_color = product.button_color || '#0ea5e9';
  form.sort_order = product.sort_order ?? 0;
  form.is_active = !!product.is_active;
  form.is_favorite = !!product.is_favorite;
  formOpen.value = true;
}

function closeForm() {
  formOpen.value = false;
  editingId.value = null;
}

async function save() {
  if (!form.name.trim() || !form.category_id) {
    toast.warning('Informe nome e categoria.');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      name: form.name.trim(),
      sku: form.sku.trim() || null,
      category_id: Number(form.category_id),
      description: form.description || null,
      price: Number(form.price),
      stock: Number(form.stock) || 0,
      image: form.image || null,
      button_color: form.button_color || null,
      sort_order: Number(form.sort_order) || 0,
      is_active: !!form.is_active,
      is_favorite: !!form.is_favorite,
    };

    if (editingId.value) {
      await axios.put(`/api/v1/products/${editingId.value}`, payload);
      toast.success('Produto atualizado.');
    } else {
      await axios.post('/api/v1/products', payload);
      toast.success('Produto criado.');
    }

    closeForm();
    await load();
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao guardar produto.'));
  } finally {
    saving.value = false;
  }
}

async function toggleActive(product) {
  try {
    const path = product.is_active ? 'deactivate' : 'activate';
    await axios.post(`/api/v1/products/${product.id}/${path}`);
    await load();
    toast.success(product.is_active ? 'Produto desativado.' : 'Produto ativado.');
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao atualizar produto.'));
  }
}

async function remove(product) {
  if (!window.confirm(`Excluir o produto "${product.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/v1/products/${product.id}`);
    await load();
    toast.success('Produto excluido.');
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao excluir produto.'));
  }
}
</script>

<style scoped>
.input-field {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  background: #fff;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  outline: none;
}
.input-field:focus {
  border-color: #64748b;
}
</style>
