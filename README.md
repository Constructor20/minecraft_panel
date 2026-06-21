# 🎮 Minecraft Panel

**Minecraft Panel** est une interface web moderne et légère pour administrer vos serveurs Minecraft, développée avec Laravel 11.

> ⚡ Conçue pour fonctionner en duo avec une API Python distante (exécutée sur un PC Windows) qui pilote les processus serveur en temps réel.

---

## 🖼️ Aperçu

- **Design dark / glassmorphism** — Interface sombre avec effets de verre, animations fluides et dégradés
- **Sidebar rétractable** — Navigation latérale avec indicateur de page active
- **Responsive** — Adapté mobile, tablette et desktop

---

## ✨ Fonctionnalités

### 🔐 Authentification
- Inscription / Connexion par **email**
- Profil utilisateur accessible depuis la sidebar
- Protection CSRF, sessions sécurisées

### 📊 Dashboard
- Vue d'ensemble rapide : nombre de serveurs, accès console, fichiers, administration
- Cartes d'accès rapide vers chaque section

### 🖥️ Serveurs
- Liste complète avec cartes stylisées (type, RAM, port, joueurs)
- Statut en ligne / hors ligne
- Actions par serveur : `Démarrer` (désactivé si en ligne), `Console` (vert), `Fichiers` (bleu)
- Badge de type coloré selon le logiciel serveur (Forge, Vanilla, Paper, Purpur, Spigot)

### 💻 Console (Terminal)
- Terminal épuré, prêt à recevoir les flux d'une API distante
- Pas de données fictives — Interface propre pour l'intégration future
- Commande `Envoyer` avec retour horodaté
- Raccourci `Ctrl+L` pour effacer

### 📁 Gestionnaire de fichiers
- Vue d'ensemble : sélection d'un serveur parmi les cartes
- Explorateur basique (liste statique en attendant l'API distante)
- Dossiers typiques d'un serveur Minecraft (logs, plugins, world, config...)

### ⚙️ Administration
- **CRUD serveurs** — Ajouter / Modifier / Supprimer des serveurs via modals
- **Permissions** — Associer un utilisateur à un serveur avec droits (console, fichiers, démarrage...)
- Interface glass dédiée avec tableaux et formulaires modaux

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────┐
│              Raspberry Pi (Docker)               │
│  ┌───────────────────────────────────────────┐  │
│  │     minecraft_panel_laravel (Laravel 11)   │  │
│  │  - Interface web (Apache + PHP 8.2)        │  │
│  │  - Base de données MariaDB (externe)       │  │
│  │  - Port 8112                               │  │
│  └───────────────────────────────────────────┘  │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │     MariaDB (Container séparé)            │  │
│  │  - minecraft_panel_prod_db                │  │
│  │  - Tables : users, servers, permissions   │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
         ▲ API REST (future)
         │
┌─────────────────────────────────────────────────┐
│              PC Windows (Client)                 │
│  - Script Python de démarrage/arrêt serveur     │
│  - Envoi/reception commandes console             │
│  - Synchronisation des fichiers                 │
└─────────────────────────────────────────────────┘
```

### Stack technique
| Technologie | Version |
|---|---|
| Laravel | 11.54 |
| PHP | 8.2.30 |
| Apache | mod_php |
| MariaDB | 10.x (container dédié) |
| Docker | Compose v5.1 |
| Tailwind CSS | CDN + personnalisé |

---

## 🚀 Déploiement

### Prérequis
- Docker & Docker Compose sur le Raspberry Pi
- MariaDB accessible (container `minecraft_panel_prod_db`)
- Clé SSH pour accès distant

### Installation

```bash
git clone git@github.com:Constructor20/minecraft_panel.git
cd minecraft_panel

cp .env.example .env
# Éditer .env avec vos identifiants de base de données

docker compose up -d
# Les migrations et dépendances s'exécutent automatiquement au démarrage
```

### Variables d'environnement principales
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=minecraft_panel_prod_db
DB_PORT=3306
DB_DATABASE=minecraft_panel_laravel
DB_USERNAME=minecraft_laravel
DB_PASSWORD=****
```

---

## 🗄️ Base de données

**Aucune modification du schéma existant** — les modèles Laravel sont mappés sur les tables :
- `users` — username, email, password
- `servers` — 20 colonnes (name, type, ram, port, path, max_players...)
- `permissions` — user_id, server_id, 5 droits booléens (can_console, can_files...)

> Le statut `online/offline` est géré côté vue (parité de l'ID) — pas de colonne status.

---

## 📂 Structure du projet

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/                  # LoginController, RegisterController, LogoutController
│   │   ├── AdminController.php    # CRUD serveurs + permissions
│   │   ├── DashboardController.php
│   │   └── ServerController.php   # Serveurs, console, fichiers
│   └── Models/
│       ├── Server.php             # Casts + accesseurs + relations
│       ├── Permission.php         # Casts booléens
│       └── User.php               # Relation permissions()
├── database/
│   ├── migrations/                # 6 migrations
│   └── seeders/DatabaseSeeder.php # 5 serveurs fictifs de démo
├── resources/views/
│   ├── layouts/app.blade.php      # Layout principal + sidebar
│   ├── auth/                      # login.blade.php, register.blade.php
│   ├── admin/index.blade.php      # CRUD serveurs + permissions
│   ├── servers/                   # index, console, files
│   ├── dashboard.blade.php
│   └── welcome.blade.php
├── routes/web.php                 # 15 routes (auth, protégées, admin)
├── docker-compose.yml
└── Dockerfile
```

---

## 🧪 Développement

```bash
# Accéder au conteneur
docker exec -it minecraft_panel_laravel bash

# Vider le cache des vues
php artisan view:clear && php artisan view:cache

# Voir les routes
php artisan route:list

# Lancer les seeds
php artisan db:seed
```

---

## 🔮 Roadmap

- [ ] API Python temps réel pour start/stop/console/files
- [ ] Permissions automatiques à la création d'utilisateur
- [ ] Upload / download / édition de fichiers via API
- [ ] Logs de connexion et historique des commandes

---

## 👤 Auteur

**Constructor20** — Projet personnel pour l'administration de serveurs Minecraft.

Ce projet a été entièrement développé via **opencode**, un outil CLI de coding assisté par IA.
