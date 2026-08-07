import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta?.content) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
}

window.axios = axios;
