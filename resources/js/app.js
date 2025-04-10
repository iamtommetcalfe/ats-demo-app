// resources/js/app.js
import '../css/app.css';
import { createApp } from 'vue';
import axios from 'axios';

// Set up Axios with CSRF token for Laravel
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Import Vue components
import ApplicantsList from './components/ApplicantsList.vue';
import ApplicantDetail from './components/ApplicantDetail.vue';

// Create Vue application and register components
const app = createApp({});
app.component('applicants-list', ApplicantsList);
app.component('applicant-detail', ApplicantDetail);
app.mount('#app');
