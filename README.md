# Minecraft Panel

Panel d'administration pour gérer les serveurs Minecraft à distance.

## Fonctionnalités

- 🎮 Démarrage/Arrêt des serveurs Minecraft
- 📁 Gestion des fichiers serveur
- 💻 Contrôle du PC distant (WOL, SSH, shutdown)
- 🔄 API Python pour la communication

## Configuration

### Variables importantes

```php
// PC Tour (Windows)
PC_IP = '192.168.1.22'
PC_MAC = '2c:f0:5d:7f:e3:2b'
SSH_USER = 'aleix'

// API
API_KEY = '6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8='
API_PORT = 8080

// Base de données
DB_HOST = '192.168.1.59'
DB_PORT = 8005
DB_USER = 'root'
DB_PASS = 'nouveaumotdepasse123'
```

### Fichiers de configuration

| Fichier | Description |
|---------|-------------|
| `includes/lib/woltour.php` | Wake-on-LAN |
| `includes/lib/sshtour.php` | Connexion SSH |
| `includes/lib/api_helper.php` | Fonctions API |
| `includes/api/minecraft_api.py` | API Python (sur PC Windows) |

## Installation

### 1. Base de données

```sql
CREATE DATABASE minecraft_panel;
USE minecraft_panel;

CREATE TABLE servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    path VARCHAR(500),
    port INT DEFAULT 25565,
    max_players INT DEFAULT 20,
    java_args VARCHAR(500),
    auto_start TINYINT(1) DEFAULT 0,
    auto_restart TINYINT(1) DEFAULT 0,
    restart_time TIME,
    backup_enabled TINYINT(1) DEFAULT 0,
    backup_interval INT DEFAULT 60,
    notify_stop TINYINT(1) DEFAULT 0,
    notify_message VARCHAR(255)
);
```

### 2. Docker

```bash
docker compose up -d
```

### 3. PC Windows

Copier `includes/api/minecraft_api.py` vers `F:\all_serv\minecraft_api.py`

Créer une tâche planifiée:
```powershell
# Exécuter en tant qu'admin
schtasks /create /tn MinecraftAPI /tr "python F:\all_serv\minecraft_api.py" /sc onlogon /rl highest
```

## Utilisation

### Page de test

**http://192.168.1.59:8101/test_panel.php**

### Endpoints API

| Action | Description |
|--------|-------------|
| `test_api.php?action=wol` | Allumer le PC |
| `test_api.php?action=ping` | Vérifier PC/API |
| `test_api.php?action=start-api` | Démarrer API |
| `test_api.php?action=stop-api` | Arrêter API |
| `test_api.php?action=shutdown-pc` | Éteindre PC |

### Démarrage complet

```
WOL → Attendre PC → Start API → Démarrer serveur MC
```

### Arrêt complet

```
Arrêter serveur MC → Arrêter API → Éteindre PC
```

## Dépannage

### WOL ne fonctionne pas

1. Vérifier que WOL est activé dans le BIOS
2. Vérifier WOL dans les paramètres Windows carte réseau
3. Tester avec: `wakeonlan 2c:f0:5d:7f:e3:2b`

### SSH ne fonctionne pas

1. Vérifier la clé SSH dans `/var/www/id_ed25519`
2. Vérifier que la clé est dans `authorized_keys` sur le PC

### API ne répond pas

1. Vérifier que la tâche planifiée est active
2. Redémarrer: `schtasks /run /tn MinecraftAPI`

## Architecture

```
┌─────────────────┐      ┌─────────────────┐
│   Raspberry Pi  │      │    PC Tour     │
│                 │      │   (Windows)     │
│  ┌───────────┐  │ SSH  │  ┌───────────┐ │
│  │   Panel   │──┼──────┼──│  API.py   │ │
│  │   PHP     │  │      │  │  (Python) │ │
│  └───────────┘  │      │  └───────────┘ │
│        │        │      │       │         │
│        └────────┼──────┼───────┘         │
│                 │      │                 │
│    ┌────────┐  │      │  ┌───────────┐  │
│    │  MySQL │──────────│  │ Minecraft │  │
│    └────────┘  │      │  │  Server   │  │
│                 │      │  └───────────┘  │
└─────────────────┘      └─────────────────┘
```
