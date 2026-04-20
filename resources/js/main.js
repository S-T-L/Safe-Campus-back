/**
 * main.js — Configuration Vue
 *
 * Ce fichier configure l'application Vue :
 * plugins, composants globaux, directives, etc.
 *
 * Il est importé par app.js (bootstrap Inertia).
 * Ne pas y mettre de logique métier.
 */

// ── Plugins ──────────────────────────────────────────
// import router from './router'          // si Vue Router ajouté
// import { createPinia } from 'pinia'    // si Pinia ajouté

// ── Composants globaux ────────────────────────────────
// import AppButton from './Components/AppButton.vue'

/**
 * Configure et retourne l'instance Vue.
 * @param {import('vue').App} app
 * @returns {import('vue').App}
 */
export function setupApp(app) {
    // app.use(createPinia())
    // app.component('AppButton', AppButton)
    return app
}
