# Installation — SAE501

Prérequis à installer **sur la machine hôte** avant d'ouvrir le projet dans un devcontainer VS Code.

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
cd SC_Back
cp .env.example .env
```

Renseigner `WWWUSER` et `WWWGROUP` dans le `.env` avec l'UID/GID de la machine hôte (obtenir les valeurs via `id -u` et `id -g`).

> Valeurs requises pour la construction de l'image avec l'utilisateur `sail` correspondant à l'utilisateur hôte. Sans ces valeurs, le build échoue.

```bash
docker compose up -d
```

Dans VS Code : `Ctrl+Shift+P` → **Dev Containers: Reopen in Container**

Le devcontainer installe automatiquement les dépendances Composer et npm, puis exécute :

```bash
php artisan key:generate
php artisan migrate
```

Les assets Vite sont buildés automatiquement (`npm run build`). L'application est accessible sans lancer Vite manuellement.

### Démarrer Vite (HMR)

Une fois dans le devcontainer :

```bash
npm run dev
```

### Ports exposés

| Port | Service | URL |
|---|---|---|
| `8000` | Laravel | http://localhost:8000 |
| `5173` | Vite HMR | http://localhost:5173 |
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

### `vite: Permission denied` lors du build

Les symlinks dans `node_modules/.bin/` perdent leurs permissions d'exécution sur volumes WSL. Le `setup.sh` corrige automatiquement ce problème via `chmod +x node_modules/.bin/*`.

---

## 6. Structure du projet

```
SAE501/
├── SC_Back/                Application principale (Laravel + Vue + Inertia)
│   ├── docker-compose.yml  Orchestration : sc_back, pgsql, adminer
│   ├── .devcontainer/      Config VS Code devcontainer
│   ├── app/                Code PHP Laravel
│   ├── resources/
│   │   ├── js/             Vue 3 / Inertia (Pages, Components…)
│   │   └── css/            Styles globaux
│   └── vendor/laravel/sail Dockerfile PHP 8.4
└── docs/                   Documentation
    ├── install.md          Ce fichier
    ├── back.md             Conventions backend
    └── front.md            Conventions frontend
```