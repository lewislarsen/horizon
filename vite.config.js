import vue from '@vitejs/plugin-vue';
import tailwindcss from "@tailwindcss/vite";

/** @type {import('vite').UserConfig} */
export default {
    plugins: [vue(), tailwindcss()],
    build: {
        outDir: 'dist',
        assetsDir: '',
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                tailwind: 'resources/css/app.css',
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
};