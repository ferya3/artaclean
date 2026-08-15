import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/*
 * Vazirmatn is installed as an npm package and imported from resources/css/app.css,
 * so the typeface is bundled with the build rather than fetched from a font CDN
 * at request time. That keeps the site working behind Iranian network filtering
 * and removes a third-party origin from the critical path.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
