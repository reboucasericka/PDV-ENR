<template>
    <div class="flex h-screen flex-col bg-slate-200">
        <Toaster position="top-right" rich-colors close-button />

        <PosTopbar
            :cash-open="cashOpen"
            :operator-name="displayOperatorName"
            :store-name="displayStoreName"
            :csrf-token="csrfToken"
            logout-url="/logout"
        />

        <div class="flex flex-1 flex-col gap-4 overflow-hidden p-3 sm:p-4 md:p-6 lg:flex-row">
            <div class="w-full shrink-0 lg:w-56">
                <PosSidebar
                    :active-item="activeMenu"
                    :cash-open="cashOpen"
                    @select="handleMenuSelect"
                />
            </div>

            <main class="min-h-0 min-w-0 flex-1 overflow-auto" role="main" aria-label="Conteúdo do POS">
                <PageLoading
                    v-if="isBootstrapping"
                    title="A carregar o POS..."
                    subtitle="A verificar se já existe um caixa aberto."
                />

                <SelectStoreModal
                    v-else-if="needsStoreSelection"
                    :stores="stores"
                    :loading="loadingStores"
                    :selecting="selectingStore"
                    @select-store="onStoreSelected"
                />

                <component
                    :is="DashboardPage"
                    v-else-if="activeMenu === 'dashboard'"
                    ref="dashboardRef"
                />

                <component
                    :is="SettingsPage"
                    v-else-if="activeMenu === 'configuracoes'"
                    ref="settingsRef"
                />

                <component
                    :is="ReportsPage"
                    v-else-if="activeMenu === 'relatorios'"
                    ref="reportsRef"
                />

                <component
                    :is="CategoriesAdminPanel"
                    v-else-if="activeMenu === 'categorias'"
                />

                <component
                    :is="ProductsAdminPanel"
                    v-else-if="activeMenu === 'produtos'"
                />

                <CashModulePanel
                    v-else-if="activeMenu === 'caixa'"
                    ref="cashModuleRef"
                    :cash-open="cashOpen"
                    :can-open-cash="needsOpenCash"
                    :summary="cashSummary"
                    :store-name="displayStoreName"
                    :operator-name="displayOperatorName"
                    :opening="openingCash"
                    :closing="closingCash"
                    @open-cash="openCashRegister"
                    @confirm-close="confirmCloseCash"
                    @keep-open="activeMenu = 'vendas'"
                />

                <OpenCashPanel
                    v-else-if="needsOpenCash"
                    :opening="openingCash"
                    :store-name="selectedStore?.name || ''"
                    @open-cash="openCashRegister"
                />

                <div
                    v-else-if="activeMenu === 'vendas' && cashOpen"
                    class="grid h-full grid-cols-1 gap-4 xl:grid-cols-[1fr_26rem]"
                >
                    <ProductGrid
                        ref="productGridRef"
                        :products="displayProducts"
                        :categories="categories"
                        :selected-category-id="selectedCategoryId"
                        :search-query="productSearch"
                        @add-product="addToCart"
                        @select-category="onSelectCategory"
                        @update:search-query="productSearch = $event"
                    />
                    <CartPanel
                        :cart="cart"
                        :received-amount="receivedAmount"
                        :total="total"
                        :change="change"
                        :keypad-keys="keypadKeys"
                        :can-pay="canPay"
                        :processing="processing"
                        :payment-method="paymentMethod"
                        :payment-methods="paymentMethods"
                        :items-count="cartItemsCount"
                        :units-count="cartUnitsCount"
                        :selected-item-id="selectedCartItemId"
                        @decrease-item="decreaseItem"
                        @increase-item="increaseItem"
                        @remove-item="removeItem"
                        @set-quantity="setItemQuantity"
                        @select-item="selectedCartItemId = $event"
                        @keypad-press="onKeypadPress"
                        @update:payment-method="onPaymentMethodChange"
                        @cancel-sale="cancelSale"
                        @pay="pay"
                    />
                </div>

                <EmptyState
                    v-else
                    :title="menuLabel(activeMenu)"
                    description="Modulo em preparacao."
                    icon="🛠️"
                />

                <OrderDetailModal
                    :open="orderModalOpen"
                    :loading="orderModalLoading"
                    :order="lastOrder"
                    :company="companySettings"
                    :amount-received="lastAmountReceived"
                    :change-amount="lastChangeAmount"
                    @close="orderModalOpen = false"
                />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, defineAsyncComponent, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import axios from "axios";
import { toast } from "vue-sonner";
import CartPanel from "../components/CartPanel.vue";
import CashModulePanel from "../components/CashModulePanel.vue";
import OpenCashPanel from "../components/OpenCashPanel.vue";
import PosSidebar from "../components/PosSidebar.vue";
import PosTopbar from "../components/PosTopbar.vue";
import ProductGrid from "../components/ProductGrid.vue";
import SelectStoreModal from "../components/SelectStoreModal.vue";
import EmptyState from "../components/ui/EmptyState.vue";
import OrderDetailModal from "../components/OrderDetailModal.vue";
import PageLoading from "../components/ui/PageLoading.vue";
import { friendlyError } from "../utils/errors";

const CategoriesAdminPanel = defineAsyncComponent(
    () => import("../components/CategoriesAdminPanel.vue")
);
const ProductsAdminPanel = defineAsyncComponent(
    () => import("../components/ProductsAdminPanel.vue")
);
const DashboardPage = defineAsyncComponent(() => import("./DashboardPage.vue"));
const ReportsPage = defineAsyncComponent(() => import("./ReportsPage.vue"));
const SettingsPage = defineAsyncComponent(() => import("./SettingsPage.vue"));

const products = ref([]);
const categories = ref([]);
const stores = ref([]);
const cart = ref([]);
const processing = ref(false);
const openingCash = ref(false);
const closingCash = ref(false);
const selectingStore = ref(false);
const loadingStores = ref(false);
const cashDigits = ref("0");
const audioContext = ref(null);
const cashOpen = ref(false);
const selectedStore = ref(null);
const currentCash = ref(null);
const activeMenu = ref("caixa");
const selectedCategoryId = ref(null);
const productSearch = ref("");
const selectedCartItemId = ref(null);
const clickedProductIds = ref(new Set());
const paymentMethod = ref("cash");
const productGridRef = ref(null);
const cashModuleRef = ref(null);
const dashboardRef = ref(null);
const settingsRef = ref(null);
const reportsRef = ref(null);
const isBootstrapping = ref(true);
const orderModalOpen = ref(false);
const orderModalLoading = ref(false);
const lastOrder = ref(null);
const lastAmountReceived = ref(null);
const lastChangeAmount = ref(null);
const companySettings = ref(null);

const paymentMethods = [
    { value: "cash", label: "Dinheiro" },
    { value: "card", label: "Cartao" },
    { value: "mbway", label: "MBWay" },
    { value: "multibanco", label: "Multibanco" },
];

const appElement = document.getElementById("app");
const operatorName = appElement?.dataset.userName || "Operador";
const csrfToken = appElement?.dataset.csrfToken || "";

const needsStoreSelection = computed(
    () => !cashOpen.value && !selectedStore.value,
);
const needsOpenCash = computed(() => !cashOpen.value && !!selectedStore.value);
const displayStoreName = computed(
    () => selectedStore.value?.name || currentCash.value?.store?.name || "",
);
const displayOperatorName = computed(
    () => currentCash.value?.operator?.name || operatorName,
);

const keypadKeys = [
    { label: "1", action: "digit", value: "1" },
    { label: "2", action: "digit", value: "2" },
    { label: "3", action: "digit", value: "3" },
    { label: "4", action: "digit", value: "4" },
    { label: "5", action: "digit", value: "5" },
    { label: "6", action: "digit", value: "6" },
    { label: "7", action: "digit", value: "7" },
    { label: "8", action: "digit", value: "8" },
    { label: "9", action: "digit", value: "9" },
    { label: "C", action: "clear" },
    { label: "0", action: "digit", value: "0" },
    { label: "⌫", action: "backspace" },
];

const isCashPayment = computed(() => paymentMethod.value === "cash");
const receivedAmount = computed(() => Number(cashDigits.value) / 100);
const total = computed(() =>
    cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
);
const change = computed(() =>
    isCashPayment.value ? Math.max(receivedAmount.value - total.value, 0) : 0,
);
const canPay = computed(() => {
    if (
        !cashOpen.value ||
        activeMenu.value !== "vendas" ||
        cart.value.length === 0 ||
        processing.value ||
        total.value <= 0
    ) {
        return false;
    }

    if (isCashPayment.value) {
        return receivedAmount.value >= total.value;
    }

    return true;
});
const displayProducts = computed(() => {
    const term = productSearch.value.trim().toLowerCase();

    let list = products.value.map((product) => ({
        ...product,
        isClicked: clickedProductIds.value.has(product.id),
    }));

    if (term) {
        list = list.filter((product) => {
            const name = String(product.name || "").toLowerCase();
            const sku = String(product.sku || "").toLowerCase();
            const code = String(product.id);
            return (
                name.includes(term) || sku.includes(term) || code.includes(term)
            );
        });
    }

    return list;
});

const cartItemsCount = computed(() => cart.value.length);
const cartUnitsCount = computed(() =>
    cart.value.reduce((sum, item) => sum + Number(item.quantity), 0),
);

const cashSummary = computed(() => ({
    opening_balance: currentCash.value?.opening_balance ?? 0,
    orders_count: currentCash.value?.orders_count ?? 0,
    sales_total: currentCash.value?.sales_total ?? 0,
    payment_totals: currentCash.value?.payment_totals ?? [],
    expected_balance: currentCash.value?.expected_balance ?? 0,
    opened_at: currentCash.value?.opened_at ?? null,
    closed_at: currentCash.value?.closed_at ?? new Date().toISOString(),
}));

onMounted(async () => {
    window.addEventListener("keydown", handleGlobalKeydown);

    try {
        // Fonte de verdade: cash_registers (OPEN) — nunca abrir caixa automaticamente.
        await restoreFromCurrentCash();

        if (!cashOpen.value) {
            await loadStores();
        }

        await Promise.all([loadCategories(), loadProducts(), loadCompanySettings()]);
    } catch (error) {
        console.error(error);
    } finally {
        isBootstrapping.value = false;
    }
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleGlobalKeydown);
});

async function loadCompanySettings() {
    try {
        const { data } = await axios.get("/api/v1/settings/current");
        companySettings.value = data?.data ?? null;
    } catch (error) {
        console.debug("Settings indisponiveis no boot", error);
    }
}
async function loadCategories() {
    const { data } = await axios.get("/api/v1/categories/active");
    categories.value = data.data ?? [];
}

async function loadProducts(categoryId = selectedCategoryId.value) {
    const params = {};
    if (categoryId) {
        params.category_id = categoryId;
    }

    const response = await axios.get("/api/v1/products", { params });
    products.value = dedupeProducts(response.data.data ?? []);
}

function dedupeProducts(list) {
    const seen = new Set();
    return list.filter((item) => {
        if (seen.has(item.id)) {
            return false;
        }

        seen.add(item.id);
        return true;
    });
}

function menuLabel(menuId) {
    const labels = {
        dashboard: "Dashboard",
        vendas: "Vendas",
        caixa: "Caixa",
        categorias: "Categorias",
        produtos: "Produtos",
        configuracoes: "Configurações",
        relatorios: "Relatórios",
    };
    return labels[menuId] || "Modulo";
}

async function onSelectCategory(categoryId) {
    selectedCategoryId.value = categoryId;
    try {
        await loadProducts(categoryId);
    } catch (error) {
        console.error(error);
        toast.error(friendlyError(error, "Erro ao filtrar produtos."));
    }
}

function addToCart(product) {
    playBeep();
    markProductClicked(product.id);

    const existing = cart.value.find((p) => p.id === product.id);
    if (existing) {
        existing.quantity += 1;
        selectedCartItemId.value = existing.id;
        return;
    }

    cart.value.push({
        id: product.id,
        name: product.name,
        price: Number(product.price),
        quantity: 1,
    });
    selectedCartItemId.value = product.id;
}

function markProductClicked(productId) {
    clickedProductIds.value.add(productId);
    clickedProductIds.value = new Set(clickedProductIds.value);

    setTimeout(() => {
        clickedProductIds.value.delete(productId);
        clickedProductIds.value = new Set(clickedProductIds.value);
    }, 180);
}

function increaseItem(productId) {
    const existing = cart.value.find((item) => item.id === productId);
    if (!existing) {
        return;
    }
    existing.quantity += 1;
    selectedCartItemId.value = productId;
}

function decreaseItem(productId) {
    const existing = cart.value.find((item) => item.id === productId);
    if (!existing) {
        return;
    }

    selectedCartItemId.value = productId;

    if (existing.quantity <= 1) {
        cart.value = cart.value.filter((item) => item.id !== productId);
        if (selectedCartItemId.value === productId) {
            selectedCartItemId.value = cart.value[0]?.id ?? null;
        }
        return;
    }

    existing.quantity -= 1;
}

function removeItem(productId) {
    cart.value = cart.value.filter((item) => item.id !== productId);
    if (selectedCartItemId.value === productId) {
        selectedCartItemId.value = cart.value[0]?.id ?? null;
    }
}

function setItemQuantity(productId, quantity) {
    const existing = cart.value.find((item) => item.id === productId);
    if (!existing) {
        return;
    }

    const next = Math.floor(Number(quantity));
    if (!Number.isFinite(next) || next < 1) {
        removeItem(productId);
        return;
    }

    existing.quantity = next;
    selectedCartItemId.value = productId;
}

function resetSaleState() {
    cart.value = [];
    cashDigits.value = "0";
    paymentMethod.value = "cash";
    selectedCartItemId.value = null;
    productSearch.value = "";
}

function cancelSale() {
    if (cart.value.length === 0 && cashDigits.value === "0") {
        return;
    }

    if (!window.confirm("Tem certeza que deseja cancelar a venda?")) {
        return;
    }

    resetSaleState();
    toast.message("Venda cancelada");
}

function onKeypadPress(key) {
    if (key.action === "digit") {
        appendDigit(key.value);
        return;
    }

    if (key.action === "clear") {
        cashDigits.value = "0";
        return;
    }

    if (key.action === "backspace") {
        cashDigits.value =
            cashDigits.value.length > 1 ? cashDigits.value.slice(0, -1) : "0";
    }
}

function appendDigit(digit) {
    const next =
        cashDigits.value === "0" ? digit : `${cashDigits.value}${digit}`;
    cashDigits.value = next.slice(0, 8);
}

async function pay() {
    if (!canPay.value) {
        return;
    }

    if (!currentCash.value?.id) {
        toast.warning("Abra o caixa antes de registar vendas.");
        return;
    }

    const saleSnapshot = {
        amountReceived: isCashPayment.value ? receivedAmount.value : null,
        changeAmount: isCashPayment.value ? change.value : null,
    };

    processing.value = true;
    try {
        const { data } = await axios.post("/api/v1/orders", {
            cash_register_id: currentCash.value.id,
            status: "paid",
            payment_method: paymentMethod.value,
            items: cart.value.map((item) => ({
                product_id: item.id,
                quantity: item.quantity,
            })),
        });

        const created = data.data ?? null;
        lastAmountReceived.value = saleSnapshot.amountReceived;
        lastChangeAmount.value = saleSnapshot.changeAmount;

        if (isCashPayment.value) {
            toast.success(
                `Venda realizada com sucesso. Troco: €${saleSnapshot.changeAmount.toFixed(2)}`,
            );
        } else {
            toast.success("Venda realizada com sucesso");
        }

        resetSaleState();
        await refreshCashSummary();

        if (created?.id) {
            await openOrderDetail(created.id, created);
        }
    } catch (error) {
        console.error(error);
        toast.error(friendlyError(error, "Erro ao criar pedido."));
    } finally {
        processing.value = false;
    }
}

async function openOrderDetail(orderId, preset = null) {
    orderModalOpen.value = true;
    orderModalLoading.value = true;

    try {
        if (!companySettings.value) {
            const settingsResponse = await axios.get("/api/v1/settings/current");
            companySettings.value = settingsResponse.data?.data ?? null;
        }

        if (preset?.items) {
            lastOrder.value = preset;
        } else {
            const { data } = await axios.get(`/api/v1/orders/${orderId}`);
            lastOrder.value = data.data ?? null;
        }
    } catch (error) {
        console.error(error);
        toast.error(friendlyError(error, "Erro ao carregar pedido."));
        orderModalOpen.value = false;
    } finally {
        orderModalLoading.value = false;
    }
}

function printLastReceipt() {
    if (!lastOrder.value) {
        toast.message("Nenhum recibo disponivel. Conclua uma venda primeiro.");
        return;
    }

    if (!orderModalOpen.value) {
        orderModalOpen.value = true;
    }

    nextTick(() => {
        const target = document.getElementById("print-receipt");
        if (!target) {
            toast.error("Recibo nao encontrado para impressao.");
            return;
        }
        document.body.setAttribute("data-print-target", "print-receipt");
        window.print();
        document.body.removeAttribute("data-print-target");
    });
}

function onPaymentMethodChange(method) {
    paymentMethod.value = method;
    if (method !== "cash") {
        cashDigits.value = "0";
    }
}

async function refreshCashSummary() {
    try {
        const response = await axios.get("/api/v1/cash/current");
        const payload = response.data?.data ?? null;
        if (payload?.status === "OPEN") {
            currentCash.value = payload;
        }
    } catch (error) {
        console.error(error);
    }
}

async function confirmCloseCash() {
    if (closingCash.value || !cashOpen.value) {
        return;
    }

    closingCash.value = true;
    try {
        await axios.post("/api/v1/cash/close");
        cashOpen.value = false;
        currentCash.value = null;
        cart.value = [];
        cashDigits.value = "0";
        activeMenu.value = "caixa";
        toast.success("Caixa fechado com sucesso.");
        await nextTick();
        cashModuleRef.value?.showHistory?.();
    } catch (error) {
        console.error(error);
        toast.error(friendlyError(error, "Nao foi possivel fechar o caixa."));
    } finally {
        closingCash.value = false;
    }
}

async function loadStores() {
    loadingStores.value = true;
    try {
        const { data } = await axios.get("/api/v1/stores/active");
        stores.value = data.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error(friendlyError(error, "Erro ao carregar lojas."));
    } finally {
        loadingStores.value = false;
    }
}

/**
 * Consulta GET /cash/current e restaura o estado do PDV.
 * Se houver caixa OPEN, entra direto em Vendas (sem abrir novo caixa).
 *
 * @param {{ clearStoreIfClosed?: boolean }} options
 */
async function restoreFromCurrentCash(options = {}) {
    const clearStoreIfClosed = options.clearStoreIfClosed !== false;

    try {
        const response = await axios.get("/api/v1/cash/current");
        const payload = response.data?.data ?? null;
        const isOpen = payload?.status === "OPEN" && !!payload?.id;

        if (!isOpen) {
            cashOpen.value = false;
            currentCash.value = null;
            if (clearStoreIfClosed) {
                selectedStore.value = null;
            }
            activeMenu.value = "caixa";
            return false;
        }

        applyOpenCashPayload(payload);
        return true;
    } catch (error) {
        console.error(error);
        cashOpen.value = false;
        currentCash.value = null;
        if (clearStoreIfClosed) {
            selectedStore.value = null;
        }
        activeMenu.value = "caixa";
        return false;
    }
}

function applyOpenCashPayload(payload) {
    cashOpen.value = true;
    currentCash.value = payload;

    if (payload?.store) {
        selectedStore.value = {
            id: payload.store.id,
            name: payload.store.name,
            city: payload.store.city ?? "",
        };
    }

    activeMenu.value = "vendas";
}

async function openCashRegister(openingBalance) {
    if (!selectedStore.value?.id) {
        toast.error("Selecione a loja primeiro");
        return;
    }

    if (openingCash.value) {
        return;
    }

    const storeId = selectedStore.value.id;

    // Segurança: se ja existir caixa OPEN, apenas restaura — nao faz POST open.
    // Nao limpa a loja ja selecionada se o caixa estiver fechado.
    const alreadyOpen = await restoreFromCurrentCash({
        clearStoreIfClosed: false,
    });
    if (alreadyOpen) {
        toast.message("Caixa ja estava aberto. A retomar a sessao.");
        return;
    }

    const balance = Number(openingBalance);
    if (!Number.isFinite(balance) || balance < 0) {
        toast.error("Informe um saldo inicial valido.");
        return;
    }

    openingCash.value = true;
    try {
        const response = await axios.post("/api/v1/cash/open", {
            store_id: storeId,
            opening_balance: balance,
        });

        const payload = response.data?.data;
        if (payload?.status === "OPEN") {
            applyOpenCashPayload(payload);
            toast.success("Caixa aberto.");
        }
    } catch (error) {
        console.error(error);
        const recovered = await restoreFromCurrentCash({
            clearStoreIfClosed: false,
        });
        if (recovered) {
            toast.message("Caixa ja estava aberto. A retomar a sessao.");
            return;
        }
        toast.error(
            friendlyError(error, "Nao foi possivel abrir o caixa."),
        );
    } finally {
        openingCash.value = false;
    }
}

function onStoreSelected(store) {
    if (selectingStore.value || !store?.id) {
        return;
    }

    selectingStore.value = true;
    selectedStore.value = store;
    selectingStore.value = false;
}

async function handleMenuSelect(menu) {
    if (!selectedStore.value) {
        toast.warning("Selecione a loja primeiro.");
        return;
    }

    const adminMenus = [
        "dashboard",
        "categorias",
        "produtos",
        "caixa",
        "configuracoes",
        "relatorios",
    ];
    if (!cashOpen.value && !adminMenus.includes(menu)) {
        toast.warning("Abra o caixa primeiro.");
        return;
    }

    activeMenu.value = menu;

    if (menu === "caixa" && cashOpen.value) {
        await refreshCashSummary();
    }

    if (menu === "dashboard") {
        await nextTick();
        dashboardRef.value?.reload?.();
    }

    if (menu === "configuracoes") {
        await nextTick();
        settingsRef.value?.reload?.();
    }

    if (menu === "relatorios") {
        await nextTick();
        reportsRef.value?.reload?.();
    }

    if (menu === "vendas") {
        await loadProducts(selectedCategoryId.value);
    }
}

function handleGlobalKeydown(event) {
    const tag = String(event.target?.tagName || "").toLowerCase();
    const typingInField =
        tag === "input" ||
        tag === "textarea" ||
        event.target?.isContentEditable;

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "p") {
        event.preventDefault();
        printLastReceipt();
        return;
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "f") {
        if (activeMenu.value === "vendas" && cashOpen.value) {
            event.preventDefault();
            productGridRef.value?.focusSearch?.();
        }
        return;
    }

    if (event.key === "Escape") {
        if (orderModalOpen.value) {
            event.preventDefault();
            orderModalOpen.value = false;
            return;
        }
        if (activeMenu.value === "vendas" && cashOpen.value) {
            event.preventDefault();
            cancelSale();
        }
        return;
    }

    if (event.key === "Delete") {
        if (typingInField) {
            return;
        }
        if (activeMenu.value === "vendas" && selectedCartItemId.value) {
            event.preventDefault();
            removeItem(selectedCartItemId.value);
        }
        return;
    }

    if (event.key === "Enter") {
        if (
            typingInField &&
            tag === "input" &&
            event.target?.type === "search"
        ) {
            return;
        }
        if (activeMenu.value === "vendas" && cashOpen.value) {
            event.preventDefault();
            pay();
        }
    }
}

function playBeep() {
    try {
        if (!audioContext.value) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) {
                return;
            }
            audioContext.value = new AudioCtx();
        }

        const osc = audioContext.value.createOscillator();
        const gain = audioContext.value.createGain();

        osc.type = "square";
        osc.frequency.value = 1200;
        gain.gain.value = 0.06;

        osc.connect(gain);
        gain.connect(audioContext.value.destination);
        osc.start();
        osc.stop(audioContext.value.currentTime + 0.04);
    } catch (error) {
        console.debug("Beep indisponivel", error);
    }
}
</script>
