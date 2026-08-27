# Déploiement en production

> `docker-compose.yml` (racine) est un stack de développement : `artisan serve`, bind mount du code source, Adminer exposé. Il n'est pas destiné à la production.

## Stack Docker prod

`docker-compose.prod.yml` — buildé par Dockploy. Différences avec le stack dev :

- Code copié dans l'image au build (`docker/8.4/Dockerfile.prod`), pas de bind mount.
- `composer install --no-dev --optimize-autoloader --no-scripts` au build (pas à chaque démarrage).
- Pas d'Adminer, pas de port PostgreSQL publié sur l'hôte — seul `sc_back` atteint `pgsql`.
- `container_name` et réseau (`scback_prod`) distincts du stack dev : les deux peuvent tourner en parallèle sans collision.
- Dockploy gère le reverse proxy/TLS en amont : le conteneur ne fait que parler HTTP sur `APP_PORT` (pas de nginx à l'intérieur).

## Variables d'environnement

À saisir dans Dockploy (voir [.env.production.example](../.env.production.example) pour la liste exacte consommée par `docker-compose.prod.yml`) :

| Variable | Valeur dev | Valeur prod |
|---|---|---|
| `APP_ENV` | `local` | `production` (fixé dans docker-compose.prod.yml) |
| `APP_DEBUG` | `true` | `false` (fixé dans docker-compose.prod.yml) — expose stacktraces et données sensibles sinon |
| `APP_KEY` | généré en local | généré **une seule fois** (`php artisan key:generate --show`), jamais régénéré ensuite — le régénérer invalide sessions/cookies/données chiffrées existantes |
| `APP_URL` | `http://localhost:8000` | URL publique en HTTPS |
| `DB_DATABASE` | `sc_back` | Nom de base spécifique |
| `DB_USERNAME` | `sail` | Utilisateur dédié sans droits superuser |
| `DB_PASSWORD` | `password` | Mot de passe fort (min. 20 caractères, aléatoire) |
| `LOG_LEVEL` | `debug` | `error` (fixé dans docker-compose.prod.yml) |
| `CORS_ALLOWED_ORIGINS` / `SANCTUM_STATEFUL_DOMAINS` | `localhost:3000` | domaine public du front |

## Hook de déploiement Dockploy

`migrate`/`key:generate`/`filament:upgrade` ne tournent pas dans le conteneur (pas d'accès `.env` au build, voir `Dockerfile.prod`) : à configurer comme commande de déploiement Dockploy (pre- ou post-deploy selon ce que l'interface propose), exécutée dans le conteneur `sc_back` après chaque déploiement :

```bash
php artisan package:discover --ansi
php artisan filament:upgrade
php artisan migrate --force
php artisan storage:link
```

> `public/storage` (symlink) est exclu du build (`.dockerignore`) : sans `storage:link` au hook, les médias uploadés (images, PDF) renvoient 404 côté front.

> `php artisan migrate --force` bypass la confirmation interactive — ne jamais l'exécuter sur une base de production sans sauvegarde préalable. Ne jamais inclure `db:seed` (webmaster de démo) dans ce hook — réservé au dev.
>
> `key:generate` ne fait **pas** partie de ce hook : à exécuter une seule fois à la main avant le tout premier déploiement, la valeur générée devient `APP_KEY` dans les variables d'environnement Dockploy (voir tableau ci-dessus).
