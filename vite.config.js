import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [laravel(['resources/js/app.js']), vue()],
  server: { host: '0.0.0.0', port: 5173, strictPort: true, hmr: { host: 'localhost' } },
});
