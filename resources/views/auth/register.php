<?php
$title = 'Inscription';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<div class="login-container">
    <h1>Créer un compte</h1>

    <form action="/register" method="POST" class="form-login">
        <div class="form-group">
            <label for="username">Pseudo</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>

    <p class="login-help">
        Déjà un compte ? <a href="/login">Se connecter</a>
    </p>
</div>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
