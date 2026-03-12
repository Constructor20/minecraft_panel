<?php
class AdminController {
    private $db;
    private $auth;

    public function __construct(Database $db, Auth $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $servers = $this->db->fetchAll("SELECT * FROM servers");
        $users = $this->db->fetchAll("SELECT * FROM users");

        ob_start();
        ?>
        <div class="page-content">
            <div class="container">
                <div class="page-header fade-in">
                    <div class="page-header-left">
                        <a href="<?= BASE_URL ?>/servers" class="back-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Retour
                        </a>
                        <div class="page-header-title">
                            <h1>Administration</h1>
                            <span class="subtitle">Gestion des serveurs et permissions</span>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['flash']['success'])): ?>
                    <div class="alert alert-success" style="max-width: 600px; margin: 0 auto 20px;"><?= htmlspecialchars($_SESSION['flash']['success']) ?></div>
                    <?php unset($_SESSION['flash']['success']); ?>
                <?php endif; ?>

                <div class="card fade-in">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        <h2>Ajouter un serveur</h2>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/admin/servers/add">
                        <?= CSRF::tokenField() ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                            <div class="form-group">
                                <label>Nom du serveur</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Chemin</label>
                                <input type="text" name="path" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="vanilla">Vanilla</option>
                                    <option value="spigot">Spigot</option>
                                    <option value="paper">Paper</option>
                                    <option value="forge">Forge</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Port</label>
                                <input type="number" name="port" class="form-control" value="25565" required>
                            </div>
                            <div class="form-group">
                                <label>Max joueurs</label>
                                <input type="number" name="max_players" class="form-control" value="20" required>
                            </div>
                            <div class="form-group">
                                <label>Arguments Java</label>
                                <input type="text" name="java_args" class="form-control" value="-Xmx2G -Xms1G">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter le serveur</button>
                    </form>
                </div>

                <div class="card fade-in" style="margin-bottom: 24px;">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <h2>Modifier les serveurs</h2>
                    </div>
                    <?php foreach ($servers as $srv): ?>
                        <button class="accordion" onclick="togglePanel('server-<?= $srv['id'] ?>')">
                            <span><?= htmlspecialchars($srv['name']) ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="panel" id="server-<?= $srv['id'] ?>">
                            <form method="POST" action="<?= BASE_URL ?>/admin/servers/update">
                                <?= CSRF::tokenField() ?>
                                <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                                    <div class="form-group">
                                        <label>Nom</label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($srv['name']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Chemin</label>
                                        <input type="text" name="path" class="form-control" value="<?= htmlspecialchars($srv['path']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="type" class="form-control">
                                            <option value="vanilla" <?= $srv['type']=="vanilla"?"selected":"" ?>>Vanilla</option>
                                            <option value="spigot" <?= $srv['type']=="spigot"?"selected":"" ?>>Spigot</option>
                                            <option value="paper" <?= $srv['type']=="paper"?"selected":"" ?>>Paper</option>
                                            <option value="forge" <?= $srv['type']=="forge"?"selected":"" ?>>Forge</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input type="number" name="port" class="form-control" value="<?= $srv['port'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Max joueurs</label>
                                        <input type="number" name="max_players" class="form-control" value="<?= $srv['max_players'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Arguments Java</label>
                                        <input type="text" name="java_args" class="form-control" value="<?= htmlspecialchars($srv['java_args']) ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteServer(<?= $srv['id'] ?>, '<?= htmlspecialchars($srv['name']) ?>')">Supprimer</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card fade-in" style="margin-bottom: 24px;">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        <h2>Permissions utilisateurs</h2>
                    </div>
                    <?php foreach ($users as $user): ?>
                        <button class="accordion" onclick="togglePanel('user-<?= $user['id'] ?>')">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: middle;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?= htmlspecialchars($user['username']) ?>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="panel" id="user-<?= $user['id'] ?>">
                            <?php foreach ($servers as $srv): ?>
                                <?php $perm = $this->getUserPermission($user['id'], $srv['id']); ?>
                                <form method="POST" action="<?= BASE_URL ?>/admin/permissions/update" class="permission-form">
                                    <?= CSRF::tokenField() ?>
                                    <div class="permission-header">
                                        <div class="permission-server">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                            <span><?= htmlspecialchars($srv['name']) ?></span>
                                        </div>
                                        <span class="badge <?= $perm['can_view'] ? 'badge-success' : 'badge-danger' ?>"><?= $perm['can_view'] ? 'Actif' : 'Inactif' ?></span>
                                    </div>
                                    <div class="permission-toggles">
                                        <div class="toggle-item <?= $perm['can_view'] ? 'active' : '' ?>">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="can_view" <?= $perm['can_view'] ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="toggle-info">
                                                <span class="toggle-label">Voir</span>
                                                <span class="toggle-desc">Accéder au serveur</span>
                                            </div>
                                        </div>
                                        <div class="toggle-item <?= $perm['can_start'] ? 'active' : '' ?>">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="can_start" <?= $perm['can_start'] ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="toggle-info">
                                                <span class="toggle-label">Démarrer</span>
                                                <span class="toggle-desc">Lancer le serveur</span>
                                            </div>
                                        </div>
                                        <div class="toggle-item <?= $perm['can_stop'] ? 'active' : '' ?>">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="can_stop" <?= $perm['can_stop'] ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="toggle-info">
                                                <span class="toggle-label">Arrêter</span>
                                                <span class="toggle-desc">Arrêter le serveur</span>
                                            </div>
                                        </div>
                                        <div class="toggle-item <?= $perm['can_console'] ? 'active' : '' ?>">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="can_console" <?= $perm['can_console'] ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="toggle-info">
                                                <span class="toggle-label">Console</span>
                                                <span class="toggle-desc">Envoyer des commandes</span>
                                            </div>
                                        </div>
                                        <div class="toggle-item <?= $perm['can_files'] ? 'active' : '' ?>">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="can_files" <?= $perm['can_files'] ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="toggle-info">
                                                <span class="toggle-label">Fichiers</span>
                                                <span class="toggle-desc">Gérer les fichiers</span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                        Sauvegarder
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            function togglePanel(id) {
                const panel = document.getElementById(id);
                panel.classList.toggle('open');
            }

            document.querySelectorAll('.toggle-switch input').forEach(input => {
                input.addEventListener('change', function() {
                    const toggleItem = this.closest('.toggle-item');
                    if (this.checked) {
                        toggleItem.classList.add('active');
                    } else {
                        toggleItem.classList.remove('active');
                    }
                });
            });

            function deleteServer(serverId, serverName) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer le serveur "' + serverName + '" ?\n\nCette action est irréversible.')) {
                    return;
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>/admin/servers/delete';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= CSRF::get() ?>';
                
                const serverIdInput = document.createElement('input');
                serverIdInput.type = 'hidden';
                serverIdInput.name = 'server_id';
                serverIdInput.value = serverId;
                
                form.appendChild(csrfInput);
                form.appendChild(serverIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    private function getUserPermission($userId, $serverId) {
        $perm = $this->db->fetch("SELECT * FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $serverId]);
        return $perm ?: ['can_start' => 0, 'can_stop' => 0, 'can_view' => 0, 'can_console' => 0, 'can_files' => 0];
    }

    public function addServer() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $this->db->query(
            "INSERT INTO servers (name, path, type, port, max_players, java_args) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $_POST['name'],
                $_POST['path'],
                $_POST['type'],
                intval($_POST['port']),
                intval($_POST['max_players']),
                $_POST['java_args'] ?? '-Xmx2G -Xms1G'
            ]
        );

        $_SESSION['flash']['success'] = 'Serveur ajouté avec succès.';
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    public function updateServer() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $this->db->query(
            "UPDATE servers SET name = ?, path = ?, type = ?, port = ?, max_players = ?, java_args = ? WHERE id = ?",
            [
                $_POST['name'],
                $_POST['path'],
                $_POST['type'],
                intval($_POST['port']),
                intval($_POST['max_players']),
                $_POST['java_args'],
                intval($_POST['server_id'])
            ]
        );

        $_SESSION['flash']['success'] = 'Serveur mis à jour.';
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    public function updatePermissions() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $userId = intval($_POST['user_id']);
        $serverId = intval($_POST['server_id']);
        $canStart = isset($_POST['can_start']) ? 1 : 0;
        $canStop = isset($_POST['can_stop']) ? 1 : 0;
        $canView = isset($_POST['can_view']) ? 1 : 0;
        $canConsole = isset($_POST['can_console']) ? 1 : 0;
        $canFiles = isset($_POST['can_files']) ? 1 : 0;

        $exists = $this->db->fetch("SELECT id FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $serverId]);

        if ($exists) {
            $this->db->query(
                "UPDATE permissions SET can_start = ?, can_stop = ?, can_view = ?, can_console = ?, can_files = ? WHERE user_id = ? AND server_id = ?",
                [$canStart, $canStop, $canView, $canConsole, $canFiles, $userId, $serverId]
            );
        } else {
            $this->db->query(
                "INSERT INTO permissions (user_id, server_id, can_start, can_stop, can_view, can_console, can_files) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $serverId, $canStart, $canStop, $canView, $canConsole, $canFiles]
            );
        }

        $_SESSION['flash']['success'] = 'Permissions mises à jour.';
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    public function deleteServer() {
        if (!CSRF::validateRequest()) {
            $_SESSION['flash']['error'] = 'Token de sécurité invalide.';
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $serverId = intval($_POST['server_id'] ?? 0);
        
        if ($serverId <= 0) {
            $_SESSION['flash']['error'] = 'ID de serveur invalide.';
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }

        $this->db->query("DELETE FROM permissions WHERE server_id = ?", [$serverId]);
        $this->db->query("DELETE FROM servers WHERE id = ?", [$serverId]);

        $_SESSION['flash']['success'] = 'Serveur supprimé avec succès.';
        header('Location: ' . BASE_URL . '/admin');
        exit;
    }

    private function renderLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Minecraft Panel - Administration</title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/includes/styles.css">
            <script>const BASE_URL = '<?= BASE_URL ?>';</script>
            <style>body { margin: 0; }</style>
        </head>
        <body>
            <?php include BASE_PATH . '/views/layout/navbar.php'; ?>
            <?= $content ?>
            <?php include BASE_PATH . '/views/layout/footer.php'; ?>
        </body>
        </html>
        <?php
    }
}
