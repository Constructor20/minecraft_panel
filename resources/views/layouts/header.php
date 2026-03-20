<?php
/**
 * Layout principal
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Minecraft Panel' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="/dashboard">Minecraft Panel</a>
        </div>
        <div class="nav-links">
            <a href="/dashboard">Dashboard</a>
            <a href="/servers">Serveurs</a>
            <a href="/profile">Profil</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="/admin">Admin</a>
            <?php endif; ?>
            <a href="/logout" class="btn-logout">Déconnexion</a>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
