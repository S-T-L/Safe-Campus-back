# Schéma de Base de Données — Safe Campus

## Sprint 1 — Thèmes, Contacts, Signalements & Admin

```mermaid
erDiagram
    Theme {
        int    Theme_id  PK
        string Nom
    }

    SousTheme {
        int     SousTheme_id        PK
        string  Nom
        int     FK_Theme            FK
        boolean Permet_signalement
    }

    SousTheme_Fiche {
        int    Fiche_id     PK
        string Nom
        text   Article
        int    FK_SousTheme FK
    }

    Contact {
        int    Contact_id   PK
        string Nom
        string Prenom
        string Mail
        string Localisation
    }

    Telephone {
        int    Tel_id     PK
        string Numero
        enum   Type
        int    FK_Contact FK
    }

    Media {
        int    Media_id PK
        string Libelle
        string Chemin
        enum   Type
    }

    User {
        int    User_id  PK
        string User_log
        string User_mpd
    }

    Signalement {
        int      Signalement_id PK
        string   Token_antispam
        int      FK_SousTheme   FK
        string   Texte
        datetime Date_heure
    }

    Liaison_Fiche_Contact {
        int FK_SousTheme_Fiche FK
        int FK_Contact         FK
    }

    Liaison_Fiche_Media {
        int FK_SousTheme_Fiche FK
        int FK_Media           FK
    }

    Theme           ||--o{ SousTheme            : "contient"
    SousTheme       ||--o{ SousTheme_Fiche       : "documente"
    SousTheme       ||--o{ Signalement           : "categorise (si Permet_signalement)"
    Contact         ||--o{ Telephone             : "possede"
    Contact         ||--o{ Liaison_Fiche_Contact : ""
    SousTheme_Fiche ||--o{ Liaison_Fiche_Contact : ""
    SousTheme_Fiche ||--o{ Liaison_Fiche_Media   : ""
    Media           ||--o{ Liaison_Fiche_Media   : ""
```

### Infrastructure auth — Sanctum & Filament Shield / Spatie (tables auto-générées)

```mermaid
erDiagram
    User {
        int    User_id  PK
        string User_log
        string User_mpd
    }

    personal_access_tokens {
        int      id             PK
        string   tokenable_type
        int      tokenable_id   FK
        string   name
        string   token
        string   abilities
        datetime last_used_at
        datetime expires_at
    }

    roles {
        int    id         PK
        string name
        string guard_name
    }

    permissions {
        int    id         PK
        string name
        string guard_name
    }

    model_has_roles {
        int    role_id    FK
        string model_type
        int    model_id   FK
    }

    role_has_permissions {
        int permission_id FK
        int role_id       FK
    }

    User        ||--o{ personal_access_tokens : "tokens Sanctum (Nuxt)"
    User        ||--o{ model_has_roles        : ""
    roles       ||--o{ model_has_roles        : ""
    roles       ||--o{ role_has_permissions   : ""
    permissions ||--o{ role_has_permissions   : ""
```

---

## Sprint 2 — Histoires & Scènes

> `Theme`, `SousTheme`, `User` et `Media` sont définis dans le Sprint 1.

```mermaid
erDiagram
    SousTheme {
        int     SousTheme_id        PK
        string  Nom
        int     FK_Theme            FK
        boolean Permet_signalement
    }

    User {
        int    User_id  PK
        string User_log
        string User_mpd
    }

    Media {
        int    Media_id PK
        string Libelle
        string Chemin
        enum   Type
    }

    Histoire {
        int  Histoire_id  PK
        enum Etat
        int  FK_SousTheme FK
        int  FK_User      FK
    }

    Scene {
        int    Scene_id      PK
        int    FK_SousTheme  FK
        int    FK_Media      FK
        string Dialogue_text
        int    FK_Histoire   FK
    }

    Choix {
        int    Choix_id      PK
        int    FK_Scene      FK
        int    FK_Next_Scene FK
        string Text_Choix
    }

    User      ||--o{ Histoire : "cree"
    SousTheme ||--o{ Histoire : "theme principal"
    Histoire  ||--o{ Scene    : "contient"
    SousTheme ||--o{ Scene    : "theme de la scene"
    Media     ||--o{ Scene    : "illustre"
    Scene     ||--o{ Choix    : "propose (FK_Scene)"
    Scene     ||--o{ Choix    : "cible (FK_Next_Scene)"
```

---

## Contrôle d'accès — Filament Shield

Un seul panel `/admin`. Filament Shield (plugin Spatie × Filament) génère automatiquement une permission par action et par ressource :

```
view_contact   create_contact   update_contact   delete_contact
view_histoire  create_histoire  update_histoire  delete_histoire
...
```

Deux rôles métier prévus :

| Ressource Filament | `webmaster` | `redacteur` |
|---|---|---|
| Thèmes / Sous-thèmes | ✓ | ✗ |
| Contacts / Téléphones | ✓ | ✗ |
| Médias | ✓ | ✓ |
| Signalements | ✓ | ✗ |
| Histoires / Scènes / Choix | ✗ | ✓ |

Un rédacteur qui tente `/admin/contacts` reçoit un 403. Le menu Filament masque automatiquement les ressources inaccessibles.

Même table `User` pour les deux rôles. L'authentification Filament passe par une session Laravel classique — Sanctum n'est pas impliqué pour `/admin`.

---

## Notes techniques

- **Telephone.Type** : enum `mobile`, `fixe`, `sms`, `urgence`.
- **Media.Type** : enum `image`, `video`, `audio`, `document`.
- **Histoire.Etat** : enum `brouillon`, `relecture`, `validé`, `publié`.
- **Signalement anonyme** : `Token_antispam` est un token libre (fingerprint, cookie, IP hashée) pour limiter le spam. Aucune FK vers `User`.
- **Sous-thèmes éligibles** : seuls les `SousTheme` avec `Permet_signalement = true` exposent le formulaire.
- **Bifurcation thématique** : `Scene.FK_SousTheme` peut différer de `Histoire.FK_SousTheme`, permettant à un choix de basculer le récit vers une autre thématique (ex. alcool → cannabis).
- **Choix** : navigation orientée vers l'avant uniquement. `FK_Prev_Scene` supprimé — le précédent se retrouve par le graphe des choix entrants.
- **Timestamps** (`created_at`, `updated_at`) : gérés automatiquement par les migrations Laravel.
- **Sanctum** : `personal_access_tokens` est publié via `php artisan vendor:publish`. Ne pas modifier manuellement.
- **Filament Shield** : permissions générées via `php artisan shield:generate`. Les tables Spatie (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`) sont migrées par le package.
