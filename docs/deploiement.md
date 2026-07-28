# Déploiement en production

> Le stack `docker-compose.yml` du repo est un stack de développement : `artisan serve`, Xdebug, bind mount du code source, PostgreSQL sans persistance externe. Il n'est pas destiné à la production.

## Variables `.env`

| Variable | Valeur dev | Valeur prod |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` — expose stacktraces et données sensibles sinon |
| `APP_URL` | `http://localhost:8000` | URL publique en HTTPS |
| `DB_DATABASE` | `sc_back` | Nom de base spécifique |
| `DB_USERNAME` | `sail` | Utilisateur dédié sans droits superuser |
| `DB_PASSWORD` | `password` | Mot de passe fort (min. 20 caractères, aléatoire) |
| `LOG_LEVEL` | `debug` | `error` |

## Commandes après déploiement

```bash
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> `php artisan migrate --force` bypass la confirmation interactive — ne jamais l'exécuter sur une base de production sans sauvegarde préalable.
