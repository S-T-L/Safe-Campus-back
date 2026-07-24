# Infrastructure — Stats & Estimation prod

## Environnement de mesure

- Machine hôte : WSL2 (Linux 6.6, 7.756 GiB RAM allouée à Docker)
- Stack : SC_Back (Laravel 12) + SC_Postgres (PostgreSQL 17) + SC_Adminer
- État : devcontainer VS Code actif, base de données de développement (vide)

---

## Consommation runtime (devcontainer actif)

| Container | CPU | RAM | RAM % hôte |
|---|---|---|---|
| SC_Back (Laravel + VS Code Server) | 3.98% | 1.37 GiB | 17.7% |
| SC_Postgres | 0.01% | 24 MiB | 0.3% |
| SC_Adminer | 0.00% | 21 MiB | 0.3% |
| **Total** | | **~1.42 GiB** | **~18.3%** |

> Le 1.37 GiB de SC_Back est quasi-entièrement dû au **VS Code Server** installé dans le container (`/home/scback/.vscode-server` = 1.6 G disque). Ce coût disparaît en production.

---

## Tailles des images Docker

| Image | Tag | Taille |
|---|---|---|
| sc_back (app dev) | latest | 933 MB |
| postgres | 17 | 453 MB |
| adminer | latest | 119 MB |
| **Total stack dev** | | **~1.5 GB** |

### Composition de l'image app (933 MB)

| Couche | Poids estimé |
|---|---|
| Ubuntu 24.04 | ~78 MB |
| PHP 8.4 + extensions + Composer | ~250 MB |
| Node 24 + npm + Claude Code | ~450 MB |
| PostgreSQL client 17 | ~50 MB |
| Xdebug + outils dev | ~50 MB |

En production (PHP-FPM seul, sans Node/Xdebug/Claude Code) : **image estimée à 300–400 MB**.

---

## Stockage volumes

| Volume | Contenu | Taille actuelle |
|---|---|---|
| `sc_back_scback-pgsql` | Données PostgreSQL (dev) | **69 MB** |

> 69 MB = base de développement quasi-vide. La taille en production dépend entièrement du volume applicatif.

---

## Estimation production

### Ressources serveur

| Ressource | Minimum | Recommandé |
|---|---|---|
| RAM | 512 MB | 1–2 GB |
| CPU | 1 vCPU | 2 vCPU |
| Disque OS + app | 5 GB | 10 GB |
| Disque PostgreSQL | 5 GB | 20 GB+ |

### Hypothèses

- Laravel en production : **128–256 MB RAM** (sans VS Code Server, sans Node)
- PostgreSQL : **256 MB–1 GB RAM** selon charge et taille des données
- Pas de Redis dans le stack actuel — à prévoir si queues volumineuses
- Vite est build-time uniquement : aucun coût runtime côté serveur

### Exemples de sizing cloud (indicatif)

| Profil | Specs | Usage |
|---|---|---|
| Petit (staging / faible trafic) | 1 vCPU / 1 GB RAM / 20 GB SSD | < 50 utilisateurs simultanés |
| Moyen | 2 vCPU / 2 GB RAM / 40 GB SSD | 50–500 utilisateurs |
| Séparation DB | App : 2 vCPU / 2 GB + DB : 2 vCPU / 4 GB | Charge variable, backups indépendants |

---

## Dette disque locale (à nettoyer)

Constat sur la machine de développement :

| Type | Total | Récupérable |
|---|---|---|
| Images | 22.7 GB | 15.1 GB (66%) |
| Containers | 2.4 GB | 793 MB |
| Volumes | 9.6 GB | 7.2 GB (75%) |
| Build cache | 26.6 GB | 20.7 GB |

**5 images dupliquées** générées par VS Code lors des rebuilds du devcontainer (`vsc-sc_back-…`, `vsc-test_sc_back-…`, etc.) représentent ~4.7 GB récupérables sans risque :

```bash
docker image prune -f
```

Pour un nettoyage global (tous projets, prudence) :

```bash
docker system prune --volumes
```