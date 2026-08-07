import '../bootstrap';
import { createApp } from 'vue';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import PosPage from './pages/PosPage.vue';

const app = createApp(PosPage);
app.component('Toaster', Toaster);
app.mount('#app');
