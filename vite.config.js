import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { symfonyPlugin } from '@symfony/ux-vite-plugin';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
        vue(), // Make sure this line exists
    ],
});
