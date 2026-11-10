import { createApp } from 'vue';
import App from './App.vue';
import '../css/app.css';

const root = document.getElementById('app');
createApp(App, {
  page: root.dataset.page,
  payload: JSON.parse(root.dataset.payload),
  user: JSON.parse(root.dataset.user),
  errors: JSON.parse(root.dataset.errors),
  flash: root.dataset.flash,
}).mount(root);
