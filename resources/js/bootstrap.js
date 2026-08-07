import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta?.content) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
}

window.axios = axios;
