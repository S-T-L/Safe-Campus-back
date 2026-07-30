# Cahier des charges techniques — Safe Campus

> Projet académique (SAE501). Le périmètre fonctionnel a été fourni par la SAE ; **le périmètre technique a été laissé libre à l'équipe**. Ce document formalise a posteriori les choix techniques retenus et leur justification — à utiliser en support de présentation/soutenance.

---

## 1. Contexte

Safe Campus est une plateforme d'information et de prévention destinée aux étudiants d'un campus. Elle centralise :

- des contenus éditoriaux organisés par **thèmes / sous-thèmes** (ex. : santé, sécurité, harcèlement),
- un **annuaire de contacts** (associations, services) avec coordonnées, horaires, numéros de téléphone (dont numéros verts/gratuits),
- un mécanisme de **signalement** rattaché à un sous-thème,
- des **médias** (texte, image, vidéo, logo) associés aux thèmes et sous-thèmes.

Démarrage du dépôt back : 20/04/2026. État au 30/07/2026 : ~14 semaines de développement, 30 commits sur `Safe-Campus-back`.

---

## 2. Objectifs techniques

| Objectif | Traduction technique |
|---|---|
| Séparer contenu et présentation | Back = API JSON + admin, Front = consommateur pur, aucun couplage de vues |
| Permettre une gestion de contenu autonome | Panel d'administration sans développement custom (CRUD, formulaires, relations) |
| Fournir une expérience mobile-first | Front avec navigation par onglets (`BottomTabBar`), consultable en conditions de mobilité |
| Garantir la reproductibilité de l'environnement | Environnement 100 % conteneurisé, aucune dépendance installée sur l'hôte |
| Poser une base de données relationnelle cohérente | Modélisation normalisée (thèmes → sous-thèmes → contacts/médias/signalements) |

---

## 3. Périmètre fonctionnel couvert par le back

| Domaine | État |
|---|---|
| Thèmes / sous-thèmes (lecture publique) | Implémenté — `GET /themes`, `GET /sous-themes/{ref}` |
| Annuaire de contacts + téléphones | Modélisé (`Contact`, `Telephone`), pas encore exposé en API publique |
| Médias liés aux thèmes/sous-thèmes | Modélisé (relations many-to-many) |
| Signalement | Modélisé (`Signalement`) — **routage et cycle de vie non tranchés** (commentaire explicite dans le code : "module reporté") |
| Authentification admin | Sanctum + rôles (`Webmaster`, `Redacteur`) via Filament |
| Authentification front public | Aucune — les routes annuaire sont publiques par design |

---

## 4. Architecture générale

Deux dépôts séparés, orchestrés ensemble en développement, déployables indépendamment :

```
Safe-Campus-back/     API JSON + panel admin Filament (Laravel 13, PHP 8.4)
Safe-Campus-front/    Application consommatrice (Nuxt 3, Vue 3)
```

- Communication back ↔ front : HTTP/JSON, `NUXT_PUBLIC_API_BASE` pointe vers l'API Laravel.
- L'admin (Filament) est autonome : il ne dépend d'aucun asset du front.
- Base de données unique (PostgreSQL), accédée uniquement par le back.

Ce découpage a un coût assumé : `routes/api.php` ne couvre pour l'instant que l'annuaire en lecture, l'intégration réelle front/back reste partielle.

---

## 5. Choix technologiques et justification

| Composant | Choix retenu | Justification |
|---|---|---|
| Backend | Laravel 13 (PHP 8.4) | Écosystème mature, ORM Eloquent adapté à un modèle relationnel à plusieurs niveaux de relation (thème → sous-thème → contact/média), conventions fortes qui réduisent le temps de setup sur un projet à durée fixe (un semestre) |
| Admin | Filament 3 | Panel CRUD généré à partir des modèles Eloquent — évite de développer une interface d'administration dédiée, alloue le temps de dev à la logique métier plutôt qu'à du CRUD répétitif |
| Base de données | PostgreSQL 17 | Support natif JSONB (utilisé pour les colonnes flexibles), CTE récursives disponibles si le graphe de contenu se complexifie, plus rigoureux que MySQL sur les contraintes (utile pour un modèle avec beaucoup de clés étrangères) |
| Auth API | Laravel Sanctum | Auth légère par token, suffisante pour un panel admin + API interne — pas de besoin OAuth2 identifié |
| Frontend | Nuxt 3 / Vue 3 | SSR/SPA hybride, structure par fichiers (`pages/`) qui correspond au découpage par thème de l'app, écosystème Vue déjà maîtrisé par l'équipe |
| Cartographie | Leaflet | Librairie légère, sans dépendance à une clé API commerciale (contrainte réaliste pour un projet étudiant sans budget) |
| Conteneurisation | Docker Compose | Parité stricte entre postes de dev (Windows/WSL, macOS, Linux) — élimine la classe de bugs "ça marche chez moi", critique en travail de groupe |
| Environnement hôte | WSL2 + Docker Desktop | Contrainte du parc de postes de l'équipe (majoritairement Windows) |

---

## 6. Modèle de données

Détail complet : [`schema_bd.md`](schema_bd.md). Entités principales :

```
Theme 1─N SousTheme
SousTheme N─N Contact   (pivot ordonné : "ordre")
SousTheme N─N Media
Theme    N─N Media
SousTheme 1─N Signalement
Contact  1─N Telephone
```

Points de modélisation notables :
- `SousTheme` porte à la fois le teaser (`resume`) et le contenu long (`article`) — pas de table "fiche" séparée, choix assumé pour éviter une jointure supplémentaire sur le parcours de lecture le plus fréquent.
- `permet_signalement` (booléen sur `SousTheme`) active/désactive le signalement par sous-thème plutôt que par une table de configuration séparée.
- `Contact::scopeActif()` permet de dépublier un contact sans le supprimer (traçabilité).

---

## 7. Sécurité applicative

| Mesure | Détail |
|---|---|
| Séparation auth admin / accès public | `canAccessPanel()` refuse l'accès Filament à tout utilisateur sans rôle (`role === null`), même authentifié |
| Rôles | Deux rôles distincts (`Webmaster`, `Redacteur`) — préparation à une gestion fine des droits, non détaillée à ce stade (pas de policy par rôle constatée dans le code) |
| Anti-spam signalement | `token_antispam` sur `Signalement` — mécanisme prévu, logique de vérification non implémentée |
| Validation | Form Requests dédiées pour toute validation (convention d'équipe, pas de validation inline en contrôleur) |
| Mots de passe | Hashés (`'password' => 'hashed'`), jamais sérialisés (`$hidden`) |

---

## 8. Environnement de développement et outillage

| Outil | Rôle |
|---|---|
| Docker Compose | Orchestration des 4 services : `sc_back`, `sc_front`, `pgsql`, `adminer` |
| Xdebug | Débogage pas-à-pas, actif en dev uniquement (port 9003) |
| Adminer | Inspection manuelle de la base en dev |
| Larastan (PHPStan) | Analyse statique backend |
| Laravel Pint | Formatage de code PHP |
| ESLint (`@nuxt/eslint`) | Lint frontend |
| PHPUnit | Tests backend (`phpunit.xml` configuré, `.phpunit.result.cache` présent) |

Absent à ce jour : CI/CD (aucun pipeline détecté), tests frontend, CI de lint automatisée sur push.

---

## 9. Contraintes non fonctionnelles (objectifs fixés par l'équipe)

> Cette section fixe des cibles, pas des mesures. Aucune charge réelle n'a encore été testée.

| Axe | Cible |
|---|---|
| Compatibilité navigateur | Navigateurs mobiles modernes (dernières versions Chrome/Safari mobile) — cohérent avec l'usage mobile-first visé |
| Accessibilité | Non spécifiée formellement — à définir si le projet est poursuivi au-delà de la SAE |
| Performance | Pas de budget de performance chiffré à ce jour |
| Disponibilité | Sans objet en développement — aucun environnement de production n'existe |

---

## 10. Méthodologie et gestion de version

- Git flow : `feature/*` pour le développement, fusion sur `dev` en `--no-ff`, `hotfix/*` depuis `main`.
- Toute fusion `feature/* → dev` validée explicitement, jamais automatique.
- Convention de commit imposée (`type(scope): résumé` + liste de sous-points).
- Deux dépôts synchronisés par convention de nommage et de structure de dossier (`../Safe-Campus-front` en chemin relatif dans le compose du back).

---

## 11. Livrables

- Code source des deux dépôts (`Safe-Campus-back`, `Safe-Campus-front`)
- Documentation technique (`docs/` : infra, déploiement, schéma BDD, conventions back/front)
- Environnement de développement conteneurisé, démarrage en une commande (`docker compose up -d`)
- [`plan-maintenance.md`](plan-maintenance.md) — trajectoire de maintenance à 5 ans

---

## 12. Limites connues du périmètre actuel

| Limite | Impact |
|---|---|
| `routes/api.php` réduit à l'annuaire en lecture | Pas d'intégration front/back complète à ce jour |
| Signalement non finalisé (routage, état) | Fonctionnalité modélisée mais non livrable en l'état |
| Aucun environnement de production | Le compose actuel est explicitement dev-only (`artisan serve`, bind mount, Xdebug actif) |
| Aucune CI/CD | Qualité du code dépendante de la discipline manuelle de l'équipe |
