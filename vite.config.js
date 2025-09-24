import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { copyFileSync } from 'fs';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        {
            name: 'copy-pdf-worker',
            buildStart() {
                // Copy PDF.js worker file to public directory
                try {
                    copyFileSync(
                        resolve(__dirname, 'node_modules/pdfjs-dist/build/pdf.worker.min.mjs'),
                        resolve(__dirname, 'public/pdf.worker.min.mjs')
                    );
                } catch (error) {
                    console.warn('Could not copy PDF.js worker file:', error.message);
                }
            },
        },
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
