<template>
    <section
        class="flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-md"
    >
        <div class="border-b border-slate-200 px-4 py-4 md:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">
                        Configurações
                    </h1>
                    <p class="text-sm text-slate-500">
                        Personalize os dados da Cafeteria Joana no sistema.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    :disabled="loading || saving"
                    @click="save"
                >
                    {{ saving ? "A guardar..." : "Salvar" }}
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="
                        activeTab === tab.id
                            ? 'bg-slate-900 text-white'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                    "
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4 md:p-6">
            <div v-if="loading" class="py-16 text-center text-slate-500">
                A carregar configurações...
            </div>

            <form
                v-else
                class="mx-auto max-w-4xl space-y-5"
                @submit.prevent="save"
            >
                <div
                    v-show="activeTab === 'geral'"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Nome da empresa</span
                        >
                        <input
                            v-model="form.company_name"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Nome comercial</span
                        >
                        <input
                            v-model="form.trade_name"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Telefone</span
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Email</span
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm md:col-span-2">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Website</span
                        >
                        <input
                            v-model="form.website"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm md:col-span-2">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Morada</span
                        >
                        <input
                            v-model="form.address"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Cidade</span
                        >
                        <input
                            v-model="form.city"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Codigo postal</span
                        >
                        <input
                            v-model="form.postal_code"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm md:col-span-2">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Pais</span
                        >
                        <input
                            v-model="form.country"
                            type="text"
                            class="input-field"
                        />
                    </label>
                </div>

                <div
                    v-show="activeTab === 'fiscal'"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >IVA (%)</span
                        >
                        <input
                            v-model.number="form.vat"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >NIF</span
                        >
                        <input
                            v-model="form.tax_number"
                            type="text"
                            class="input-field"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Moeda</span
                        >
                        <input
                            v-model="form.currency"
                            type="text"
                            class="input-field"
                            placeholder="EUR"
                        />
                    </label>
                    <label
                        class="flex items-end gap-2 pb-2 text-sm text-slate-700"
                    >
                        <input
                            v-model="form.is_open"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300"
                        />
                        Estabelecimento aberto
                    </label>
                </div>

                <div v-show="activeTab === 'logo'" class="space-y-4">
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
                    >
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            alt="Logo"
                            class="mx-auto mb-4 h-28 w-auto rounded-xl object-contain"
                        />
                        <p v-else class="mb-4 text-sm text-slate-500">
                            Nenhum logo definido.
                        </p>
                        <input
                            ref="logoInputRef"
                            type="file"
                            accept="image/*"
                            class="mx-auto block text-sm"
                            @change="onLogoSelected"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                            :disabled="!logoPreview"
                            @click="clearLogo"
                        >
                            Remover logo
                        </button>
                    </div>
                </div>

                <div v-show="activeTab === 'recibo'" class="grid gap-4">
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Rodape do recibo</span
                        >
                        <textarea
                            v-model="form.receipt_footer"
                            rows="5"
                            class="input-field"
                            placeholder="Texto impresso no final do recibo"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Impressora</span
                        >
                        <input
                            v-model="form.printer_name"
                            type="text"
                            class="input-field"
                        />
                    </label>
                </div>

                <div
                    v-show="activeTab === 'horario'"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Moeda</span
                        >
                        <input
                            v-model="form.currency"
                            type="text"
                            class="input-field"
                            placeholder="EUR"
                        />
                    </label>
                    <label class="block text-sm">
                        <span
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                            >Timezone</span
                        >
                        <input
                            v-model="form.timezone"
                            type="text"
                            class="input-field"
                            placeholder="Europe/Lisbon"
                        />
                    </label>
                </div>
            </form>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import axios from "axios";
import { toast } from "vue-sonner";

const tabs = [
    { id: "geral", label: "Geral" },
    { id: "fiscal", label: "Fiscal" },
    { id: "logo", label: "Logo" },
    { id: "recibo", label: "Recibo" },
    { id: "horario", label: "Horario" },
];

const activeTab = ref("geral");
const loading = ref(false);
const saving = ref(false);
const settingId = ref(null);
const logoFile = ref(null);
const logoPreview = ref("");
const removeLogo = ref(false);
const logoInputRef = ref(null);

const form = reactive({
    company_name: "",
    trade_name: "",
    tax_number: "",
    phone: "",
    email: "",
    website: "",
    address: "",
    city: "",
    postal_code: "",
    country: "",
    currency: "",
    timezone: "",
    receipt_footer: "",
    printer_name: "",
    vat: 0,
    is_open: true,
});

onMounted(load);

async function load() {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/v1/settings/current");
        applySetting(data.data);
    } catch (error) {
        console.error(error);
        toast.error(
            error.response?.data?.message || "Erro ao carregar configurações.",
        );
    } finally {
        loading.value = false;
    }
}

function applySetting(setting) {
    if (!setting) {
        return;
    }

    settingId.value = setting.id ?? null;
    form.company_name = setting.company_name ?? "";
    form.trade_name = setting.trade_name ?? "";
    form.tax_number = setting.tax_number ?? "";
    form.phone = setting.phone ?? "";
    form.email = setting.email ?? "";
    form.website = setting.website ?? "";
    form.address = setting.address ?? "";
    form.city = setting.city ?? "";
    form.postal_code = setting.postal_code ?? "";
    form.country = setting.country ?? "";
    form.currency = setting.currency ?? "";
    form.timezone = setting.timezone ?? "";
    form.receipt_footer = setting.receipt_footer ?? "";
    form.printer_name = setting.printer_name ?? "";
    form.vat = Number(setting.vat ?? 0);
    form.is_open = Boolean(setting.is_open);
    logoPreview.value = setting.logo_url || "";
    logoFile.value = null;
    removeLogo.value = false;
    if (logoInputRef.value) {
        logoInputRef.value.value = "";
    }
}

function onLogoSelected(event) {
    const file = event.target.files?.[0] ?? null;
    logoFile.value = file;
    removeLogo.value = false;

    if (!file) {
        return;
    }

    logoPreview.value = URL.createObjectURL(file);
}

function clearLogo() {
    logoFile.value = null;
    logoPreview.value = "";
    removeLogo.value = true;
    if (logoInputRef.value) {
        logoInputRef.value.value = "";
    }
}

async function save() {
    if (saving.value) {
        return;
    }

    saving.value = true;
    try {
        const body = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (key === "is_open") {
                body.append(key, value ? "1" : "0");
                return;
            }
            body.append(
                key,
                value === null || value === undefined ? "" : String(value),
            );
        });

        if (logoFile.value) {
            body.append("logo", logoFile.value);
        }

        if (removeLogo.value) {
            body.append("remove_logo", "1");
        }

        const { data } = await axios.post("/api/v1/settings/current", body, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        applySetting(data.data);
        toast.success("Configurações guardadas.");
    } catch (error) {
        console.error(error);
        const message =
            error.response?.data?.message ||
            Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
            "Erro ao guardar configurações.";
        toast.error(message);
    } finally {
        saving.value = false;
    }
}

defineExpose({ reload: load });
</script>

<style scoped>
.input-field {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #cbd5e1;
    background: #fff;
    padding: 0.65rem 0.85rem;
    font-size: 0.875rem;
    color: #0f172a;
}
.input-field:focus {
    outline: 2px solid #94a3b8;
    outline-offset: 1px;
}
</style>
