<?php
class AuthController {
    private $db;
    private $auth;

    public function __construct(Database $db, Auth $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function showLogin() {
        if ($this->auth->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
        
        ob_start();
        ?>
        <div class="auth-container fade-in">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Minecraft Panel</h1>
                    <p>Gestion de serveur</p>
                </div>

                <?php if (isset($_SESSION['flash']['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']['success']) ?></div>
                    <?php unset($_SESSION['flash']['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash']['error']) ?></div>
                    <?php unset($_SESSION['flash']['error']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login">
                    <?= CSRF::tokenField() ?>
                    
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur ou Email</label>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Entrez votre identifiant" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="password-toggle">
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Entrez votre mot de passe" required autocomplete="current-password">
                            <button type="button" onclick="togglePassword()">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </form>

                <div class="form-footer">
                    <p><a href="<?= BASE_URL ?>/forgot" class="forgot-link">Mot de passe oublié ?</a></p>
                    <p>Pas encore de compte ? <a href="<?= BASE_URL ?>/register">Créer un compte</a></p>
                </div>
            </div>
        </div>

        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const icon = document.getElementById('eye-icon');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                } else {
                    input.type = 'password';
                    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                }
            }
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderAuthLayout($content);
    }

    public function login() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['flash']['error'] = 'Veuillez remplir tous les champs.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->verifyUser($username, $password);

        if ($user) {
            $this->auth->login($user['id'], $user['username'], $user['email']);
            header('Location: ' . BASE_URL . '/profile');
            exit;
        } else {
            $_SESSION['flash']['error'] = 'Identifiants incorrects.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function showRegister() {
        if ($this->auth->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
        
        ob_start();
        ?>
        <div class="auth-container fade-in">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Créer un compte</h1>
                    <p>Rejoignez Minecraft Panel</p>
                </div>

                <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash']['error']) ?></div>
                    <?php unset($_SESSION['flash']['error']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register">
                    <?= CSRF::tokenField() ?>
                    
                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               placeholder="exemple@email.com" required autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Choix d'un identifiant" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="password-toggle">
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Minimum 6 caractères" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('password')">
                                <svg id="eye-icon-password" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <p class="requirements">Minimum 6 caractères</p>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="password-toggle">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                   placeholder="Répétez votre mot de passe" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('confirm_password')">
                                <svg id="eye-icon-confirm_password" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
                </form>

                <div class="form-footer">
                    <p>Déjà inscrit ? <a href="<?= BASE_URL ?>/login">Se connecter</a></p>
                </div>
            </div>
        </div>

        <script>
            function togglePassword(inputId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById('eye-icon-' + inputId);
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                } else {
                    input.type = 'password';
                    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                }
            }
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderAuthLayout($content);
    }

    public function register() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse e-mail invalide.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($this->auth->userExists($username, $email)) {
            $errors[] = 'Ce nom d\'utilisateur ou cette adresse e-mail est déjà utilisé.';
        }

        if (!empty($errors)) {
            $_SESSION['flash']['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $this->auth->createUser($username, $email, $password);
        $_SESSION['flash']['success'] = 'Compte créé avec succès. Veuillez vous connecter.';
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function logout() {
        $this->auth->logout();
    }

    public function showForgot() {
        ob_start();
        ?>
        <div class="auth-container fade-in">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Mot de passe oublié</h1>
                    <p>Réinitialisez votre mot de passe</p>
                </div>

                <?php if (isset($_SESSION['flash']['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']['success']) ?></div>
                    <?php unset($_SESSION['flash']['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash']['error']) ?></div>
                    <?php unset($_SESSION['flash']['error']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/forgot">
                    <?= CSRF::tokenField() ?>
                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Envoyer le lien</button>
                </form>

                <div class="form-footer">
                    <p><a href="<?= BASE_URL ?>/login">Retour à la connexion</a></p>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $this->renderAuthLayout($content);
    }

    public function forgot() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/forgot');
            exit;
        }
        $_SESSION['flash']['success'] = 'Si ce compte existe, un email a été envoyé.';
        header('Location: ' . BASE_URL . '/forgot');
        exit;
    }

    public function showReset() {
        ob_start();
        ?>
        <div class="auth-container fade-in">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Nouveau mot de passe</h1>
                </div>
                <form method="POST" action="<?= BASE_URL ?>/reset">
                    <?= CSRF::tokenField() ?>
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Réinitialiser</button>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $this->renderAuthLayout($content);
    }

    public function reset() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/reset');
            exit;
        }
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    private function renderAuthLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $title ?? 'Minecraft Panel' ?></title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/includes/styles.css">
            <script>
                const BASE_URL = '<?= BASE_URL ?>';
                const CSRF_TOKEN = '<?= CSRF::get() ?>';
            </script>
            <style>
                body {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
                }
                .auth-container { width: 100%; max-width: 420px; padding: 40px; }
                .auth-card {
                    background: rgba(30, 41, 59, 0.9);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(59, 130, 246, 0.2);
                    border-radius: 16px;
                    padding: 40px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                }
                .auth-logo { text-align: center; margin-bottom: 30px; }
                .auth-logo h1 {
                    font-size: 1.8rem;
                    background: linear-gradient(135deg, #60a5fa, #38bdf8);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin: 0;
                }
                .auth-logo p { color: var(--text-muted); font-size: 0.9rem; margin-top: 8px; }
                .form-footer { text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); }
                .form-footer p { color: var(--text-muted); font-size: 0.9rem; margin: 8px 0; }
                .form-footer .forgot-link { color: var(--accent-tertiary); font-weight: 500; }
                .form-footer .forgot-link:hover { color: var(--accent-secondary); text-decoration: underline; }
                .password-toggle { position: relative; }
                .password-toggle input { padding-right: 45px; }
                .password-toggle button {
                    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                    background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;
                }
                .password-toggle button:hover { color: var(--accent-secondary); }
                .requirements { font-size: 0.8rem; color: var(--text-muted); margin-top: 8px; }
            </style>
        </head>
        <body>
        <?= $content ?>
        </body>
        </html>
        <?php
    }
}
