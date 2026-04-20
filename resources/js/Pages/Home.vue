<script setup>
defineProps({
    appName: String,
})

const conventions = [
    {
        titre: 'Structure d\'un composant .vue',
        regles: [
            '<script setup> en premier — toujours',
            '<template> en second',
            'Pas de <style> dans les .vue — tout le CSS va dans main.css',
            'Une seule responsabilité par composant',
        ],
    },
    {
        titre: 'Organisation des fichiers',
        regles: [
            'Pages/        → une page = une route Inertia (PascalCase)',
            'Components/   → composants réutilisables (préfixe App ou domaine)',
            'Layouts/      → gabarits de page (ex: MainLayout.vue)',
            'Composables/  → logique réutilisable useXxx.js',
        ],
    },
    {
        titre: 'CSS & styles',
        regles: [
            'Classes CSS sémantiques dans les templates',
            'Variables CSS custom dans main.css (:root)',
            'Styles nommés par composant dans main.css (convention BEM)',
            'Jamais de style inline sauf valeurs dynamiques',
        ],
    },
    {
        titre: 'Données & props',
        regles: [
            'defineProps() avec typage explicite',
            'defineEmits() documenté',
            'Données de page → props Inertia (contrôleur Laravel)',
            'État local → ref() ou reactive()',
        ],
    },
    {
        titre: 'Navigation',
        regles: [
            'Utiliser <Link href="..."> d\'Inertia (pas <a>)',
            'router.visit() pour navigation programmatique',
            'router.reload() pour rafraîchir les props sans rechargement',
        ],
    },
]

const stack = ['Laravel 12', 'Inertia v3', 'Vue 3', 'Vite 7', 'PostgreSQL 17']
</script>

<template>
    <div class="home">
        <header class="home__header">
            <h1 class="home__title">{{ appName }}</h1>
            <p class="home__subtitle">Environnement de développement — consignes front</p>
        </header>

        <main class="home__content">
            <div
                v-for="section in conventions"
                :key="section.titre"
                class="convention-card"
            >
                <h2 class="convention-card__title">{{ section.titre }}</h2>
                <ul class="convention-card__list">
                    <li
                        v-for="regle in section.regles"
                        :key="regle"
                        class="convention-card__item"
                    >
                        <span class="convention-card__arrow">→</span>
                        <code>{{ regle }}</code>
                    </li>
                </ul>
            </div>

            <div class="stack-card">
                <h2 class="stack-card__title">Stack</h2>
                <div class="stack-card__badges">
                    <span
                        v-for="tech in stack"
                        :key="tech"
                        class="stack-badge"
                    >
                        {{ tech }}
                    </span>
                </div>
            </div>
        </main>
    </div>
</template>
