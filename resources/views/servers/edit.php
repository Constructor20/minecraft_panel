<?php
$title = 'Modifier Serveur';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<h1>Modifier : <?= htmlspecialchars($server['name']) ?></h1>

<form action="/servers/<?= $server['id'] ?>/update" method="POST" class="form">
    <div class="form-group">
        <label for="name">Nom du serveur</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($server['name']) ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="3"><?= htmlspecialchars($server['description']) ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="port">Port</label>
            <input type="number" name="port" id="port" value="<?= $server['port'] ?>" required>
        </div>

        <div class="form-group">
            <label for="memory">Mémoire (MB)</label>
            <input type="number" name="memory" id="memory" value="<?= $server['memory'] ?>" required>
        </div>
    </div>

    <div class="form-actions">
        <a href="/servers" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
