import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // اپ جدید سمت کاربر (قالب React)
                'resources/js/newapp/main.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
});
