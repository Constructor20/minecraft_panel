<?php
$title = 'Nouveau Serveur';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<h1>Nouveau Serveur</h1>

<form action="/servers" method="POST" class="form">
    <div class="form-group">
        <label for="name">Nom du serveur</label>
        <input type="text" name="name" id="name" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="3"></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="port">Port</label>
            <input type="number" name="port" id="port" value="25565" required>
        </div>

        <div class="form-group">
            <label for="memory">Mémoire (MB)</label>
            <input type="number" name="memory" id="memory" value="2048" required>
        </div>
    </div>

    <div class="form-actions">
        <a href="/servers" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Créer</button>
    </div>
</form>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
