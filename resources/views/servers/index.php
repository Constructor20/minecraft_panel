<?php
$title = 'Serveurs';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<div class="page-header">
    <h1>Mes Serveurs</h1>
    <a href="/servers/create" class="btn btn-primary">+ Nouveau serveur</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Port</th>
            <th>Mémoire</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($servers as $server): ?>
        <tr>
            <td><?= htmlspecialchars($server['name']) ?></td>
            <td><?= htmlspecialchars($server['description']) ?></td>
            <td><?= $server['port'] ?></td>
            <td><?= $server['memory'] ?> MB</td>
            <td>
                <span class="status status-<?= $server['status'] ?>">
                    <?= $server['status'] ?>
                </span>
            </td>
            <td class="actions">
                <?php if ($server['status'] === 'stopped'): ?>
                    <a href="/servers/<?= $server['id'] ?>/start" class="btn btn-success btn-sm">Démarrer</a>
                <?php else: ?>
                    <a href="/servers/<?= $server['id'] ?>/stop" class="btn btn-danger btn-sm">Arrêter</a>
                <?php endif; ?>
                <a href="/servers/<?= $server['id'] ?>" class="btn btn-secondary btn-sm">Voir</a>
                <a href="/servers/<?= $server['id'] ?>/edit" class="btn btn-secondary btn-sm">Modifier</a>
                <a href="/servers/<?= $server['id'] ?>/delete" class="btn btn-danger btn-sm" 
                   onclick="return confirm('Supprimer ce serveur ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
