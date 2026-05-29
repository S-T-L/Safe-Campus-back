# Installation — SAE501

> **Environnement de développement local** — voir la section [Déploiement en production](#déploiement-en-production) avant toute mise en ligne.

Prérequis à installer **sur la machine hôte** avant d'ouvrir le projet dans un devcontainer VS Code.

---

## Déploiement en production

Variables `.env` **obligatoirement** à modifier avant toute mise en ligne :

| Variable | Valeur dev (à changer) | Recommandation prod |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` — expose stacktraces et données sensibles si laissé à `true` |
| `APP_URL` | `http://localhost:8000` | URL publique avec HTTPS |
| `DB_DATABASE` | `sc_back` | Nom de base spécifique |
| `DB_USERNAME` | `sail` | Utilisateur dédié sans droits superuser |
| `DB_PASSWORD` | `password` | Mot de passe fort (min. 20 caractères, aléatoire) |
| `LOG_LEVEL` | `debug` | `error` |

Commandes à exécuter après déploiement :

```bash
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> `php artisan migrate --force` bypass la confirmation interactive — ne jamais exécuter sur une base de production sans sauvegarde préalable.

---

## 0. Installation de WSL (Windows uniquement)

### Activer WSL

Dans un terminal PowerShell **en administrateur** :

```powershell
wsl --install
```

Redémarre la machine si demandé, puis installe une distro Ubuntu :

```powershell
wsl --install -d Ubuntu-24.04
```

Au premier lancement, WSL demande de créer un utilisateur Linux (nom + mot de passe).

Vérifier que la distro est bien active :

```powershell
wsl --list --running
```

### Configurer Git dans WSL

```bash
git config --global user.name "Prénom Nom"
git config --global user.email "ton@email.com"
```

### Configurer SSH pour GitHub

Générer une clé SSH (laisser la passphrase vide en appuyant sur Entrée) :

```bash
ssh-keygen -t ed25519 -C "ton@email.com"
```

Afficher la clé publique :

```bash
cat ~/.ssh/id_ed25519.pub
```

Copier la clé affichée, puis sur GitHub : **Settings → SSH and GPG keys → New SSH key** → coller et sauvegarder.

Tester la connexion :

```bash
ssh -T git@github.com
```

> La réponse attendue est `Hi <username>! You've successfully authenticated`.

### Activer le SSH agent au démarrage de WSL

Le devcontainer forward le socket SSH de l'hôte pour permettre `git push` depuis l'intérieur du container. L'agent doit être actif **avant** d'ouvrir le devcontainer.

Ajouter à la fin de `~/.bashrc` (ou `~/.zshrc`) sur WSL :

```bash
# SSH agent — démarrage automatique
if [ -z "$SSH_AUTH_SOCK" ]; then
    eval $(ssh-agent -s) > /dev/null
    ssh-add ~/.ssh/id_ed25519 2>/dev/null
fi
```

Recharger le shell :

```bash
source ~/.bashrc
```

Vérifier que la clé est bien chargée :

```bash
ssh-add -l
```

> Si `SSH_AUTH_SOCK` n'est pas défini au moment d'ouvrir le devcontainer, `git push` depuis le container échouera. Pusher depuis WSL reste toujours possible en secours.

### Cloner les deux dépôts

Les deux repos doivent être **dans le même dossier parent** — le `docker-compose.yml` du back référence le front via un chemin relatif `../Safe-Campus-front`.

```bash
mkdir -p ~/workspace/safe-campus && cd ~/workspace/safe-campus
git clone git@github.com:<org>/Safe-Campus-back.git
git clone git@github.com:<org>/Safe-Campus-front.git
```

---

## 1. Logiciels requis

| Outil | Version minimale | Lien |
|---|---|---|
| Docker Desktop | 4.x | https://www.docker.com/products/docker-desktop |
| VS Code | 1.85+ | https://code.visualstudio.com |
| Git | 2.x | https://git-scm.com |

---

## 2. Configuration Docker Desktop (WSL)

Sous Windows avec WSL 2, Docker Desktop ne communique pas automatiquement avec toutes les distros WSL.

Activer l'intégration : **Docker Desktop** → **Settings** → **Resources** → **WSL Integration** (sous-onglet dans Resources) → cocher la distro cible (ex. `Ubuntu-24.04`).

Sans cette étape, toute commande `docker` depuis WSL retourne `Cannot connect to the Docker daemon`.

---

## 3. Extensions VS Code hôte (obligatoires)

Extensions permettant à VS Code de se connecter aux conteneurs et environnements distants.
Installation impossible via `devcontainer.json` — procéder manuellement :

```bash
code --install-extension ms-vscode-remote.remote-containers
code --install-extension ms-vscode-remote.remote-ssh
code --install-extension ms-vscode-remote.remote-wsl
code --install-extension ms-vscode.remote-explorer
code --install-extension ms-azuretools.vscode-docker
```

> **Pourquoi ces extensions sont hôte-only ?**
> Remote SSH et Remote WSL servent de pont entre VS Code (Windows) et l'environnement
> cible (serveur SSH ou noyau Linux WSL). Le devcontainer s'exécute à l'intérieur de cet
> environnement — ces extensions doivent être présentes sur la machine hôte, pas dans le conteneur.

---

## 4. Démarrer l'environnement de développement

### Première utilisation

```bash
cd Safe-Campus-back
cp .env.example .env
```

Renseigner les variables dans le `.env` :
- `WWWUSER` / `WWWGROUP` — UID/GID de la machine hôte (`id -u` et `id -g`)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — credentials PostgreSQL

> Le `.env` doit être présent avant d'ouvrir le devcontainer — VS Code démarre les containers Docker automatiquement via `docker-compose.yml` au "Reopen in Container".

Dans VS Code, ouvrir le dossier `Safe-Campus-back` puis : `Ctrl+Shift+P` → **Dev Containers: Reopen in Container**

### Ce qui démarre automatiquement

Ouvrir le devcontainer du **back** lance l'ensemble du stack :

| Conteneur | Rôle | Démarrage |
|---|---|---|
| `SC_Back` | Laravel (PHP 8.4) | Automatique via `artisan serve` |
| `SC_Front` | Nuxt 3 + Vite HMR | Automatique via `npm run dev` |
| `SC_Postgres` | PostgreSQL 17 | Automatique |
| `SC_Adminer` | Interface DB | Automatique |

Le `setup.sh` s'exécute une fois à la création du container et installe les dépendances Composer, génère la clé d'application et exécute les migrations.

### Ouvrir le front indépendamment

Il est possible d'ouvrir `Safe-Campus-front` dans un devcontainer séparé. Il utilise le même `docker-compose.yml` que le back — si `SC_Front` tourne déjà, VS Code se réattache au conteneur existant sans en créer un nouveau.

### Ports exposés

| Port | Service | URL |
|---|---|---|
| `8000` | Laravel | http://localhost:8000 |
| `3000` | Nuxt | http://localhost:3000 |
| `24678` | Vite HMR (front) | — |
| `5432` | PostgreSQL | — |
| `8080` | Adminer | http://localhost:8080 |

### Connexion Adminer

> Adminer sélectionne `MySQL` par défaut — bien changer le **moteur** en `PostgreSQL` avant de se connecter.

- **Moteur** : `PostgreSQL` *(menu déroulant en haut)*
- **Serveur** : `pgsql`
- **Utilisateur** : valeur de `DB_USERNAME` dans `.env`
- **Mot de passe** : valeur de `DB_PASSWORD` dans `.env`
- **Base de données** : valeur de `DB_DATABASE` dans `.env`

---

## 5. Dépannage

### `Could not resolve host: deb.nodesource.com` lors du build Docker

Problème DNS dans le réseau Docker (fréquent sous WSL2). Créer le fichier de config Docker avec un DNS public :

```bash
sudo mkdir -p /etc/docker
echo '{"dns": ["8.8.8.8", "8.8.4.4"]}' | sudo tee /etc/docker/daemon.json
sudo service docker restart
```

### `could not translate host name "pgsql"` lors du setup

Symptôme : le container back ne trouve pas PostgreSQL. Cause probable : un container `SC_Postgres` stale d'une session précédente a perdu son attachement réseau.

Solution : toujours repartir d'un état propre avant un rebuild :

```bash
cd Safe-Campus-back
docker compose down
```

Puis relancer le devcontainer depuis VS Code.

### Rebuild du devcontainer

Certains fichiers sont copiés dans l'image Docker au moment du build et ne sont **pas** mis à jour par le volume monté. Tout changement sur ces fichiers nécessite un rebuild :

| Fichier modifié | Action requise |
|---|---|
| `docker/8.4/Dockerfile` | Rebuild sans cache |
| `supervisord.conf` | Rebuild (avec ou sans cache) |
| `docker-compose.yml` | Rebuild |
| `.devcontainer/devcontainer.json` | Rebuild |
| `start-container`, `php.ini` | Rebuild |

**Rebuild avec cache** (rapide — réutilise les couches Docker non modifiées) :

`Ctrl+Shift+P` → **Dev Containers: Rebuild Container**

**Rebuild sans cache** (complet — re-télécharge tout, à utiliser si le cache pose problème) :

`Ctrl+Shift+P` → **Dev Containers: Rebuild Without Cache and Reopen in Container**

Ou depuis le terminal WSL avant d'ouvrir VS Code :

```bash
docker compose build --no-cache
```

---

## 6. Structure du projet

```mermaid
graph TD
    root["safe-campus/"]

    root --> scback["Safe-Campus-back/\nAPI + rendu serveur\nLaravel + Inertia + Vue"]
    root --> scfront["Safe-Campus-front/\nInterface utilisateur\nNuxt 3 + Vue 3"]

    scback --> readme["README.md\nGuide d'installation"]
    scback --> dc["docker-compose.yml\nOrchestration : back, front, pgsql, adminer"]
    scback --> devcback[".devcontainer/\nConfig VS Code devcontainer"]
    scback --> app["app/\nCode PHP Laravel"]
    scback --> res["resources/"]
    scback --> docker["docker/8.4/\nDockerfile PHP 8.4"]

    res --> js["js/\nVue 3 / Inertia\nPages, Components…"]
    res --> css["css/\nStyles globaux"]

    scfront --> devcfront[".devcontainer/\nConfig VS Code devcontainer"]
    scfront --> pages["pages/\nPages Nuxt"]
    scfront --> components["components/\nComposants Vue"]
    scfront --> frontdocker["Dockerfile\nNode 22 Alpine"]
```
