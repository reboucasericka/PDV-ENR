<template>
    <aside
        class="h-auto rounded-2xl border border-slate-200 bg-white p-3 shadow-sm md:h-full md:p-4"
        aria-label="Menu principal"
    >
        <h2
            class="mb-3 hidden text-xs font-bold uppercase tracking-wide text-slate-500 md:mb-4 md:block"
        >
            Menu
        </h2>

        <nav
            class="flex gap-2 overflow-x-auto pb-1 md:flex-col md:space-y-2 md:overflow-visible md:pb-0"
            aria-label="Navegação do POS"
        >
            <button
                v-for="item in items"
                :key="item.id"
                type="button"
                class="shrink-0 rounded-xl px-4 py-2.5 text-left text-sm font-semibold transition md:w-full md:py-3"
                :class="
                    activeItem === item.id
                        ? 'bg-slate-900 text-white shadow-sm'
                        : isDisabled(item.id)
                          ? 'bg-slate-100 text-slate-400'
                          : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                "
                :disabled="isDisabled(item.id)"
                :aria-current="activeItem === item.id ? 'page' : undefined"
                :aria-disabled="isDisabled(item.id) ? 'true' : undefined"
                @click="$emit('select', item.id)"
            >
                {{ item.label }}
            </button>
        </nav>
    </aside>
</template>

<script setup>
const props = defineProps({
    activeItem: { type: String, required: true },
    cashOpen: { type: Boolean, required: true },
});

defineEmits(["select"]);

const items = [
    { id: "dashboard", label: "Dashboard" },
    { id: "vendas", label: "Vendas" },
    { id: "caixa", label: "Caixa" },
    { id: "categorias", label: "Categorias" },
    { id: "produtos", label: "Produtos" },
    { id: "configuracoes", label: "Configurações" },
    { id: "relatorios", label: "Relatórios" },
];

const alwaysAvailable = new Set([
    "dashboard",
    "caixa",
    "categorias",
    "produtos",
    "configuracoes",
    "relatorios",
]);

function isDisabled(itemId) {
    if (alwaysAvailable.has(itemId)) {
        return false;
    }

    return !props.cashOpen;
}
</script>
