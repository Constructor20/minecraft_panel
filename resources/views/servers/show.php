<?php
$title = $server['name'];
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<div class="server-detail">
    <h1><?= htmlspecialchars($server['name']) ?></h1>

    <div class="server-info">
        <p><strong>Description :</strong> <?= htmlspecialchars($server['description']) ?></p>
        <p><strong>Port :</strong> <?= $server['port'] ?></p>
        <p><strong>Mémoire :</strong> <?= $server['memory'] ?> MB</p>
        <p><strong>Statut :</strong> 
            <span class="status status-<?= $server['status'] ?>"><?= $server['status'] ?></span>
        </p>
    </div>

    <div class="server-actions">
        <?php if ($server['status'] === 'stopped'): ?>
            <a href="/servers/<?= $server['id'] ?>/start" class="btn btn-success btn-lg">Démarrer</a>
        <?php else: ?>
            <a href="/servers/<?= $server['id'] ?>/stop" class="btn btn-danger btn-lg">Arrêter</a>
        <?php endif; ?>

        <a href="/servers/<?= $server['id'] ?>/edit" class="btn btn-secondary">Modifier</a>
        <a href="/servers" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
