# Guide développement front-end

Stack : **Vue 3** · **Inertia v3** · **Vite 7** · **Laravel 12**

---

## Philosophie

- JS + HTML dans le fichier `.vue` (`<script setup>` + `<template>`)
- **Pas de `<style>` dans les `.vue`** — tout le CSS est dans `resources/css/main.css`
- Convention de nommage CSS : **BEM** (`.bloc__element--modificateur`)
- Variables CSS dans `:root` pour toutes les valeurs partagées

---

## Structure d'un composant

```vue
<script setup>
// 1. Imports
// 2. Props & emits
// 3. État local (ref/reactive)
// 4. Computed
// 5. Fonctions
</script>

<template>
  <!-- Une seule racine recommandée -->
</template>

<!-- Pas de <style> -->
```

---

## Organisation des fichiers

```
resources/js/
├── app.js              Bootstrap Inertia
├── main.js             Setup Vue (plugins, composants globaux)
├── Pages/              Une page = une route Inertia (PascalCase)
│   └── Home.vue
├── Components/         Composants réutilisables
│   └── App/            Composants génériques préfixés App
├── Layouts/            Gabarits de page
│   └── MainLayout.vue
└── Composables/        Logique réutilisable
    └── useXxx.js

resources/css/
├── app.css             Point d'entrée CSS
└── main.css            Tous les styles (variables + composants)
```

---

## Règles CSS

```css
/* ✅ BEM dans main.css */
.ma-page { }
.ma-page__section { }
.ma-page__titre--grand { }

/* ✅ Variables CSS */
color: var(--color-primary);
padding: var(--spacing-md);

/* ❌ Jamais */
/* <style> dans un .vue */
/* style="color: red" (sauf valeur dynamique) */
```

---

## Props & données

```vue
<script setup>
// Props typées
const props = defineProps({
    titre: String,
    items: Array,
    visible: {
        type: Boolean,
        default: true,
    },
})

// Emits documentés
const emit = defineEmits(['update:visible', 'submit'])

// État local
const compteur = ref(0)

// Computed
const total = computed(() => props.items.length)
</script>
```

---

## Navigation Inertia

```vue
<script setup>
import { Link, router } from '@inertiajs/vue3'
</script>

<template>
    <!-- Navigation déclarative -->
    <Link href="/histoires">Histoires</Link>

    <!-- Navigation programmatique -->
    <button @click="router.visit('/histoires')">Go</button>

    <!-- Refresh des props sans rechargement -->
    <button @click="router.reload()">Actualiser</button>
</template>
```

---

## Flux de données

```
Laravel Controller
    └── Inertia::render('MaPage', ['data' => $data])
            └── MaPage.vue reçoit data en prop
                    └── Pas de fetch() / axios pour les données de page
                        (réserver axios aux actions POST/PATCH/DELETE)
```

---

## Variables CSS disponibles

| Variable | Valeur |
|---|---|
| `--color-primary` | `#4f46e5` |
| `--color-secondary` | `#7c3aed` |
| `--color-bg` | `#ffffff` |
| `--color-surface` | `#f9fafb` |
| `--color-text` | `#111827` |
| `--color-muted` | `#6b7280` |
| `--color-border` | `#e5e7eb` |
| `--spacing-sm` | `0.5rem` |
| `--spacing-md` | `1rem` |
| `--spacing-lg` | `1.5rem` |
| `--spacing-xl` | `2rem` |
| `--radius-md` | `0.5rem` |
| `--radius-lg` | `0.75rem` |
| `--shadow-sm` | `0 1px 2px …` |
| `--shadow-md` | `0 4px 6px …` |
