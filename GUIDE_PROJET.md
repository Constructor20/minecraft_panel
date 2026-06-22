# 🎮 Projet Minecraft Panel — Le Guide Complet

## 1. C'est quoi ce truc ?

Un panel web Laravel pour gérer des serveurs Minecraft. Tu peux :
- Voir la liste de tes serveurs avec leurs stats (RAM, port, joueurs)
- Ouvrir une **console** pour envoyer des commandes
- Explorer les **fichiers** du serveur (dossiers, configs…)
- Gérer les **permissions** : qui a le droit de faire quoi sur quel serveur
- **Admin** : ajouter/supprimer/modifier des serveurs, utilisateurs, permissions

## 2. Stack technique

| Truc | Détail |
|------|--------|
| **Framework** | Laravel 11 |
| **PHP** | 8.2.30 |
| **Base de données** | MariaDB (base `minecraft_panel_laravel`) |
| **Serveur web** | Apache |
| **Conteneur** | Docker (bind mount vers `/home/chris/minecraft_panel_laravel`) |
| **Front** | Blade + Tailwind (glassmorphism, dark theme) |
| **Port** | 8112 (traduit du 80 du conteneur) |
| **Auth** | Email + password (laravel/ui scaffold) |

## 3. Structure du projet

```
minecraft_panel_laravel/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Auth/
│   │       │   ├── LoginController.php      # Login par email
│   │       │   ├── RegisterController.php    # Register (username + email)
│   │       │   └── LogoutController.php      # Déconnexion
│   │       ├── DashboardController.php       # Page d'accueil (/dashboard)
│   │       ├── ServerController.php          # Index / Console / Fichiers
│   │       └── AdminController.php           # CRUD serveurs + permissions + users
│   └── Models/
│       ├── User.php                          # hasMany permissions
│       ├── Server.php                        # hasMany permissions, accesseurs type_icon/color/label
│       └── Permission.php                    # belongsTo user + server
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php            # id, username, email, password
│   │   ├── create_servers_table.php          # name, type, port, ram, path, jar, pc_ip...
│   │   └── create_permissions_table.php      # user_id, server_id, can_view/start/stop/console/files
│   └── seeders/
│       └── DatabaseSeeder.php                # 5 serveurs (Create 2, Survie, Mini-Jeux, Skyblock, Ancien)
├── resources/views/
│   ├── layouts/app.blade.php                 # Layout principal : sidebar, header, profile dropdown
│   ├── auth/login.blade.php                  # Login form
│   ├── auth/register.blade.php               # Register form
│   ├── dashboard.blade.php                   # Cartes de navigation
│   ├── admin/index.blade.php                 # Tableau serveurs + utilisateurs + permissions tab
│   └── servers/
│       ├── index.blade.php                   # Liste serveurs avec cards complètes
│       ├── console.blade.php                 # Cards console → terminal ou overview
│       └── files.blade.php                   # Cards fichiers → explorateur ou overview
└── routes/web.php                            # Toutes les routes
```

## 4. BDD — Les tables

### `users`
| Champ | Type |
|-------|------|
| id | auto-increment |
| username | string |
| email | string (unique) |
| password | hashed |

### `servers`
| Champ | Type |
|-------|------|
| id | auto-increment |
| name | string |
| type | enum('vanilla','paper','spigot','purpur','forge','fabric') |
| port | integer (unique) |
| ram | integer |
| ram_unit | 'G' ou 'M' |
| max_players | integer |
| path | string (chemin dossier serveur) |
| jar_file | string (fichier jar) |
| java_args | string (args JVM) |
| pc_ip | string (IP du PC hébergeur) |
| auto_start | boolean |

### `permissions`
| Champ | Type |
|-------|------|
| id | auto-increment |
| user_id | foreign → users (cascade) |
| server_id | foreign → servers (cascade) |
| can_view | boolean |
| can_start | boolean |
| can_stop | boolean |
| can_console | boolean |
| can_files | boolean |

## 5. Les routes

```
GET  /login                → formulaire de connexion
POST /login                → connexion
GET  /register             → formulaire d'inscription
POST /register             → inscription

=== Routes protégées (auth) ===

GET  /dashboard            → page d'accueil
GET  /servers              → liste des serveurs (cards)
GET  /servers/console      → overview cards → console (?id=X)
GET  /servers/files        → overview cards → explorateur (?id=X)
GET  /admin                → panneau admin
POST /admin/servers        → créer serveur
PUT  /admin/servers/{id}   → modifier serveur
DELETE /admin/servers/{id} → supprimer serveur (+ permissions liées)
POST /admin/permissions    → ajouter permission
PUT  /admin/permissions/{id} → modifier permission
DELETE /admin/permissions/{id} → supprimer permission
DELETE /admin/users/{id}   → supprimer utilisateur (+ permissions liées)
POST /logout               → déconnexion
```

## 6. Les vues en détail

### Sidebar (layouts/app.blade.php)
- Logo + nom du panel en haut
- Dashboard, Serveurs, Console, Fichiers, Admin (si admin)
- Profile dropdown en bas (avatar cliquable → menu)
- Highlight de l'onglet actif par route

### Dashboard (dashboard.blade.php)
- 4 cartes : Serveurs, Console, Fichiers, Admin
- Stats (nombre de serveurs, utilisateurs)

### Index serveurs (servers/index.blade.php)
- Cards avec barre de couleur (type du serveur)
- Icône + nom + type + status (ON/OFF avec ping animé)
- Stats : RAM, Port, Joueurs
- Boutons : Console (vert) + Fichiers (bleu)
- Clic sur la card → console du serveur

### Console (servers/console.blade.php)
- **Overview** (sans ?id) : cards design terminal (bordures vertes, fond dégradé)
  - Nom, type, RAM, port, joueurs, status
  - Bouton "Ouvrir la console →"
  - Clic → console du serveur
- **Terminal** (avec ?id=X) : stats + terminal vide + input commandes

### Fichiers (servers/files.blade.php)
- **Overview** (sans ?id) : cards design explorateur (bordures ambrées)
  - Nom, type, RAM, port, chemin
  - Bouton "Explorer les fichiers →"
  - Clic → explorateur du serveur
- **Explorateur** (avec ?id=X) : liste de fichiers statiques (logs, plugins, configs…)

### Admin (admin/index.blade.php)
- **Serveurs** : tableau + modal créer/éditer
- **Utilisateurs** : tableau + bouton supprimer
- **Permissions** : tabs par utilisateur
  - Chaque onglet : tableau avec les 5 droits (Voir, Start, Stop, Console, Fichiers)
  - ✅ / ❌ par droit
  - Bouton ✏️ modifier (modal pré-rempli)
  - Poubelle pour supprimer
  - Bouton "Nouvelle permission" (choisir user + server + droits)

## 7. Comment les pages interagissent

```
/servers
  → cards complètes avec 2 boutons (Console + Fichiers)
  → clic sur card = console

/servers/console
  → cards design terminal avec 1 bouton "Console"
  → clic sur card = console?id=X (terminal)
  → clic "Fichiers" = files?id=X (explorateur)

/servers/files
  → cards design explorateur avec 1 bouton "Fichiers"
  → clic sur card = files?id=X (explorateur)
```

Pas de redirection inutile : chaque vue a sa propre page overview avec des cards adaptées.

## 8. Admin — Gestion des permissions

- Les permissions sont liées à un couple (user_id, server_id) — unique
- 5 droits possibles : view / start / stop / console / files
- Interface en tabs : chaque user a son onglet
- Édition via modal : tu coches/décoches et sauvegardes
- Si tu supprimes un serveur ou un user, les permissions attachées sautent aussi (cascade)

## 9. Le conteneur Docker

```
Image : php:8.2-apache (custom)
Port : 8112 → 80
Volume : /home/chris/minecraft_panel_laravel → /var/www/html
```

Au démarrage :
1. `composer install`
2. `php artisan migrate --force`
3. Redémarrage Apache

Si ça marche pas : `docker restart minecraft_panel_laravel`
Si les caches plantent : `chown -R www-data:www-data storage/framework/views/`

## 10. Seeds actuels

**Serveurs (5) :**
| ID | Nom | Type | Port | RAM | Auto | PC IP |
|----|-----|------|------|-----|------|-------|
| 1 | Create 2 | Forge | 25565 | 8G | ✅ | 192.168.1.22 |
| 2 | Survie Vanilla | Vanilla | 25566 | 4G | ❌ | 192.168.1.22 |
| 3 | Mini-Jeux | Paper | 25567 | 6G | ❌ | 192.168.1.22 |
| 4 | Skyblock | Purpur | 25568 | 3G | ❌ | 192.168.1.22 |
| 5 | Ancien Serveur | Spigot | 25569 | 2G | ❌ | 192.168.1.22 |

**Utilisateurs :**
| ID | Username | Email |
|----|----------|-------|
| 1 | Chris | aleixochristophe@gmail.com |
| 2 | testuser | test@test.com |

**Permissions :**
- Chris → tous les droits sur les 5 serveurs
- testuser → view + console sur les 5 serveurs

## 11. Architecture — Le flow

```
User (navigateur)
     ↓ HTTP
Panel Laravel (Apache :8112)
     ├── Auth (email/password)
     ├── Dashboard → stats
     ├── Serveurs → liste
     ├── Console → terminal (vide, prêt API)
     ├── Fichiers → explorateur (statique)
     └── Admin → CRUD + permissions
     ↓
MariaDB (base dédiée)
     └── users, servers, permissions
```

L'API Python pour les actions réelles (start/stop/console/files) est en attente.
Le panel est en mode "démo" : l'interface est prête, les actions réelles viendront après.

## 12. Pour reproduire

```bash
git clone https://github.com/Constructor20/minecraft_panel.git
cd minecraft_panel

# .env à configurer avec :
# DB_DATABASE=minecraft_panel_laravel
# DB_USERNAME=minecraft_laravel
# DB_PASSWORD=laravel_pass_123

docker compose up -d
# → accessible sur http://IP:8112
# → php artisan migrate --seed
```

## 13. Ce qui manque / À faire

- [ ] API Python pour start/stop réels
- [ ] Console fonctionnelle (vraies commandes)
- [ ] Upload/download/édition de fichiers
- [ ] Gestion automatique des permissions à la création d'un user
- [ ] Rôles (admin, user simple…)
