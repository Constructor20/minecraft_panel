<?php
global $auth;
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= BASE_URL ?>/servers" class="navbar-brand">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
            Minecraft Panel
        </a>

        <ul class="navbar-nav">
            <li><a href="<?= BASE_URL ?>/servers" class="<?= strpos($_SERVER['REQUEST_URI'], '/servers') !== false ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                Serveurs
            </a></li>

            <?php if ($auth->isAdmin()): ?>
            <li><a href="<?= BASE_URL ?>/admin" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Administration
            </a></li>
            <?php endif; ?>

            <li><a href="<?= BASE_URL ?>/profile" class="<?= strpos($_SERVER['REQUEST_URI'], '/profile') !== false ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Profil
            </a></li>

            <div class="navbar-user">
                <div class="user-avatar"><?= strtoupper(substr($auth->getUsername(), 0, 2)) ?></div>
                <a href="<?= BASE_URL ?>/logout">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </a>
            </div>
        </ul>
    </div>
</nav>

<style>
.navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(100, 116, 139, 0.2);
    padding: 14px 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.navbar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar-brand {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--accent-secondary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.navbar-brand svg { width: 24px; height: 24px; }

.navbar-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.navbar-nav a {
    color: var(--text-secondary);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
}

.navbar-nav a svg { width: 16px; height: 16px; }

.navbar-nav a:hover, .navbar-nav a.active {
    color: var(--accent-secondary);
    background: rgba(96, 165, 250, 0.1);
}

.navbar-nav a.active { color: var(--accent-primary); }

.navbar-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-left: 12px;
    border-left: 1px solid var(--border-color);
    margin-left: 12px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-tertiary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

@media (max-width: 768px) {
    .navbar-inner { flex-direction: column; gap: 12px; }
    .navbar-nav { flex-wrap: wrap; justify-content: center; }
    .navbar-user { border-left: none; margin-left: 0; padding-left: 0; padding-top: 12px; border-top: 1px solid var(--border-color); }
}
</style>
