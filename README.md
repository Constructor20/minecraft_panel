# Minecraft Panel - Projet BTS SIO SLAM

## Structure du projet

```
minecraft_panel/
├── app/
│   ├── Database/
│   │   └── Database.php      # Classe de connexion PDO
│   ├── Http/Controllers/
│   │   ├── AuthController.php # Connexion/Déconnexion
│   │   └── ServerController.php # CRUD serveurs
│   ├── Models/
│   │   ├── User.php          # Modèle utilisateur
│   │   └── Server.php        # Modèle serveur
│   └── Router.php            # Routing simple
├── bootstrap/
│   └── app.php               # Initialisation
├── config/
│   ├── app.php               # Config application
│   ├── database.php          # Config BDD
│   └── pctour.php            # Config PC Tour
├── database/
│   ├── seed.php              # Script de seed
│   └── seeders/
│       ├── UserSeeder.php    # Seed utilisateurs
│       └── ServerSeeder.php  # Seed serveurs
├── public/
│   ├── css/style.css         # Styles
│   └── index.php             # Point d'entrée
├── resources/views/
│   ├── auth/
│   │   ├── login.php         # Page connexion
│   │   └── register.php      # Page inscription
│   ├── servers/
│   │   ├── index.php         # Liste serveurs
│   │   ├── create.php        # Créer serveur
│   │   ├── edit.php          # Modifier serveur
│   │   └── show.php          # Détail serveur
│   ├── layouts/
│   │   ├── header.php        # En-tête
│   │   └── footer.php        # Pied de page
│   ├── dashboard.php         # Page d'accueil
│   ├── profile.php           # Profil utilisateur
│   └── errors/
│       └── 404.php           # Erreur 404
├── routes/
│   └── web.php               # Routes
├── config/
│   └── ...
└── .htaccess                # Configuration Apache
```

## Installation

1. Configurer la base de données dans `config/database.php`
2. Exécuter les seeders : `php database/seed.php`
3. Lancer le serveur PHP : `php -S localhost:8101 -t public`

## Comptes de test

- **admin** / admin123 (administrateur)
- **chris** / chris123 (utilisateur)

## Routes

| Route | Description |
|-------|-------------|
| `/` | Redirection |
| `/login` | Connexion |
| `/logout` | Déconnexion |
| `/register` | Inscription |
| `/dashboard` | Tableau de bord |
| `/servers` | Liste des serveurs |
| `/servers/create` | Créer un serveur |
| `/servers/{id}` | Détail serveur |
| `/servers/{id}/edit` | Modifier serveur |
| `/servers/{id}/delete` | Supprimer serveur |
| `/servers/{id}/start` | Démarrer serveur |
| `/servers/{id}/stop` | Arrêter serveur |

## Exercices BTS SIO

1. Ajouter la fonctionnalité de changement de mot de passe dans le profil
2. Ajouter un middleware pour vérifier le rôle admin
3. Implémenter les действий WOL et API dans ServerController
4. Créer une page d'administration pour gérer les utilisateurs
5. Ajouter la validation des formulaires
6. Améliorer le CSS avec un framework (Bootstrap/Tailwind)
