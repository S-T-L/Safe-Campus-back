/**
 * app.js — Bootstrap Inertia
 *
 * Point d'entrée de l'application.
 * Initialise Inertia + Vue et délègue la config à main.js.
 */

import './bootstrap'
import '../css/main.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { setupApp } from './main.js'

createInertiaApp({
    title: (title) => title ? `${title} — ${import.meta.env.VITE_APP_NAME}` : import.meta.env.VITE_APP_NAME,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return setupApp(
            createApp({ render: () => h(App, props) }).use(plugin)
        ).mount(el)
    },
})
