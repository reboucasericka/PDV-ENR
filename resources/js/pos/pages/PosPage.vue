<template>
  <div class="flex h-screen flex-col bg-slate-200">
    <PosTopbar
      :cash-open="cashOpen"
      :operator-name="operatorName"
      :csrf-token="csrfToken"
      logout-url="/logout"
    />

    <div class="flex flex-1 gap-4 p-4 md:p-6">
      <div class="w-56 shrink-0">
        <PosSidebar :active-item="activeMenu" :cash-open="cashOpen" @select="handleMenuSelect" />
      </div>

      <main class="min-w-0 flex-1">
        <OpenCashPanel v-if="!cashOpen" :opening="openingCash" @open-cash="openCashRegister" />

        <div
          v-else-if="activeMenu === 'vendas'"
          class="grid h-full grid-cols-1 gap-4 xl:grid-cols-[1fr_26rem]"
        >
          <ProductGrid :products="displayProducts" @add-product="addToCart" />
          <CartPanel
            :cart="cart"
            :received-amount="receivedAmount"
            :total="total"
            :change="change"
            :keypad-keys="keypadKeys"
            :can-pay="canPay"
            :processing="processing"
            @decrease-item="decreaseItem"
            @keypad-press="onKeypadPress"
            @pay="pay"
          />
        </div>

        <section
          v-else-if="activeMenu === 'caixa'"
          class="flex h-full items-center justify-center rounded-2xl bg-white p-8 text-center shadow-md"
        >
          <div>
            <h2 class="text-2xl font-bold text-slate-900">Caixa aberto</h2>
            <p class="mt-2 text-sm text-slate-500">
              Caixa operacional. Continue para o menu de Vendas para registrar pedidos.
            </p>
            <button
              type="button"
              class="mt-5 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
              @click="activeMenu = 'vendas'"
            >
              Ir para Vendas
            </button>
          </div>
        </section>

        <section
          v-else
          class="flex h-full items-center justify-center rounded-2xl bg-white p-8 text-center shadow-md"
        >
          <div>
            <h2 class="text-2xl font-bold text-slate-900">
              {{ menuLabel(activeMenu) }}
            </h2>
            <p class="mt-2 text-sm text-slate-500">
              Modulo em preparacao. Use "Vendas" para operar o caixa.
            </p>
          </div>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import CartPanel from '../components/CartPanel.vue';
import OpenCashPanel from '../components/OpenCashPanel.vue';
import PosSidebar from '../components/PosSidebar.vue';
import PosTopbar from '../components/PosTopbar.vue';
import ProductGrid from '../components/ProductGrid.vue';

const products = ref([]);
const cart = ref([]);
const processing = ref(false);
const openingCash = ref(false);
const cashDigits = ref('0');
const audioContext = ref(null);
const cashOpen = ref(false);
const activeMenu = ref('caixa');
const clickedProductIds = ref(new Set());

const appElement = document.getElementById('app');
const operatorName = appElement?.dataset.userName || 'Operador';
const csrfToken = appElement?.dataset.csrfToken || '';

const keypadKeys = [
  { label: '1', action: 'digit', value: '1' },
  { label: '2', action: 'digit', value: '2' },
  { label: '3', action: 'digit', value: '3' },
  { label: '4', action: 'digit', value: '4' },
  { label: '5', action: 'digit', value: '5' },
  { label: '6', action: 'digit', value: '6' },
  { label: '7', action: 'digit', value: '7' },
  { label: '8', action: 'digit', value: '8' },
  { label: '9', action: 'digit', value: '9' },
  { label: 'C', action: 'clear' },
  { label: '0', action: 'digit', value: '0' },
  { label: '⌫', action: 'backspace' },
];

const receivedAmount = computed(() => Number(cashDigits.value) / 100);
const total = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
);
const change = computed(() => Math.max(receivedAmount.value - total.value, 0));
const canPay = computed(
  () =>
    cashOpen.value &&
    activeMenu.value === 'vendas' &&
    cart.value.length > 0 &&
    !processing.value &&
    receivedAmount.value >= total.value &&
    total.value > 0
);
const displayProducts = computed(() =>
  products.value.map((product) => ({
    ...product,
    categoryLabel: normalizeCategory(product),
    categoryClass: categoryClasses(product),
    isClicked: clickedProductIds.value.has(product.id),
  }))
);

onMounted(async () => {
  window.addEventListener('keydown', handleGlobalKeydown);

  try {
    const response = await axios.get('/api/v1/products');
    products.value = dedupeProducts(response.data.data ?? []);
  } catch (error) {
    console.error(error);
    alert('Erro ao carregar produtos');
  }

  await fetchCashStatus();
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleGlobalKeydown);
});

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

function normalizeCategory(product) {
  const category = String(product.category ?? '').trim();
  if (category) {
    return category;
  }

  const byName = String(product.name ?? '').toLowerCase();
  if (/(cafe|cha|suco|refrigerante|agua|bebida|espresso)/i.test(byName)) {
    return 'Bebida';
  }
  if (/(bolo|croissant|cookie|doce|sobremesa)/i.test(byName)) {
    return 'Doce';
  }
  return 'Comida';
}

function categoryClasses(product) {
  const normalized = normalizeCategory(product).toLowerCase();

  if (normalized.includes('bebida')) {
    return 'border-sky-200 bg-sky-50 text-sky-900 hover:bg-sky-100';
  }
  if (normalized.includes('doce')) {
    return 'border-amber-200 bg-amber-50 text-amber-900 hover:bg-amber-100';
  }
  return 'border-emerald-200 bg-emerald-50 text-emerald-900 hover:bg-emerald-100';
}

function menuLabel(menuId) {
  const labels = {
    vendas: 'Vendas',
    caixa: 'Caixa',
    produtos: 'Produtos',
    relatorios: 'Relatorios',
  };
  return labels[menuId] || 'Modulo';
}

function addToCart(product) {
  playBeep();
  markProductClicked(product.id);

  const existing = cart.value.find((p) => p.id === product.id);
  if (existing) {
    existing.quantity += 1;
    return;
  }

  cart.value.push({
    id: product.id,
    name: product.name,
    price: Number(product.price),
    quantity: 1,
  });
}

function markProductClicked(productId) {
  clickedProductIds.value.add(productId);
  clickedProductIds.value = new Set(clickedProductIds.value);

  setTimeout(() => {
    clickedProductIds.value.delete(productId);
    clickedProductIds.value = new Set(clickedProductIds.value);
  }, 150);
}

function decreaseItem(productId) {
  const existing = cart.value.find((item) => item.id === productId);
  if (!existing) {
    return;
  }

  if (existing.quantity <= 1) {
    cart.value = cart.value.filter((item) => item.id !== productId);
    return;
  }

  existing.quantity -= 1;
}

function onKeypadPress(key) {
  if (key.action === 'digit') {
    appendDigit(key.value);
    return;
  }

  if (key.action === 'clear') {
    cashDigits.value = '0';
    return;
  }

  if (key.action === 'backspace') {
    cashDigits.value = cashDigits.value.length > 1 ? cashDigits.value.slice(0, -1) : '0';
  }
}

function appendDigit(digit) {
  const next = cashDigits.value === '0' ? digit : `${cashDigits.value}${digit}`;
  cashDigits.value = next.slice(0, 8);
}

async function pay() {
  if (!canPay.value) {
    return;
  }

  processing.value = true;
  try {
    await axios.post('/api/v1/orders', {
      items: cart.value.map((item) => ({
        product_id: item.id,
        quantity: item.quantity,
      })),
    });

    alert(`Pedido criado com sucesso. Troco: €${change.value.toFixed(2)}`);
    cart.value = [];
    cashDigits.value = '0';
  } catch (error) {
    console.error(error);
    alert('Erro ao criar pedido');
  } finally {
    processing.value = false;
  }
}

async function fetchCashStatus() {
  try {
    const response = await axios.get('/api/v1/cash/current');
    cashOpen.value = response.data?.data?.status === 'open';
    activeMenu.value = cashOpen.value ? 'vendas' : 'caixa';
  } catch (error) {
    cashOpen.value = false;
    activeMenu.value = 'caixa';
  }
}

async function openCashRegister() {
  if (openingCash.value) {
    return;
  }

  openingCash.value = true;
  try {
    await axios.post('/api/v1/cash/open', {
      initial_balance: 50,
    });

    await fetchCashStatus();
  } catch (error) {
    console.error(error);
    alert('Nao foi possivel abrir o caixa.');
  } finally {
    openingCash.value = false;
  }
}

function handleMenuSelect(menu) {
  if (!cashOpen.value && menu !== 'caixa') {
    alert('Abra o caixa primeiro');
    return;
  }

  activeMenu.value = menu;
}

function handleGlobalKeydown(event) {
  if (event.key === 'Enter') {
    event.preventDefault();
    pay();
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

    osc.type = 'square';
    osc.frequency.value = 1200;
    gain.gain.value = 0.06;

    osc.connect(gain);
    gain.connect(audioContext.value.destination);
    osc.start();
    osc.stop(audioContext.value.currentTime + 0.04);
  } catch (error) {
    console.debug('Beep indisponivel', error);
  }
}
</script>
