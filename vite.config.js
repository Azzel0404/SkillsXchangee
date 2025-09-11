import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/css/app.css',
                js: 'resources/js/app.js',
            },
        },
    },
    define: {
        'process.env.MIX_PUSHER_APP_KEY': JSON.stringify(process.env.VITE_PUSHER_APP_KEY),
        'process.env.MIX_PUSHER_APP_CLUSTER': JSON.stringify(process.env.VITE_PUSHER_APP_CLUSTER),
    },
});
