<?php
class ProfileController {
    private $db;
    private $auth;

    public function __construct(Database $db, Auth $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        ob_start();
        ?>
        <div class="page-content">
            <div class="container">
                <div class="card fade-in">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($this->auth->getUsername(), 0, 2)) ?>
                        </div>
                        <h1>Bienvenue, <?= htmlspecialchars($this->auth->getUsername()) ?></h1>
                        <p class="profile-email"><?= htmlspecialchars($this->auth->getEmail()) ?></p>
                    </div>

                    <div class="profile-grid">
                        <div class="card">
                            <h2>Paramètres du compte</h2>
                            <ul class="settings-list">
                                <li>
                                    <a href="<?= BASE_URL ?>/profile/edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        Modifier mes informations
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= BASE_URL ?>/profile/password">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        Changer mon mot de passe
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <h2>Accès rapide</h2>
                            <div class="quick-actions">
                                <a href="<?= BASE_URL ?>/servers" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                    Mes serveurs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    public function edit() {
        ob_start();
        ?>
        <div class="page-content">
            <div class="container">
                <div class="page-header fade-in">
                    <div class="page-header-left">
                        <a href="<?= BASE_URL ?>/profile" class="back-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Retour
                        </a>
                        <div class="page-header-title">
                            <h1>Modifier mon profil</h1>
                        </div>
                    </div>
                </div>

                <div class="card fade-in" style="max-width: 500px; margin: 0 auto;">

                    <?php if (isset($_SESSION['flash']['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']['success']) ?></div>
                        <?php unset($_SESSION['flash']['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash']['error'])): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash']['error']) ?></div>
                        <?php unset($_SESSION['flash']['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/profile/edit">
                        <?= CSRF::tokenField() ?>
                        
                        <div class="form-group">
                            <label for="username">Nom d'utilisateur</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($this->auth->getUsername()) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Adresse e-mail</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($this->auth->getEmail()) ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
                    </form>

                    <div style="margin-top: 20px; text-align: center;">
                        <a href="<?= BASE_URL ?>/profile/password" class="btn btn-secondary btn-sm">Changer le mot de passe</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    public function update() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($username) || empty($email)) {
            $_SESSION['flash']['error'] = 'Tous les champs sont obligatoires.';
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }

        if (strlen($username) < 3) {
            $_SESSION['flash']['error'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash']['error'] = 'Adresse e-mail invalide.';
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }

        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?",
            [$username, $email, $this->auth->getUserId()]
        );

        if ($existing) {
            $_SESSION['flash']['error'] = 'Ce nom d\'utilisateur ou cette adresse e-mail est déjà utilisé.';
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }

        $this->auth->updateProfile($this->auth->getUserId(), $username, $email);
        $_SESSION['flash']['success'] = 'Profil mis à jour avec succès.';
        header('Location: ' . BASE_URL . '/profile/edit');
        exit;
    }

    public function showPassword() {
        ob_start();
        ?>
        <div class="page-content">
            <div class="container">
                <div class="page-header fade-in">
                    <div class="page-header-left">
                        <a href="<?= BASE_URL ?>/profile" class="back-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Retour
                        </a>
                        <div class="page-header-title">
                            <h1>Changer mon mot de passe</h1>
                        </div>
                    </div>
                </div>

                <div class="card fade-in" style="max-width: 500px; margin: 0 auto;">

                    <?php if (isset($_SESSION['flash']['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']['success']) ?></div>
                        <?php unset($_SESSION['flash']['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash']['error'])): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash']['error']) ?></div>
                        <?php unset($_SESSION['flash']['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/profile/password">
                        <?= CSRF::tokenField() ?>
                        
                        <div class="form-group">
                            <label for="current_password">Ancien mot de passe</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Changer le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    public function updatePassword() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/profile/password');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash']['error'] = 'Tous les champs sont obligatoires.';
            header('Location: ' . BASE_URL . '/profile/password');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash']['error'] = 'Les nouveaux mots de passe ne correspondent pas.';
            header('Location: ' . BASE_URL . '/profile/password');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash']['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            header('Location: ' . BASE_URL . '/profile/password');
            exit;
        }

        $user = $this->auth->getUser($this->auth->getUserId());

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash']['error'] = 'L\'ancien mot de passe est incorrect.';
            header('Location: ' . BASE_URL . '/profile/password');
            exit;
        }

        $this->auth->updatePassword($this->auth->getUserId(), $newPassword);
        $_SESSION['flash']['success'] = 'Mot de passe changé avec succès.';
        header('Location: ' . BASE_URL . '/profile/password');
        exit;
    }

    private function renderLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Minecraft Panel</title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/includes/styles.css">
            <script>const BASE_URL = '<?= BASE_URL ?>';</script>
            <style>
                body { margin: 0; }
            </style>
        </head>
        <body>
            <?php include BASE_PATH . '/views/layout/navbar.php'; ?>
            <?= $content ?>
            <?php include BASE_PATH . '/views/layout/footer.php'; ?>
        </body>
        </html>
        <?php
    }
}
