<?php
$title = 'Dashboard';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</h1>

<div class="dashboard-cards">
    <div class="card">
        <h3>Mes serveurs</h3>
        <p>Gérez vos serveurs Minecraft</p>
        <a href="/servers" class="btn btn-primary">Voir les serveurs</a>
    </div>

    <div class="card">
        <h3>Mon profil</h3>
        <p>Modifier mes informations</p>
        <a href="/profile" class="btn btn-secondary">Mon profil</a>
    </div>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="card card-admin">
        <h3>Administration</h3>
        <p>Gérer les utilisateurs</p>
        <a href="/admin" class="btn btn-admin">Panel Admin</a>
    </div>
    <?php endif; ?>
</div>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
