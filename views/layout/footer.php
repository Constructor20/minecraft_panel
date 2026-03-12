<?php
global $auth;
?>
<footer class="site-footer">
    <div class="footer-inner">
        <p>&copy; <?= date('Y'); ?> <strong>Minecraft Panel</strong> — Tous droits réservés</p>
    </div>
</footer>

<style>
.site-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
    border-top: 1px solid rgba(100, 116, 139, 0.2);
    padding: 14px 0;
    z-index: 99;
}

.footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.85rem;
}

.footer-inner strong { color: var(--accent-secondary); }
</style>
