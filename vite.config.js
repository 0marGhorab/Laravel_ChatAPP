import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST;

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/profile-photo-cropper.js',
                ],
                refresh: true,
            }),
        ],
        server: hmrHost
            ? {
                host: true,
                hmr: {
                    host: hmrHost,
                },
            }
            : {
                host: '127.0.0.1',
                port: 5173,
                strictPort: true,
            },
    };
});
