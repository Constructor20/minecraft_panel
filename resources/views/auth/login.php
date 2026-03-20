<?php
$title = 'Connexion';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<div class="login-container">
    <h1>Connexion</h1>

    <form action="/login" method="POST" class="form-login">
        <div class="form-group">
            <label for="username">Pseudo ou Email</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <p class="login-help">
        Comptes de test :<br>
        - admin / admin123 (admin)<br>
        - chris / chris123 (user)
    </p>
</div>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
