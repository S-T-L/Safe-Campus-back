# Plan de maintenance — Safe Campus (2026–2031)

> Document généré à partir de l'état réel du dépôt au 2026-07-30. Les sections marquées **[HYPOTHÈSE]** ne sont pas dérivables du code : à valider ou corriger avant usage réel.

---

## 1. Inventaire technique

| Composant | Version actuelle | Rôle |
|---|---|---|
| PHP | 8.4 | Runtime backend |
| Laravel | 13 | Framework backend |
| Filament | 3.3 | Panel admin |
| Laravel Sanctum | 4.0 | Auth API |
| PostgreSQL | 17 | Base de données |
| Nuxt | 3.15 | Framework frontend |
| Vue | 3.5 | Librairie UI |
| Node.js | 22 | Runtime frontend |

Sources : `Safe-Campus-back/composer.json`, `Safe-Campus-front/package.json`.

---

## 2. Cycle de vie — calendrier de fin de support (EOL)

| Composant | Fin de support | Statut au 2026-07-30 | Urgence |
|---|---|---|---|
| **Nuxt 3** | **31 juillet 2026** | Expire dans **2 jours** | 🔴 Critique — migration Nuxt 4 à lancer immédiatement |
| Node.js 22 | Avril 2027 | Maintenance LTS (fin des nouvelles fonctionnalités depuis oct. 2026) | 🟠 À planifier sur 2026–2027 |
| Laravel 13 | 17 mars 2028 | Support actif (correctifs bugs jusqu'à Q3 2027, sécurité jusqu'à Q1 2028) | 🟡 Suivre |
| PHP 8.4 | 31 décembre 2028 | Support actif | 🟡 Suivre |
| PostgreSQL 17 | 8 novembre 2029 | Support actif | 🟢 OK |

**Constat majeur** : le composant le plus critique du stack (le framework front) sort de support avant que ce document ne soit lu. Ce n'est pas une projection à 5 ans — c'est une action à traiter cette semaine.

Sources : [endoflife.ai — Nuxt](https://www.herodevs.com/blog-posts/nuxt-3-reaches-end-of-life-on-july-31-2026-what-are-your-options), [endoflife.ai — Node.js](https://dev.to/endoflifeai/nodejs-22-lts-eol-date-support-timeline-and-what-comes-next-30dm), [endoflife.ai — Laravel](https://endoflife.ai/laravel/13), [endoflife.ai — PHP](https://www.herodevs.com/blog-posts/php-end-of-life-dates-support-timeline-for-every-version-2026), [PostgreSQL versioning policy](https://www.instaclustr.com/education/postgresql/postgres-versions-supported-releases-eol-dates-upgrades/).

---

## 3. Roadmap de montée de version (5 ans)

| Période | Action | Déclencheur |
|---|---|---|
| **2026 Q3 (immédiat)** | Migrer Nuxt 3 → Nuxt 4 | Nuxt 3 déjà en EOL |
| 2026 Q4 – 2027 Q1 | Planifier migration Node 22 → Node 24 (LTS suivante) | Fin de maintenance LTS Node 22 en avril 2027 |
| 2027 Q3 | Réévaluer Laravel 13 → version suivante | Fin des correctifs bugs Laravel 13 |
| 2028 | Migrer PHP 8.4 → version suivante avant le 31/12/2028 | EOL PHP 8.4 |
| 2028 Q1 | Vérifier compat Laravel avant l'EOL sécurité (mars 2028) | EOL Laravel 13 |
| 2029 | Migrer PostgreSQL 17 → version suivante avant nov. 2029 | EOL PostgreSQL 17 |
| Continu | Revue de dépendances mineures (composer/npm) — mensuelle recommandée | Aucun outil de veille automatique en place actuellement (pas de Dependabot/Renovate détecté) |

---

## 4. Sécurité

| Point | État constaté | Action recommandée |
|---|---|---|
| Veille CVE / dépendances | Aucun outil automatisé détecté (`.github/` absent des deux repos) | Activer Dependabot ou Renovate sur les deux dépôts GitHub |
| `.env` prod | Checklist existante (`docs/deploiement.md`) : `APP_DEBUG=false`, mot de passe DB fort, `LOG_LEVEL=error` | Ajouter rotation périodique des secrets — non documentée actuellement |
| Utilisateur DB | Doc mentionne "utilisateur dédié sans droits superuser" en prod | À vérifier au moment du déploiement réel |
| Xdebug | Actif en dev, port 9003 | S'assurer qu'il est absent de l'image de prod (non traité par le compose actuel, qui est dev-only) |

---

## 5. Sauvegarde et restauration — **[HYPOTHÈSE]**

Aucune stratégie de sauvegarde n'existe dans le dépôt (le volume `scback-pgsql` est local, non répliqué). À définir avant toute mise en production :

- Fréquence de sauvegarde (ex. : `pg_dump` quotidien)
- Rétention (ex. : 30 jours glissants)
- Test de restauration périodique (ex. : trimestriel)
- Emplacement de stockage hors du serveur applicatif

---

## 6. Environnement de production — **[HYPOTHÈSE]**

Le stack actuel (`docker-compose.yml`) est explicitement documenté comme dev-only (`artisan serve`, bind mount, Xdebug). Aucun environnement de prod réel n'existe à ce jour.

Sizing indicatif déjà présent dans `docs/infra.md` (mesuré en dev, à confirmer en charge réelle) :

| Profil | Specs | Usage |
|---|---|---|
| Petit | 1 vCPU / 1 GB RAM / 20 GB SSD | < 50 utilisateurs simultanés |
| Moyen | 2 vCPU / 2 GB RAM / 40 GB SSD | 50–500 utilisateurs |
| Séparation DB | App 2 vCPU/2GB + DB 2 vCPU/4GB | Charge variable |

À trancher : hébergeur cible, séparation des environnements (staging/prod), procédure de déploiement (actuellement inexistante — pas de CI/CD détecté).

---

## 7. Supervision — **[HYPOTHÈSE]**

Rien en place. À définir : outil de monitoring (uptime, erreurs applicatives), seuils d'alerte, rétention des logs (`storage/logs/laravel.log` en dev, pas de solution centralisée constatée).

---

## 8. Ressources humaines — **[HYPOTHÈSE]**

Projet académique (SAE501), deux dépôts GitHub (`S-T-L/Safe-Campus-back`, `S-T-L/Safe-Campus-front`), workflow git flow actif. Aucun porteur de maintenance post-projet identifié dans le dépôt. Un plan à 5 ans suppose une réponse à : qui maintient après la fin de la SAE, avec quel budget/temps alloué.

---

## 9. Risques identifiés

| Risque | Impact | Probabilité |
|---|---|---|
| Nuxt 3 déjà EOL sans migration planifiée | Faille de sécurité non corrigée sur le front | Élevé — immédiat |
| Absence de CI/CD | Déploiements manuels, erreurs humaines | Moyen |
| Absence de sauvegarde définie | Perte de données en cas d'incident | Élevé si mise en prod sans correction |
| `routes/api.php` absent | Aucune intégration front/back fonctionnelle à ce jour | Bloquant pour toute mise en prod |

---

## Sections à compléter (bloquantes pour un plan définitif)

1. Hébergement cible et budget associé
2. Porteur de la maintenance post-SAE
3. SLA / criticité attendue
4. Politique de sauvegarde validée
5. Mise en place CI/CD et monitoring
