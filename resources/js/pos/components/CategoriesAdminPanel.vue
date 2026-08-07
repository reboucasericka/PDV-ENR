<template>
  <section class="h-full overflow-y-auto rounded-2xl bg-white p-4 shadow-md md:p-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Categorias</h1>
        <p class="text-sm text-slate-500">Gerencie o cardapio da cafeteria.</p>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
        @click="startCreate"
      >
        Nova categoria
      </button>
    </div>

    <div v-if="formOpen" class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
      <h2 class="mb-3 text-sm font-bold uppercase text-slate-500">
        {{ editingId ? 'Editar categoria' : 'Nova categoria' }}
      </h2>
      <div class="grid gap-3 md:grid-cols-2">
        <input v-model="form.name" type="text" placeholder="Nome" class="input-field" />
        <input v-model="form.icon" type="text" placeholder="Icone (ex: coffee)" class="input-field" />
        <input v-model="form.color" type="text" placeholder="Cor (#0ea5e9)" class="input-field" />
        <input v-model.number="form.sort_order" type="number" min="0" placeholder="Ordem" class="input-field" />
      </div>
      <label class="mt-3 flex items-center gap-2 text-sm text-slate-700">
        <input v-model="form.is_active" type="checkbox" />
        Ativa no POS
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

    <LoadingSkeleton v-if="loading" :rows="5" height="2.75rem" />
    <EmptyState
      v-else-if="categories.length === 0"
      title="Nenhuma categoria"
      description="Crie a primeira categoria do cardapio."
      icon="📂"
    />
    <div v-else class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
          <tr>
            <th class="px-2 py-3">Ordem</th>
            <th class="px-2 py-3">Nome</th>
            <th class="px-2 py-3">Cor</th>
            <th class="px-2 py-3">Produtos</th>
            <th class="px-2 py-3">Estado</th>
            <th class="px-2 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="border-b border-slate-100">
            <td class="px-2 py-3">{{ category.sort_order }}</td>
            <td class="px-2 py-3 font-semibold text-slate-900">
              {{ category.icon ? `${category.icon} ` : '' }}{{ category.name }}
            </td>
            <td class="px-2 py-3">
              <span
                class="inline-block h-4 w-8 rounded"
                :style="{ backgroundColor: category.color || '#cbd5e1' }"
              />
            </td>
            <td class="px-2 py-3">{{ category.products_count ?? 0 }}</td>
            <td class="px-2 py-3">
              <span :class="category.is_active ? 'text-emerald-700' : 'text-slate-400'">
                {{ category.is_active ? 'Ativa' : 'Inativa' }}
              </span>
            </td>
            <td class="px-2 py-3">
              <div class="flex justify-end gap-2">
                <button type="button" class="text-xs font-semibold text-slate-700" @click="startEdit(category)">
                  Editar
                </button>
                <button
                  type="button"
                  class="text-xs font-semibold text-slate-700"
                  @click="toggleActive(category)"
                >
                  {{ category.is_active ? 'Desativar' : 'Ativar' }}
                </button>
                <button type="button" class="text-xs font-semibold text-red-600" @click="remove(category)">
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

const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
const formOpen = ref(false);
const editingId = ref(null);

const form = reactive({
  name: '',
  icon: '',
  color: '#0ea5e9',
  sort_order: 0,
  is_active: true,
});

onMounted(load);

async function load() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/v1/categories');
    categories.value = data.data ?? [];
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao carregar categorias.'));
  } finally {
    loading.value = false;
  }
}

function startCreate() {
  editingId.value = null;
  form.name = '';
  form.icon = '';
  form.color = '#0ea5e9';
  form.sort_order = (categories.value.at(-1)?.sort_order ?? 0) + 1;
  form.is_active = true;
  formOpen.value = true;
}

function startEdit(category) {
  editingId.value = category.id;
  form.name = category.name;
  form.icon = category.icon || '';
  form.color = category.color || '#0ea5e9';
  form.sort_order = category.sort_order ?? 0;
  form.is_active = !!category.is_active;
  formOpen.value = true;
}

function closeForm() {
  formOpen.value = false;
  editingId.value = null;
}

async function save() {
  if (!form.name.trim()) {
    toast.warning('Informe o nome da categoria.');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      name: form.name.trim(),
      icon: form.icon || null,
      color: form.color || null,
      sort_order: Number(form.sort_order) || 0,
      is_active: !!form.is_active,
    };

    if (editingId.value) {
      await axios.put(`/api/v1/categories/${editingId.value}`, payload);
      toast.success('Categoria atualizada.');
    } else {
      await axios.post('/api/v1/categories', payload);
      toast.success('Categoria criada.');
    }

    closeForm();
    await load();
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao guardar categoria.'));
  } finally {
    saving.value = false;
  }
}

async function toggleActive(category) {
  try {
    const path = category.is_active ? 'deactivate' : 'activate';
    await axios.post(`/api/v1/categories/${category.id}/${path}`);
    await load();
    toast.success(category.is_active ? 'Categoria desativada.' : 'Categoria ativada.');
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao atualizar categoria.'));
  }
}

async function remove(category) {
  if (!window.confirm(`Excluir a categoria "${category.name}"?`)) {
    return;
  }

  try {
    await axios.delete(`/api/v1/categories/${category.id}`);
    await load();
    toast.success('Categoria excluida.');
  } catch (error) {
    console.error(error);
    toast.error(friendlyError(error, 'Erro ao excluir categoria.'));
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
