# Inventaire des textes du front

Recensement de **tout** le texte affiché par [Safe-Campus-front](../../Safe-Campus-front), avec sa
correspondance vers le schéma de base — voir [schema_bd.md](schema_bd.md).

Objectif : identifier ce qui doit passer en base (contenu éditorial, modifiable par un rédacteur via
Filament) et ce qui reste codé en dur côté front (libellés d'interface).

> Établi le 2026-08-23 sur `Safe-Campus-front@dev`. Source unique du contenu :
> `app/data/themes.js` (509 lignes).

---

## 1. Ce qui reste au front

Libellés d'interface, non éditoriaux. Ils ne décrivent aucune donnée métier : les mettre en base
créerait une table de traduction sans rédacteur pour la maintenir.

| Fichier | Texte |
|---|---|
| `pages/index.vue` | « À propos », « C'est quoi ? », le paragraphe de présentation Safe Campus, « © 2025 Université de Nouvelle-Calédonie » |
| `components/SearchBar.vue` | « Cherche un sujet, une ressource... », « Thèmes », « Ressources », « Contacts », « Aucun résultat pour … » |
| `components/NavDrawer.vue` | « MENU », « Retour », « Thématiques », « Informations », « À propos de Safe Campus », « Voir toute la section », « Contact », « Ressources », « Safe Campus · UNC 2025 » |
| `components/CardItem.vue` | « Contact », « Ressources » |
| `components/ContactCard.vue` | « Le plus proche », « Copier » / « Copié », « Numéro vert » |
| `pages/contact/[theme]/[slug].vue` | « Retour », « Suivre l'histoire », « Où trouver de l'aide près de chez vous », « Me géolocaliser » / « Recherche… », les 3 messages d'erreur de géolocalisation, « Contact » |
| `pages/ressources/[theme]/[slug].vue` | « Retour », « Fiches réflectives », « Télécharger », « Bientôt disponible » |

**Exception assumée** : le paragraphe « C'est quoi ? » de l'accueil est du contenu éditorial. Il n'a
pas d'entité qui le porte (ce n'est ni un thème ni un sous-thème). Laissé au front tant qu'aucune
table `page` n'existe. Voir [Reste à trancher](#5-reste-à-trancher).

---

## 2. Ce qui doit passer en base

### 2.1 Thème — 3 lignes

| Champ front | Exemple | Colonne | État |
|---|---|---|---|
| `id` | `addiction` | `themes.ref` | **Écart** : la base porte `addictions`, `sante_mentale`, `vss`. Le front doit consommer le `ref` de l'API, pas un id codé en dur. |
| `label` | Conduites addictives | `themes.libelle` | Existe. Requalifié en libellé **long**. |
| `shortLabel` | Addictions | — | **Manquant** → `themes.libelle_court` |
| `navLabel` | ADDICTIONS | — | **Champ mort** : zéro usage dans le front. Supprimé. |
| `color` | `#4260e6` | — | **Front.** Charte graphique, non éditable depuis le back. |
| `colorVar` | `--color-addiction` | — | **Champ mort** : zéro usage. Supprimé. |
| *(position dans le tableau)* | 1, 2, 3 | `themes.ordre` | Trie en base (`->ordonne()`), **pas exposé en JSON** — le tableau arrive déjà trié. |
| *(préfixe du sous-titre)* | COMPORTEMENT ADDICTIF | — | **Front.** Gabarit d'affichage. |

**Contrat de tri.** `GET /api/themes` renvoie `data[]` trié par `themes.ordre`, et chaque
`sous_themes[]` imbriqué trié par `sous_themes.ordre` — même mécanisme pour `contacts[]`
(`contact_sous_theme.ordre`) et `documents[]` (`media_sous_theme.ordre`) sur la fiche. Le front
**itère, il ne trie pas** : `v-for` sur le tableau tel quel, aucune valeur `ordre` transmise.
Garanti par `AnnuaireApiTest::test_get_themes_respecte_l_ordre_editorial` et
`test_get_themes_ordonne_les_sous_themes_dans_le_theme`.

`libelle` et `libelle_court` sont bien deux textes distincts, affichés à des endroits différents :
`libelle_court` dans la nav, les titres de section, les tags et la BottomTabBar (5 usages) ;
`libelle` dans le NavDrawer et l'index de recherche (3 usages).

Le préfixe de sous-titre ne se déduit d'aucun libellé (`vss` → « VIOLENCE » au singulier, là où
`libelle_court` est au pluriel) mais il n'apparaît que dans le drawer et la recherche, jamais sur
les cartes. Une constante de 3 lignes au front, à côté du mapping des illustrations. L'ordinal
(« N°1 », « N°2 ») se calcule sur la position dans `themes[].sous_themes[]` — déjà trié par
`sous_themes.ordre` côté API — et non sur une valeur `ordre` renvoyée par l'API.

### 2.2 Sous-thème — 9 lignes

| Champ front | Exemple | Colonne | État |
|---|---|---|---|
| `id` | 1, 2, 3 | `sous_themes.ordre` | Trie en base. Sans lui, `hasMany` rend un ordre arbitraire en PostgreSQL. **Pas exposé en JSON**, voir 2.1. |
| `slug` | `alcool` | `sous_themes.ref` | Existe. Deux écarts : `drogues`/`drogue`, `burn-out`/`burn_out`. Le front construit ses URL avec le `ref` de l'API. |
| `ninja` | `/assets/ninja_alcool.png` | — | **Front.** Asset statique, ~1 Mo pièce. |
| `title` | Alcool | `sous_themes.libelle` | Existe |
| `hook` | « Tu as dit que c'était ton dernier verre hier. » | `sous_themes.resume` | Existe |
| `subtitle` | COMPORTEMENT ADDICTIF N°1 | — | Composé au front : `prefixe_sous_titre` + « N° » + `ordre` |
| `description` | ~600 signes, hero de la page contact | `sous_themes.article` | Existe |
| `resources.intro` | ~350 signes, hero de la page ressources | — | **Manquant** → `sous_themes.intro_ressources` |
| `resources.documents[]` | 2 par sous-thème, `title` + `description` | `medias` (type `document`) + `media_sous_theme` | Tables existantes. `libelle` porte le titre. **Manquent** `medias.description` et `media_sous_theme.ordre`. |

**Pas de table dédiée aux documents.** `medias` stocke déjà `libelle`, `chemin` et `type`, et le
pivot `media_sous_theme` fait la liaison. Deux colonnes suffisent : une `description` (le front
affiche un titre **et** un texte) et un `ordre` sur le pivot (même rôle que
`contact_sous_theme.ordre`). Le fichier lui-même est `medias.chemin` — nul aujourd'hui, aucun PDF
n'existe, le bouton de téléchargement du front est désactivé.

**Les illustrations restent des assets du front.** Un mapping `ref → chemin` de 9 lignes côté Nuxt,
servi depuis `public/assets/`. Aucune colonne, aucune FK : ces images ne sont pas éditables depuis
le back.

### 2.3 Contact

| Champ front | Exemple | Colonne | État |
|---|---|---|---|
| `name` | CSAPA — Nouméa | `contacts.nom` | Existe |
| `role` | « Consultations en addictologie, accueil libre et anonyme » | `contacts.remarques` | Existe |
| `phone` | +687 28 12 34 | `telephones.numero` | Existe |
| `tollFree` | `false` | `telephones.numero_vert` | Existe |
| `email` | `csapa@example.com` | `contacts.mail` | Existe |
| `hours` | Lun–Ven · 8h–16h30 | `contacts.horaires` | Existe |
| `address` | 12 Rue du Général Mangin, Nouméa | `contacts.localisation` | Existe |
| `lat` / `lng` | -22.2758 / 166.4580 | — | **Manquant** → `contacts.latitude` / `contacts.longitude` |

`lat`/`lng` tranche le [point 2](schema_bd.md#points-à-trancher) : deux colonnes stockées, pas de
pipeline de géocodage.

---

## 3. Les contacts du front sont fictifs

**Ils ne sont pas repris en base.**

| | Front (`themes.js`) | Base (`ContactSeeder`) |
|---|---|---|
| Nombre | 13 structures | 38 structures |
| Téléphones | `+687 28 12 34`, `+687 25 66 66`… inventés | `15`, `17`, `18`, `3114`, `27 76 08`… réels |
| Mails | `csapa@example.com` | `secretariat.csa@chs.nc`, `accueil@adavi.nc` |
| Structures | « DAFE », « Référente Égalité », « Service Médiation », « CAARUD NC » — non vérifiées | ADAVI, CSAPA, CHS Albert Bousquet, MCPF, DECLIC, CIDFE — issues de l'annuaire source |

Injecter les contacts du front en base placerait de faux numéros à côté du SAMU et du 3114 dans une
application d'aide au suicide et aux violences. `ContactSeeder` fait autorité. Le seul contenu du
front repris est **éditorial** : accroches, descriptions, intros ressources, documents,
illustrations, couleurs.

Conséquence directe : `latitude`/`longitude` sont ajoutées mais restent **vides**. La carte Leaflet
de la page contact ne s'affichera pas tant que les 38 adresses réelles ne sont pas géocodées.
Le front masque déjà la section quand aucun contact n'est localisé (`v-if="hasLocated"`).

---

## 4. Audit des routes API

Existant : `GET /api/themes`, `GET /api/sous-themes/{ref}`.

**Aucune route ajoutée.** Les deux existantes suffisent, leurs payloads sont complétés.

| Page front | Endpoint | Ce qui manquait |
|---|---|---|
| `/` accueil | `GET /api/themes` | `libelle_court` et `ordre` sur le thème, `ordre` sur les cartes |
| `/contact/{theme}/{slug}` | `GET /api/sous-themes/{ref}` | le thème parent (tag), `latitude`/`longitude` sur les contacts |
| `/ressources/{theme}/{slug}` | `GET /api/sous-themes/{ref}` | `intro_ressources` et `documents` |
| `SearchBar` | `GET /api/themes` | rien — l'index se reconstruit sur thèmes + sous-thèmes |

**Une seule route pour les deux pages de fiche.** Les pages contact et ressources partagent le même
hero (tag du thème, titre, illustration). Un second endpoint aurait dupliqué ce bloc pour économiser
deux champs texte. `GET /api/sous-themes/{ref}` renvoie la fiche entière.

**La recherche perd les contacts.** L'index de `SearchBar` couvrait les 13 contacts fictifs de
`themes.js`. Les 38 contacts réels ne sont pas dans le payload de l'accueil et il serait absurde de
les y charger. La recherche porte désormais sur les thèmes et les sous-thèmes. Rétablir la recherche
de contacts demandera un endpoint dédié — hors périmètre ici.

---

## 5. Reste à trancher

| # | Question |
|---|---|
| 1 | Géocodage des 38 contacts réels — sans quoi la carte reste vide. |
| 2 | Le paragraphe « C'est quoi ? » de l'accueil : le laisser au front ou créer une entité `page` éditable ? |
| 3 | Les fichiers PDF des 18 fiches réflectives n'existent pas. `medias.chemin` reste vide, le bouton de téléchargement reste désactivé. |
| 4 | Rétablir la recherche de contacts, si le besoin est confirmé. |

---

## 6. Bilan des changements de schéma

Aucune table, aucun modèle. Six colonnes sur cinq tables existantes.

| Table | Colonne | Rôle |
|---|---|---|
| `themes` | `libelle_court` | « Addictions » quand `libelle` vaut « Conduites addictives » |
| `themes` | `ordre` | ordre des sections de l'accueil |
| `sous_themes` | `ordre` | ordre des cartes, porte la numérotation affichée |
| `sous_themes` | `intro_ressources` | chapeau de la page ressources |
| `medias` | `description` | texte des fiches réflectives |
| `media_sous_theme` | `ordre` | ordre des fiches réflectives |
| `contacts` | `latitude` / `longitude` | carte Leaflet |
