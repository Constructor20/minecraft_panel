<?php
$title = 'Mon Profil';
require ROOT_PATH . '/resources/views/layouts/header.php';
?>

<div class="profile-container">
    <h1>Mon Profil</h1>

    <div class="profile-info">
        <p><strong>Pseudo :</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($_SESSION['role']) ?></p>
    </div>
</div>

<?php require ROOT_PATH . '/resources/views/layouts/footer.php'; ?>
