<?php
require_once BASE_PATH . '/core/ServerService.php';

class ServerController {
    private $db;
    private $auth;
    private $serverService;

    public function __construct(Database $db, Auth $auth) {
        $this->db = $db;
        $this->auth = $auth;
        $this->serverService = new ServerService($db);
    }

    public function index() {
        $userId = $this->auth->getUserId();

        if ($userId == 1) {
            $servers = $this->db->fetchAll("SELECT * FROM servers");
        } else {
            $servers = $this->db->fetchAll("
                SELECT s.* 
                FROM servers s
                INNER JOIN permissions p ON p.server_id = s.id
                WHERE p.user_id = ? AND p.can_view = 1
            ", [$userId]);
        }

        ob_start();
        ?>
        <div class="page-content">
            <div class="container">
                <div class="page-header fade-in">
                    <h1>Mes Serveurs</h1>
                </div>

                <?php if (empty($servers)): ?>
                    <div class="card empty-state fade-in">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                        <h3>Aucun serveur disponible</h3>
                        <p>Vous n'avez pas accès à des serveurs pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="server-grid">
                        <?php foreach ($servers as $srv): ?>
                            <div class="server-card fade-in" id="server-<?= $srv['id'] ?>" onclick="window.location.href='<?= BASE_URL ?>/servers/console?id=<?= $srv['id'] ?>'">
                                <div class="server-header">
                                    <span class="server-name"><?= htmlspecialchars($srv['name']) ?></span>
                                    <span class="server-status offline" id="status-<?= $srv['id'] ?>">
                                        <span class="status-indicator status-offline"></span>
                                        <span id="status-text-<?= $srv['id'] ?>">Hors ligne</span>
                                    </span>
                                </div>
                                <div class="server-info">
                                    <span class="server-info-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                        <span id="players-<?= $srv['id'] ?>">--/--</span>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        async function updateServerStatus(serverId) {
            try {
                const response = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId);
                const data = await response.json();

                const statusEl = document.getElementById('status-' + serverId);
                const statusText = document.getElementById('status-text-' + serverId);
                const playersEl = document.getElementById('players-' + serverId);

                if (!statusEl || !statusText || !playersEl) return;

                if (data.status === "ok") {
                    if (data.starting) {
                        statusEl.classList.remove('online');
                        statusEl.classList.add('offline');
                        statusEl.querySelector('.status-indicator').classList.remove('status-online');
                        statusEl.querySelector('.status-indicator').classList.add('status-starting');
                        statusText.textContent = 'Demarrage...';
                    } else if (data.stopping) {
                        statusEl.classList.remove('online');
                        statusEl.classList.add('offline');
                        statusEl.querySelector('.status-indicator').classList.remove('status-online');
                        statusEl.querySelector('.status-indicator').classList.add('status-stopping');
                        statusText.textContent = 'Arret...';
                    } else if (data.running && data.online) {
                        statusEl.classList.remove('offline');
                        statusEl.classList.add('online');
                        statusEl.querySelector('.status-indicator').classList.remove('status-offline', 'status-starting', 'status-stopping');
                        statusEl.querySelector('.status-indicator').classList.add('status-online');
                        statusText.textContent = 'En ligne';
                        playersEl.textContent = (data.current_players || 0) + '/' + (data.max_players || 0);
                    } else if (data.running && !data.online) {
                        statusEl.classList.remove('online');
                        statusEl.classList.add('offline');
                        statusEl.querySelector('.status-indicator').classList.remove('status-online');
                        statusEl.querySelector('.status-indicator').classList.add('status-starting');
                        statusText.textContent = 'Hors ligne';
                        playersEl.textContent = '0/' + (data.max_players || 0);
                    } else {
                        statusEl.classList.remove('online');
                        statusEl.classList.add('offline');
                        statusEl.querySelector('.status-indicator').classList.remove('status-online', 'status-starting', 'status-stopping');
                        statusEl.querySelector('.status-indicator').classList.add('status-offline');
                        statusText.textContent = 'Hors ligne';
                        playersEl.textContent = '--/--';
                    }
                }
            } catch (e) {
                console.warn('Error fetching server ' + serverId + ' status:', e);
            }
        }

        function refreshAllServers() {
            const serverIds = Array.from(document.querySelectorAll('[id^="server-"]')).map(el => el.id.replace('server-', ''));
            serverIds.forEach(id => updateServerStatus(id));
        }

        refreshAllServers();
        setInterval(refreshAllServers, 1000);
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    public function console() {
        if (!isset($_GET['id'])) {
            header('Location: ' . BASE_URL . '/servers');
            exit;
        }

        $serverId = intval($_GET['id']);
        $userId = $this->auth->getUserId();

        if ($userId == 1) {
            $server = $this->db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
            $permissions = ["can_view" => 1, "can_start" => 1, "can_stop" => 1, "can_console" => 1, "can_files" => 1];
        } else {
            $server = $this->db->fetch("
                SELECT s.*, p.can_view, p.can_start, p.can_stop, p.can_console, p.can_files
                FROM servers s
                INNER JOIN permissions p ON p.server_id = s.id
                WHERE s.id = ? AND p.user_id = ?
            ", [$serverId, $userId]);

            if (!$server || !$server['can_view']) {
                http_response_code(403);
                echo json_encode(['error' => 'Accès refusé']);
                exit;
            }
            $permissions = [
                "can_view" => $server["can_view"],
                "can_start" => $server["can_start"],
                "can_stop" => $server["can_stop"],
                "can_console" => $server["can_console"],
                "can_files" => $server["can_files"] ?? 0
            ];
        }

        if (!$server) {
            header('Location: ' . BASE_URL . '/servers');
            exit;
        }

        $players = '0/' . ($server['max_players'] ?? 20);

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
                            <h1><?= htmlspecialchars($server['name']) ?></h1>
                            <span class="subtitle">
                                <span class="badge badge-danger" id="initial-status">
                                    <span class="status-indicator status-offline"></span>
                                    Hors ligne
                                </span>
                            </span>
                        </div>
                    </div>
                    <?php if ($permissions["can_start"] || $permissions["can_stop"] || $permissions["can_files"]): ?>
                    <div class="page-header-actions">
                        <?php if ($permissions["can_start"]): ?>
                            <button class="btn btn-success btn-sm" onclick="sendAction('start')">Démarrer</button>
                        <?php endif; ?>
                        <?php if ($permissions["can_stop"]): ?>
                            <button class="btn btn-danger btn-sm" onclick="sendAction('stop')">Arrêter</button>
                        <?php endif; ?>
                        <?php if ($permissions["can_files"]): ?>
                            <a href="<?= BASE_URL ?>/servers/files?id=<?= $serverId ?>" class="btn btn-secondary btn-sm">Fichiers</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card fade-in">

                    <div id="action-message" class="action-message"></div>

                    <div class="server-stats">
                        <div class="stat-box">
                            <div class="label">Joueurs</div>
                            <div class="value" id="players-count"><?= $players ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="label">CPU</div>
                            <div class="value" id="cpu-value">0%</div>
                            <div class="progress-bar"><div class="progress-fill progress-low" id="cpu-bar" style="width: 0%"></div></div>
                        </div>
                        <div class="stat-box">
                            <div class="label">RAM</div>
                            <div class="value" id="ram-value">0%</div>
                            <div class="progress-bar"><div class="progress-fill progress-low" id="ram-bar" style="width: 0%"></div></div>
                        </div>
                        <div class="stat-box">
                            <div class="label">Disque</div>
                            <div class="value" id="disk-value">0%</div>
                            <div class="progress-bar"><div class="progress-fill progress-low" id="disk-bar" style="width: 0%"></div></div>
                        </div>
                        <div class="stat-box">
                            <div class="label">TPS</div>
                            <div class="value" id="tps-value" style="font-size: 1.4rem; font-weight: bold;">--</div>
                            <div class="label" id="tps-label" style="font-size: 0.7rem; color: var(--text-muted);">Tick/s</div>
                        </div>
                    </div>

                    <?php if ($permissions["can_console"]): ?>
                    <div class="console-wrapper">
                        <div class="console-output" id="console"></div>
                        <div class="console-input-wrapper">
                            <input type="text" id="command" placeholder="Entrer une commande..." onkeypress="if(event.key==='Enter')sendCommand()">
                            <button class="btn btn-success btn-sm" onclick="sendCommand()">Envoyer</button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($userId == 1): ?>
                    <div style="display: flex; gap: 10px; padding: 12px 16px; border-top: 1px solid var(--border-color); background: rgba(15, 23, 42, 0.3);">
                        <button class="btn btn-warning btn-sm" id="services-btn" onclick="stopApiService()">⚙️ Services</button>
                        <button class="btn btn-danger btn-sm" id="shutdown-btn" onclick="showShutdownConfirm()">🔴 Éteindre PC</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            const serverId = <?= $serverId ?>;
            const consoleDiv = document.getElementById('console');
            let autoScrollEnabled = true;
            let allLogs = [];
            let displayedLogCount = 0;
            let lastLogCount = 0;
            const INITIAL_LINES = 500;
            const LOAD_MORE_LINES = 500;

            <?php if ($permissions["can_console"]): ?>
            if (consoleDiv) {
                let scrollTimeout;
                consoleDiv.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    const nearBottom = consoleDiv.scrollHeight - consoleDiv.scrollTop - consoleDiv.clientHeight < 150;
                    const nearTop = consoleDiv.scrollTop < 300;
                    const scrollProgress = consoleDiv.scrollTop / consoleDiv.scrollHeight;
                    
                    if (nearTop && allLogs.length > displayedLogCount) {
                        scrollTimeout = setTimeout(() => {
                            loadMoreLogs(true);
                        }, 200);
                    } else if (scrollProgress < 0.15 && allLogs.length > displayedLogCount) {
                        scrollTimeout = setTimeout(() => {
                            loadMoreLogs(true);
                        }, 300);
                    }
                    
                    updateLoadMoreIndicator();
                    
                    if (nearBottom) {
                        autoScrollEnabled = true;
                        hideScrollButton();
                    } else if (!nearTop) {
                        autoScrollEnabled = false;
                        showScrollButton();
                    }
                });
            }
            <?php endif; ?>

            function getProgressClass(value) {
                if (value < 50) return 'progress-low';
                if (value < 80) return 'progress-medium';
                return 'progress-high';
            }

            function getTPSColor(tps) {
                if (tps >= 19.5) return '#22c55e';
                if (tps >= 18) return '#84cc16';
                if (tps >= 15) return '#facc15';
                if (tps >= 10) return '#f97316';
                return '#ef4444';
            }

            function getTPSLabel(tps) {
                if (tps >= 19.5) return 'Excellent';
                if (tps >= 18) return 'Bon';
                if (tps >= 15) return 'Moyen';
                if (tps >= 10) return 'Laggy';
                return 'Critique';
            }

            function formatLogLine(log) {
                const cleanLog = log.replace(/^\[Serveur [^\]]+\]\s*/, '');
                const withBreak = cleanLog.replace(/\[(\d{2}:\d{2}:\d{2})\]/g, '\n[$1]');
                return withBreak
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function showScrollButton() {
                let btn = document.getElementById('scroll-bottom-btn');
                if (!btn) {
                    btn = document.createElement('div');
                    btn.id = 'scroll-bottom-btn';
                    btn.innerHTML = '<button onclick="scrollToBottom()" style="position: fixed; bottom: 80px; right: 30px; padding: 10px 16px; background: var(--accent-primary); color: white; border: none; border-radius: 20px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 100; font-size: 0.85rem;">⬇️ Nouveaux logs</button>';
                    document.body.appendChild(btn);
                }
                btn.style.display = 'block';
            }

            function hideScrollButton() {
                const btn = document.getElementById('scroll-bottom-btn');
                if (btn) btn.style.display = 'none';
            }

            function scrollToBottom() {
                autoScrollEnabled = true;
                hideScrollButton();
                if (consoleDiv) {
                    consoleDiv.scrollTop = consoleDiv.scrollHeight;
                }
            }

            function updateLoadMoreIndicator() {
                const existing = document.getElementById('load-more-indicator');
                if (!consoleDiv) return;
                
                const remaining = allLogs.length - displayedLogCount;
                const hasMore = remaining > 0;
                
                if (hasMore) {
                    if (!existing) {
                        const indicator = document.createElement('div');
                        indicator.id = 'load-more-indicator';
                        indicator.style.cssText = 'text-align: center; padding: 12px 15px; background: linear-gradient(to bottom, var(--bg-secondary), transparent); position: absolute; top: 0; left: 0; right: 0; z-index: 10; cursor: pointer;';
                        indicator.innerHTML = '<span onclick="loadMoreLogs(true)" style="display: inline-block; background: var(--accent-primary); color: white; padding: 8px 20px; border-radius: 20px; font-size: 0.85rem; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">📜 Afficher plus (' + remaining + ')</span>';
                        consoleDiv.insertBefore(indicator, consoleDiv.firstChild);
                        consoleDiv.style.position = 'relative';
                    } else {
                        existing.innerHTML = '<span onclick="loadMoreLogs(true)" style="display: inline-block; background: var(--accent-primary); color: white; padding: 8px 20px; border-radius: 20px; font-size: 0.85rem; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">📜 Afficher plus (' + remaining + ')</span>';
                    }
                } else if (existing) {
                    existing.remove();
                }
            }

            function renderLogs(append = false, oldHeight = 0, oldScrollTop = 0, fromScroll = false) {
                if (!consoleDiv) return;
                
                if (!append) {
                    displayedLogCount = Math.min(allLogs.length, INITIAL_LINES);
                }
                
                const logsToShow = allLogs.slice(-displayedLogCount);
                
                if (append && !fromScroll) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = logsToShow.map(formatLogLine).join("");
                    const newContent = tempDiv.innerHTML;
                    
                    const existingIndicator = document.getElementById('load-more-indicator');
                    const existingSpinner = document.getElementById('load-more-spinner');
                    if (existingIndicator) {
                        existingIndicator.remove();
                    }
                    if (existingSpinner) {
                        existingSpinner.remove();
                    }
                    
                    consoleDiv.innerHTML = newContent;
                    
                    if (oldHeight > 0) {
                        const heightDiff = consoleDiv.scrollHeight - oldHeight;
                        consoleDiv.scrollTop = oldScrollTop + heightDiff;
                    }
                } else if (append && fromScroll) {
                    hideLoadMoreSpinner();
                } else {
                    consoleDiv.innerHTML = logsToShow.map(formatLogLine).join("");
                }
                
                updateLoadMoreIndicator();
                
                if (autoScrollEnabled) {
                    consoleDiv.scrollTop = consoleDiv.scrollHeight;
                } else if (fromScroll && oldHeight > 0) {
                    const heightDiff = consoleDiv.scrollHeight - oldHeight;
                    consoleDiv.scrollTop = oldScrollTop + heightDiff;
                } else {
                    showScrollButton();
                }
            }

            function loadMoreLogs(fromScroll = false) {
                const oldHeight = consoleDiv.scrollHeight;
                const oldScrollTop = consoleDiv.scrollTop;
                
                showLoadMoreSpinner();
                
                displayedLogCount = Math.min(allLogs.length, displayedLogCount + LOAD_MORE_LINES);
                renderLogs(true, oldHeight, oldScrollTop, fromScroll);
            }

            function showLoadMoreSpinner() {
                let spinner = document.getElementById('load-more-spinner');
                if (!spinner) {
                    spinner = document.createElement('div');
                    spinner.id = 'load-more-spinner';
                    spinner.style.cssText = 'text-align: center; padding: 20px; color: var(--text-muted); width: 100%;';
                    spinner.innerHTML = '<div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 20px;"><span style="display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite;"></span><span style="color: white; font-size: 0.85rem;">Chargement...</span></div><style>@keyframes spin { to { transform: rotate(360deg); } }</style>';
                    consoleDiv.insertBefore(spinner, consoleDiv.firstChild);
                }
                spinner.style.display = 'block';
            }

            function hideLoadMoreSpinner() {
                const spinner = document.getElementById('load-more-spinner');
                if (spinner) spinner.style.display = 'none';
            }

            async function fetchConsole() {
                try {
                    const response = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId + '&t=' + Date.now());
                    if (!response.ok) {
                        console.error('Response not ok:', response.status, response.statusText);
                        return;
                    }
                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e, 'Text:', text.substring(0, 200));
                        return;
                    }

                    console.log('Console data received:', data);

                    if (data.status !== "ok") {
                        console.error('API Error:', data.message || data);
                        const statusBadge = document.getElementById('initial-status');
                        if (statusBadge) {
                            statusBadge.textContent = 'Erreur: ' + (data.message || 'API error');
                        }
                        return;
                    }

                    if (data.disk !== undefined) data.disk_usage = data.disk;
                    if (data.temp !== undefined) data.temp_cpu = data.temp;

                    <?php if ($permissions["can_console"]): ?>
                    if (consoleDiv && data.logs && Array.isArray(data.logs)) {
                        const newLogs = data.logs;
                        const oldLen = allLogs.length;
                        const lastOldLog = allLogs.length > 0 ? allLogs[allLogs.length - 1] : null;
                        const lastNewLog = newLogs.length > 0 ? newLogs[newLogs.length - 1] : null;
                        
                        if (newLogs.length > oldLen || lastOldLog !== lastNewLog) {
                            allLogs = newLogs;
                            
                            const oldHeight = consoleDiv.scrollHeight;
                            const oldScrollTop = consoleDiv.scrollTop;
                            const wasAtBottom = oldLen === 0 || (consoleDiv.scrollHeight - oldScrollTop - consoleDiv.clientHeight < 50);
                            
                            if (autoScrollEnabled || wasAtBottom) {
                                displayedLogCount = Math.min(allLogs.length, INITIAL_LINES);
                                renderLogs(false);
                            } else {
                                const newCount = Math.min(allLogs.length, displayedLogCount + (newLogs.length - oldLen));
                                if (newCount > displayedLogCount) {
                                    displayedLogCount = newCount;
                                    renderLogs(true, oldHeight, oldScrollTop, true);
                                }
                            }
                            lastLogCount = newLogs.length;
                        }
                    }
                    <?php endif; ?>

                    const statusBadge = document.getElementById('initial-status');
                    if (statusBadge) {
                        if (data.starting) {
                            statusBadge.className = 'badge badge-warning';
                            statusBadge.innerHTML = '<span class="status-indicator status-starting"></span>Demarrage...';
                        } else if (data.stopping) {
                            statusBadge.className = 'badge badge-warning';
                            statusBadge.innerHTML = '<span class="status-indicator status-stopping"></span>Arret...';
                        } else if (data.running && data.online) {
                            statusBadge.className = 'badge badge-success';
                            statusBadge.innerHTML = '<span class="status-indicator status-online"></span>En ligne';
                        } else if (data.running && !data.online) {
                            statusBadge.className = 'badge badge-warning';
                            statusBadge.innerHTML = '<span class="status-indicator status-starting"></span>Chargement...';
                        } else {
                            statusBadge.className = 'badge badge-danger';
                            statusBadge.innerHTML = '<span class="status-indicator status-offline"></span>Hors ligne';
                        }
                    }

                    const playersEl = document.getElementById('players-count');
                    if (playersEl) {
                        playersEl.textContent = (data.current_players || 0) + '/' + (data.max_players || 0);
                    }

                    const cpuBar = document.getElementById('cpu-bar');
                    const ramBar = document.getElementById('ram-bar');
                    const diskBar = document.getElementById('disk-bar');
                    const cpuValue = document.getElementById('cpu-value');
                    const ramValue = document.getElementById('ram-value');
                    const diskValue = document.getElementById('disk-value');

                    if (cpuBar && data.cpu !== undefined) {
                        const cpu = Math.min(100, Math.max(0, data.cpu));
                        cpuBar.style.width = cpu + '%';
                        cpuBar.className = 'progress-fill ' + getProgressClass(cpu);
                        cpuValue.textContent = cpu.toFixed(1) + '%';
                    }

                    if (ramBar && data.ram !== undefined) {
                        const totalRam = 8192;
                        const ramPercent = (data.ram / totalRam) * 100;
                        const ram = Math.min(100, Math.max(0, ramPercent));
                        ramBar.style.width = ram + '%';
                        ramBar.className = 'progress-fill ' + getProgressClass(ram);
                        ramValue.textContent = data.ram.toFixed(0) + ' MB';
                    }

                    if (diskBar && data.disk_usage !== undefined) {
                        const disk = Math.min(100, Math.max(0, data.disk_usage));
                        diskBar.style.width = disk + '%';
                        diskBar.className = 'progress-fill ' + getProgressClass(disk);
                        diskValue.textContent = disk + '%';
                    }

                    // TPS - try multiple possible field names
                    const tpsValue = document.getElementById('tps-value');
                    const tpsLabel = document.getElementById('tps-label');
                    const tpsRaw = data.tps ?? data.tps_ms ?? data.tick_per_second ?? data.tps_minecraft ?? null;
                    
                    if (tpsValue) {
                        if (tpsRaw !== null && tpsRaw !== undefined && tpsRaw !== '') {
                            const tps = parseFloat(tpsRaw);
                            if (!isNaN(tps)) {
                                tpsValue.textContent = tps.toFixed(1);
                                tpsValue.style.color = getTPSColor(tps);
                                if (tpsLabel) {
                                    tpsLabel.textContent = getTPSLabel(tps);
                                    tpsLabel.style.color = getTPSColor(tps);
                                }
                            } else {
                                tpsValue.textContent = '--';
                                tpsValue.style.color = 'var(--text-muted)';
                                if (tpsLabel) {
                                    tpsLabel.textContent = 'Tick/s';
                                    tpsLabel.style.color = 'var(--text-muted)';
                                }
                            }
                        } else {
                            tpsValue.textContent = '--';
                            tpsValue.style.color = 'var(--text-muted)';
                            if (tpsLabel) {
                                tpsLabel.textContent = 'Tick/s';
                                tpsLabel.style.color = 'var(--text-muted)';
                            }
                        }
                    }

                } catch (err) {
                    console.error("Fetch error:", err);
                }
            }

            async function sendCommand() {
                const cmdInput = document.getElementById('command');
                const cmd = cmdInput.value.trim();
                if (!cmd) return;

                try {
                    const response = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId + '&command=' + encodeURIComponent(cmd));
                    
                    if (!response || response.status === 0) {
                        alert('Erreur: Impossible de joindre le serveur (HTTP 0)');
                        return;
                    }
                    
                    const data = await response.json();
                    console.log('Command result:', data);
                    
                    if (data.success === false) {
                        alert('Erreur: ' + (data.message || 'Inconnue'));
                    }
                    cmdInput.value = "";
                    
                    // Immediately fetch logs to show server response
                    fetchConsole();
                } catch (err) {
                    console.error("Command error:", err);
                    alert("Erreur de connexion: " + err.message);
                }
            }

            async function sendAction(action) {
                const msgEl = document.getElementById('action-message');
                msgEl.textContent = 'Action en cours: ' + action + '...';
                msgEl.className = 'action-message';
                msgEl.style.display = 'block';
                
                try {
                    const response = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId + '&action=' + action, {
                        method: 'GET',
                        headers: {'Content-Type': 'application/json'}
                    }).catch(err => {
                        console.error('Fetch error:', err);
                        msgEl.textContent = "Erreur de connexion: " + err.message;
                        msgEl.className = 'action-message error';
                        msgEl.style.display = 'block';
                        return null;
                    });
                    
                    if (!response) {
                        return;
                    }
                    
                    if (response.status === 0) {
                        msgEl.textContent = "Erreur: Impossible de joindre le serveur (HTTP 0)";
                        msgEl.className = 'action-message error';
                        msgEl.style.display = 'block';
                        return;
                    }
                    
                    const data = await response.json().catch(err => {
                        console.error('JSON parse error:', err);
                        msgEl.textContent = "Erreur: Réponse invalide du serveur";
                        msgEl.className = 'action-message error';
                        msgEl.style.display = 'block';
                        return {error: true};
                    });
                    
                    if (data && data.error) {
                        return;
                    }
                    
                    console.log('Action result:', action, data);
                    
                    if (!data || data.status === 'error') {
                        msgEl.textContent = data.message || 'Erreur: ' + JSON.stringify(data);
                        msgEl.className = 'action-message error';
                    } else if (data.success === false) {
                        msgEl.textContent = data.message || 'Erreur';
                        msgEl.className = 'action-message error';
                    } else {
                        msgEl.textContent = data.message || 'Action "' + action + '" réussie!';
                        msgEl.className = 'action-message success';
                    }
                    msgEl.style.display = 'block';
                    setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
                } catch (err) {
                    console.error("Action error:", err);
                    msgEl.textContent = "Erreur de connexion: " + err.message;
                    msgEl.className = 'action-message error';
                    msgEl.style.display = 'block';
                }
            }

            let systemCheckInterval = null;

            async function stopApiService() {
                const servicesBtn = document.getElementById('services-btn');
                if (servicesBtn && servicesBtn.disabled) return;
                
                try {
                    const response = await fetch('<?= BASE_URL ?>/api/system/status');
                    const data = await response.json();
                    
                    const msgEl = document.getElementById('action-message');
                    
                    if (data.any_server_running) {
                        msgEl.textContent = 'Serveur(s) Minecraft actif(s) - Arrêtez-les d\'abord!';
                        msgEl.className = 'action-message error';
                        msgEl.style.display = 'block';
                        setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
                        return;
                    }
                    
                    if (data.any_server_starting) {
                        msgEl.textContent = 'Serveur(s) en cours de démarrage - Patience...';
                        msgEl.className = 'action-message';
                        msgEl.style.display = 'block';
                        setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
                        return;
                    }
                    
                    if (data.any_server_stopping) {
                        msgEl.textContent = 'Serveur(s) en cours d\'arrêt - Patience...';
                        msgEl.className = 'action-message';
                        msgEl.style.display = 'block';
                        setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
                        return;
                    }
                    
                    if (!data.api_running) {
                        msgEl.textContent = 'API déjà arrêtée';
                        msgEl.className = 'action-message';
                        msgEl.style.display = 'block';
                        setTimeout(() => { msgEl.style.display = 'none'; }, 3000);
                        updateSystemButtons(data);
                        return;
                    }
                    
                    if (confirm('Voulez-vous arrêter l\'API Python?\n\nCela rendra tous les serveurs inaccessibles.')) {
                        const stopResponse = await fetch('<?= BASE_URL ?>/api/system/stop-api', {
                            method: 'POST'
                        });
                        const stopData = await stopResponse.json();
                        
                        if (stopData.success) {
                            msgEl.textContent = stopData.message || 'API arrêtée';
                            msgEl.className = 'action-message success';
                        } else {
                            msgEl.textContent = stopData.message || 'Erreur lors de l\'arrêt de l\'API';
                            msgEl.className = 'action-message error';
                        }
                        msgEl.style.display = 'block';
                        setTimeout(() => { msgEl.style.display = 'none'; }, 5000);
                        updateSystemButtons(data);
                    }
                } catch (err) {
                    console.error("Stop API error:", err);
                    const msgEl = document.getElementById('action-message');
                    msgEl.textContent = "Erreur: " + err.message;
                    msgEl.className = 'action-message error';
                    msgEl.style.display = 'block';
                }
            }

            async function showShutdownConfirm() {
                const shutdownBtn = document.getElementById('shutdown-btn');
                if (shutdownBtn && shutdownBtn.disabled) {
                    alert('Impossible d\'éteindre le PC: un serveur Minecraft est encore actif ou en cours de démarrage/arrêt!');
                    return;
                }
                
                try {
                    const response = await fetch('<?= BASE_URL ?>/api/system/status');
                    const data = await response.json();
                    console.log('System status:', data);
                    
                    if (data.any_server_running || data.any_server_starting || data.any_server_stopping) {
                        alert('Impossible d\'éteindre le PC: un serveur Minecraft est encore actif ou en cours de démarrage/arrêt!');
                        return;
                    }
                    
                    if (!confirm('ATTENTION: Cette action va ÉTEINDRE LE PC après avoir arrêté l\'API!\n\nÊtes-vous sûr de vouloir continuer?')) {
                        return;
                    }
                    
                    const msgEl = document.getElementById('action-message');
                    msgEl.textContent = 'Arrêt de l\'API en cours...';
                    msgEl.className = 'action-message';
                    msgEl.style.display = 'block';
                    
                    const shutdownResponse = await fetch('<?= BASE_URL ?>/api/system/shutdown-pc', {
                        method: 'POST'
                    });
                    const shutdownData = await shutdownResponse.json();
                    console.log('Shutdown result:', shutdownData);
                    
                    if (shutdownData.success) {
                        msgEl.textContent = shutdownData.message || 'PC va s\'éteindre dans 30 secondes!';
                        msgEl.className = 'action-message success';
                    } else {
                        msgEl.textContent = shutdownData.message || shutdownData.error || 'Erreur lors de l\'arrêt';
                        msgEl.className = 'action-message error';
                    }
                    msgEl.style.display = 'block';
                    setTimeout(() => { msgEl.style.display = 'none'; }, 10000);
                    
                    updateSystemButtons(data);
                } catch (err) {
                    console.error("Shutdown error:", err);
                    alert("Erreur: " + err.message);
                }
            }

            <?php if ($userId == 1): ?>
            async function updateSystemButtons(forceRefresh = false) {
                try {
                    let data;
                    if (forceRefresh === true || typeof forceRefresh === 'object') {
                        const response = await fetch('<?= BASE_URL ?>/api/system/status');
                        data = await response.json();
                    } else {
                        data = forceRefresh;
                    }
                    
                    const shutdownBtn = document.getElementById('shutdown-btn');
                    const servicesBtn = document.getElementById('services-btn');
                    
                    const anyActive = data.any_server_running || data.any_server_starting || data.any_server_stopping;
                    
                    if (shutdownBtn) {
                        if (anyActive) {
                            shutdownBtn.disabled = true;
                            shutdownBtn.title = 'Arrêtez d\'abord tous les serveurs Minecraft';
                            shutdownBtn.style.opacity = '0.5';
                            shutdownBtn.style.cursor = 'not-allowed';
                        } else {
                            shutdownBtn.disabled = false;
                            shutdownBtn.title = 'Éteindre le PC';
                            shutdownBtn.style.opacity = '1';
                            shutdownBtn.style.cursor = 'pointer';
                        }
                    }
                    
                    if (servicesBtn) {
                        if (anyActive) {
                            servicesBtn.disabled = true;
                            servicesBtn.title = 'Arrêtez d\'abord tous les serveurs Minecraft';
                            servicesBtn.style.opacity = '0.5';
                            servicesBtn.style.cursor = 'not-allowed';
                        } else {
                            servicesBtn.disabled = false;
                            servicesBtn.title = 'Arrêter l\'API Python';
                            servicesBtn.style.opacity = '1';
                            servicesBtn.style.cursor = 'pointer';
                        }
                    }
                } catch (err) {
                    console.error("Update buttons error:", err);
                }
            }
            <?php endif; ?>

            fetchConsole();
            setInterval(fetchConsole, 250);
            <?php if ($userId == 1): ?>
            updateSystemButtons();
            setInterval(() => updateSystemButtons(true), 3000);
            <?php endif; ?>
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    public function getStatus() {
        $serverId = intval($_GET['id'] ?? 0);
        
        $result = $this->serverService->getServerStatus($serverId);
        echo json_encode($result);
    }

    public function sendCommand() {
        if (!CSRF::validateRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $serverId = intval($data['server_id'] ?? 0);
        $command = trim($data['command'] ?? '');

        if (empty($command)) {
            echo json_encode(['error' => 'Commande vide']);
            return;
        }

        $result = $this->serverService->sendCommand($serverId, $command);
        echo json_encode($result);
    }

    public function sendAction() {
        if (!CSRF::validateRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $serverId = intval($data['server_id'] ?? 0);
        $action = $data['action'] ?? '';

        if (!in_array($action, ['start', 'stop', 'restart'])) {
            echo json_encode(['error' => 'Action invalide']);
            return;
        }

        $result = $this->serverService->sendAction($serverId, $action);
        echo json_encode($result);
    }

    public function files() {
        if (!isset($_GET['id'])) {
            header('Location: ' . BASE_URL . '/servers');
            exit;
        }

        $serverId = intval($_GET['id']);
        $userId = $this->auth->getUserId();

        if ($userId == 1) {
            $server = $this->db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
            $permissions = ["can_view" => 1, "can_start" => 1, "can_stop" => 1, "can_console" => 1, "can_files" => 1];
        } else {
            $server = $this->db->fetch("
                SELECT s.*, p.can_view, p.can_start, p.can_stop, p.can_console, p.can_files
                FROM servers s
                INNER JOIN permissions p ON p.server_id = s.id
                WHERE s.id = ? AND p.user_id = ?
            ", [$serverId, $userId]);

            if (!$server || !$server['can_view']) {
                http_response_code(403);
                echo json_encode(['error' => 'Accès refusé']);
                exit;
            }
            $permissions = [
                "can_view" => $server["can_view"],
                "can_start" => $server["can_start"],
                "can_stop" => $server["can_stop"],
                "can_console" => $server["can_console"],
                "can_files" => $server["can_files"]
            ];
        }

        if (!$server) {
            header('Location: ' . BASE_URL . '/servers');
            exit;
        }

        if (!$permissions["can_files"]) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé aux fichiers']);
            exit;
        }

        ob_start();
        
        $serverType = $server['type'] ?? 'vanilla';
        $supportsPlugins = in_array($serverType, ['spigot', 'paper']);
        ?>
        <div class="page-content" style="padding-top: 70px;">
            <div class="container" style="max-width: 100%; padding: 0 20px;">
                <div class="page-header fade-in" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="<?= BASE_URL ?>/servers/console?id=<?= $serverId ?>" style="display: flex; align-items: center; gap: 6px; color: var(--accent-secondary); text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Retour
                        </a>
                        <div>
                            <h1 style="margin: 0; font-size: 1.5rem;">Fichiers</h1>
                            <span class="subtitle" style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($server['name']) ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-secondary btn-sm" onclick="refreshFiles()">🔄 Actualiser</button>
                        <button class="btn btn-primary btn-sm" onclick="showCreateModal('folder')">+ Nouveau dossier</button>
                        <button class="btn btn-primary btn-sm" onclick="showCreateModal('file')">+ Nouveau fichier</button>
                    </div>
                </div>

                <!-- Modal pour créer/renommer -->
                <div id="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;" onclick="closeModal(event)">
                    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; width: 90%; max-width: 450px; margin: 10% auto; position: relative;" onclick="event.stopPropagation()">
                        <h3 id="modal-title" style="margin: 0 0 20px 0; color: var(--text-primary); font-size: 1.2rem;">Nouveau dossier</h3>
                        
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; margin-bottom: 6px; color: var(--text-muted); font-size: 0.85rem;">Nom</label>
                            <input type="text" id="modal-name" style="width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.5); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 0.95rem;" placeholder="Nom du fichier/dossier">
                        </div>
                        
                        <div id="format-select" style="margin-bottom: 20px; display: none;">
                            <label style="display: block; margin-bottom: 6px; color: var(--text-muted); font-size: 0.85rem;">Type de fichier</label>
                            <select id="modal-format" style="width: 100%; padding: 10px 12px; background: rgba(15,23,42,0.5); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 0.95rem;">
                                <option value="yml">YML (Configuration)</option>
                                <option value="yaml">YAML (Configuration)</option>
                                <option value="json">JSON</option>
                                <option value="txt">Texte (.txt)</option>
                                <option value="properties">Properties</option>
                                <option value="log">Log (.log)</option>
                                <option value="sh">Script Shell (.sh)</option>
                                <option value="bat">Script Batch (.bat)</option>
                                <option value="png">Image PNG</option>
                                <option value="jpg">Image JPG</option>
                                <option value="dat">Données (.dat)</option>
                                <option value="nbt">NBT (.nbt)</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick="closeModal()" style="padding: 10px 20px; background: var(--bg-tertiary); border: none; border-radius: 6px; color: var(--text-primary); cursor: pointer;">Annuler</button>
                            <button id="modal-confirm" onclick="confirmModal()" style="padding: 10px 20px; background: var(--accent-primary); border: none; border-radius: 6px; color: white; cursor: pointer;">Créer</button>
                        </div>
                    </div>
                </div>

                <!-- Menu contextuel -->
                <div id="context-menu" style="display: none; position: fixed; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 0; min-width: 160px; z-index: 1001; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
                    <div onclick="contextOpenFile()" style="padding: 10px 16px; cursor: pointer; color: var(--text-primary); font-size: 0.9rem; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(59,130,246,0.1)'" onmouseout="this.style.background='transparent'">📝 Ouvrir / Éditer</div>
                    <div onclick="contextRename()" style="padding: 10px 16px; cursor: pointer; color: var(--text-primary); font-size: 0.9rem; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(59,130,246,0.1)'" onmouseout="this.style.background='transparent'">✏️ Renommer</div>
                    <div onclick="contextDuplicate()" style="padding: 10px 16px; cursor: pointer; color: var(--text-primary); font-size: 0.9rem; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(59,130,246,0.1)'" onmouseout="this.style.background='transparent'">📋 Dupliquer</div>
                    <div style="height: 1px; background: var(--border-color); margin: 6px 0;"></div>
                    <div onclick="contextDelete()" style="padding: 10px 16px; cursor: pointer; color: var(--danger); font-size: 0.9rem; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">🗑️ Supprimer</div>
                </div>

                <div class="files-wrapper fade-in" style="display: flex; gap: 16px; height: calc(100vh - 160px); min-height: 400px;">
                    <div class="files-sidebar" style="width: 280px; min-width: 240px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
                        <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-color); background: rgba(15, 23, 42, 0.4); flex-shrink: 0;">
                            <span style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">📁 Arborescence</span>
                            <button onclick="collapseAll()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem; padding: 4px; border-radius: 4px;" title="Tout réduire">⊟</button>
                        </div>
                        
                        <div class="quick-access" style="padding: 10px 12px; border-bottom: 1px solid var(--border-color); background: rgba(59, 130, 246, 0.05); flex-shrink: 0;">
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">⚡ Accès rapide</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                <button onclick="quickAccess('mods')" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--accent-secondary); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">🧩 Mods</button>
                                <button onclick="quickAccess('world')" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--accent-secondary); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">🌍 World</button>
                                <?php if ($supportsPlugins): ?>
                                <button onclick="quickAccess('plugins')" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--accent-secondary); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">🔌 Plugins</button>
                                <?php endif; ?>
                                <button onclick="quickAccess('config')" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--accent-secondary); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">⚙️ Config</button>
                            </div>
                        </div>
                        
                        <div id="sidebar-path" style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); background: rgba(15,23,42,0.3); font-size: 0.85rem; color: var(--text-muted);">
                            <span style="cursor: pointer; color: var(--accent-secondary);" onclick="loadFolderContents('')">📂 Racine</span>
                        </div>
                        
                        <div id="tree-view" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 8px 0;">
                        </div>
                    </div>
                    
                    <div class="files-main" style="flex: 1; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; min-width: 0;">
                        <div class="file-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-color); background: rgba(15, 23, 42, 0.4); flex-shrink: 0; flex-wrap: wrap; gap: 8px;">
                            <div id="breadcrumb" style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                <span onclick="loadFolderContents('')" style="cursor: pointer; color: var(--accent-secondary);">🏠 Racine</span>
                            </div>
                            <div id="file-actions" style="display: none; gap: 8px; align-items: center;">
                                <button onclick="closeCurrentFile()" style="background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">⬅️ Retour</button>
                                <span id="current-file-name" style="color: var(--text-primary); font-weight: 500; font-size: 0.85rem; padding: 4px 10px; background: rgba(59, 130, 246, 0.15); border-radius: 4px;"></span>
                                <button onclick="saveCurrentFile()" style="background: var(--accent-primary); color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">💾 Enregistrer</button>
                                <button onclick="deleteCurrentFile()" style="background: var(--danger); color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">🗑️</button>
                            </div>
                        </div>
                        <div id="file-wrapper" style="flex: 1; overflow: auto; padding: 0; position: relative;">
                            <div id="drop-overlay" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(34,197,94,0.2);border:4px dashed #22c55e;z-index:1000;align-items:center;justify-content:center;flex-direction:column;">
                                <div style="font-size:4rem;">📥</div>
                                <p style="color:#22c55e;font-size:1.3rem;font-weight:bold;margin-top:15px;">Déposez vos fichiers ici</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); padding: 40px;">
                                <div style="font-size: 3rem; opacity: 0.5;">📂</div>
                                <p style="margin-top: 16px; text-align: center;">Sélectionnez un dossier ou utilisez les accès rapides</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        const serverId = <?= $serverId ?>;
        const serverType = '<?= $serverType ?>';
        const supportsPlugins = <?= $supportsPlugins ? 'true' : 'false' ?>;
        let currentPath = '';
        let currentFile = null;
        let apiReady = false;
        
        function init() {
            checkApiAndLoad();
        }
        
        async function checkApiAndLoad() {
            console.log('[FILES] Vérification API pour fichiers...');
            const treeView = document.getElementById('tree-view');
            treeView.innerHTML = '<div style="text-align: center; padding: 40px 20px;"><div style="font-size: 3rem; margin-bottom: 16px;">🖥️</div><p style="color: var(--text-muted); margin-bottom: 10px;">Vérification de l\'API...</p><div id="api-status-indicator" style="margin-bottom: 20px;"></div></div>';
            
            try {
                console.log('[FILES] Appel /api/files/ensure...');
                const r = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                
                console.log('[FILES] Réponse reçue, status:', r.status);
                
                if (r.redirected || r.url.includes('/login')) {
                    window.location.href = '<?= BASE_URL ?>/login';
                    return;
                }
                
                const d = await r.json();
                
                console.log('[FILES] Réponse API:', d);
                
                const statusIndicator = document.getElementById('api-status-indicator');
                
                if (d.status === 'ok' || d.status === 'running') {
                    statusIndicator.innerHTML = '<span style="color: #22c55e; font-size: 0.9rem;">✅ API démarrée</span>';
                    console.log('[FILES] API OK, chargement fichiers...');
                    apiReady = true;
                    setTimeout(() => {
                        loadFiles();
                    }, 500);
                } else if (d.status === 'starting') {
                    statusIndicator.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">⏳ API en cours de démarrage...</span>';
                    console.log('[FILES] API en cours de démarrage...');
                    setTimeout(checkApiAndLoad, 3000);
                } else {
                    // API is off - start PC and API automatically
                    statusIndicator.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">⏳ API arrêtée - Démarrage...</span>';
                    console.log('[FILES] API arrêtée, démarrage automatique...');
                    
                    // Check if PC is on
                    const debugResp = await fetch('<?= BASE_URL ?>/test/system?action=debug');
                    const debugData = await debugResp.json().catch(() => ({}));
                    console.log('[FILES] Debug:', debugData);
                    
                    if (!debugData.pc_online) {
                        statusIndicator.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">🖥️ PC OFF - Envoi WOL...</span>';
                        console.log('[WOL] Envoi Wake-on-LAN...');
                        await fetch('<?= BASE_URL ?>/test/system?action=wol');
                        console.log('[WOL] WOL envoyé, attente du PC...');
                        
                        // Wait for PC to come online
                        for (let i = 0; i < 45; i++) {
                            await new Promise(resolve => setTimeout(resolve, 2000));
                            const checkStatus = await fetch('<?= BASE_URL ?>/test/system?action=debug');
                            const checkData = await checkStatus.json().catch(() => ({}));
                            if (checkData.pc_online) {
                                console.log('[WOL] PC allumé!');
                                break;
                            }
                        }
                    }
                    
                    // Start API
                    statusIndicator.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">🐍 Démarrage API...</span>';
                    console.log('[API] Démarrage de l\'API...');
                    const startApiResp = await fetch('<?= BASE_URL ?>/test/system?action=start-api-direct');
                    console.log('[API] Réponse status:', startApiResp.status);
                    const startApiData = await startApiResp.json();
                    console.log('[API] Réponse data:', startApiData);
                    
                    // Wait for API
                    for (let i = 0; i < 30; i++) {
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        try {
                            const r2 = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                            const d2 = await r2.json();
                            console.log('[API] Vérification:', d2);
                            if (d2.status === 'ok') {
                                apiReady = true;
                                statusIndicator.innerHTML = '<span style="color: #22c55e; font-size: 0.9rem;">✅ API démarrée</span>';
                                setTimeout(() => loadFiles(), 500);
                                return;
                            }
                        } catch(e) {
                            console.log('[API] Erreur vérification:', e);
                        }
                    }
                    
                    console.log('[API] Échec du démarrage après 30s');
                    statusIndicator.innerHTML = '<span style="color: #ef4444; font-size: 0.9rem;">❌ Échec du démarrage</span>';
                }
            } catch(e) {
                const statusIndicator = document.getElementById('api-status-indicator');
                statusIndicator.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">⏳ Erreur - Tentative de démarrage...</span>';
                console.error('[ERROR] Erreur détectée:', e);
                console.log('[WOL] Tentative WOL...');
                
                // Try to start PC and API
                await fetch('<?= BASE_URL ?>/test/system?action=wol');
                await new Promise(resolve => setTimeout(resolve, 5000));
                console.log('[API] Tentative de démarrage...');
                await fetch('<?= BASE_URL ?>/test/system?action=start-api-direct');
                
                // Wait and retry
                for (let i = 0; i < 30; i++) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    try {
                        const r2 = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                        const d2 = await r2.json();
                        if (d2.status === 'ok') {
                            loadFiles();
                            return;
                        }
                    } catch(e2) {}
                }
                
                statusIndicator.innerHTML = '<span style="color: #ef4444; font-size: 0.9rem;">❌ Échec du démarrage</span>';
            }
        }
        
        function showLaunchButton(msg) {
            const treeView = document.getElementById('tree-view');
            treeView.innerHTML = '<div style="text-align: center; padding: 40px 20px;"><div style="font-size: 3rem; margin-bottom: 16px;">🖥️</div><p style="color: var(--text-muted); margin-bottom: 20px;">' + msg + '</p><button onclick="startApiAndLoad()" style="background: var(--accent-primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 500;">🚀 Lancer l\'API</button></div>';
        }
        
        async function startApiAndLoad() {
            const treeView = document.getElementById('tree-view');
            treeView.innerHTML = '<div style="text-align: center; padding: 40px 20px;"><div style="font-size: 3rem; margin-bottom: 16px;">🖥️</div><p style="color: var(--text-muted); margin-bottom: 10px;">Démarrage de l\'API...</p><div id="api-start-status" style="margin-bottom: 20px;"></div></div>';
            
            const statusEl = document.getElementById('api-start-status');
            
            // Start API via test endpoint
            await fetch('<?= BASE_URL ?>/test/system?action=start-api-direct');
            
            // Wait for API
            for (let i = 0; i < 15; i++) {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                const r = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                const d = await r.json();
                
                if (d.status === 'ok') {
                    statusEl.innerHTML = '<span style="color: #22c55e; font-size: 0.9rem;">✅ API démarrée!</span>';
                    setTimeout(() => loadFiles(), 500);
                    return;
                }
                statusEl.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">⏳ Attente API... (' + (i+1) + 's)</span>';
            }
            
            statusEl.innerHTML = '<span style="color: #ef4444; font-size: 0.9rem;">❌ Échec du démarrage</span>';
        }
        
        function refreshFiles() {
            checkApiAndLoad();
        }

        function renderTree(items, containerId, parentPath) {
            const container = document.getElementById(containerId);
            if (!items || items.length === 0) {
                container.innerHTML = '<div style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Aucun élément</div>';
                return;
            }
            
            const knownFolders = ['mods', 'plugins', 'world', 'worlds', 'config', 'configs', 'ops', 'whitelist', 'logs', 'backups', 'cache', 'defaultconfigs', 'datapacks', 'advancements', 'playerdata', 'region', 'DIM-1', 'DIM1'];
            
            function isFolder(item) {
                if (item.is_dir === true || item.is_dir === 'true') return true;
                const nameLower = item.name.toLowerCase();
                if (knownFolders.includes(nameLower)) return true;
                if (!item.name.includes('.')) return true;
                return false;
            }
            
            const dirs = items.filter(i => isFolder(i)).sort((a, b) => a.name.localeCompare(b.name));
            const files = items.filter(i => !isFolder(i)).sort((a, b) => a.name.localeCompare(b.name));
            
            let html = '';
            for (const dir of dirs) {
                const fullPath = parentPath ? parentPath + '/' + dir.name : dir.name;
                const safePath = fullPath.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                html += '<div class="tree-folder" data-path="' + fullPath + '">' +
                    '<div onclick="toggleFolder(\'' + safePath + '\', \'' + fullPath + '\'); loadFolderContents(\'' + fullPath + '\');" style="display: flex; align-items: center; padding: 8px 12px; cursor: pointer; color: #fbbf24; font-weight: 500; font-size: 0.9rem; border-radius: 4px; margin: 2px 8px; transition: background 0.15s;" onmouseover="this.style.background=\'rgba(59,130,246,0.1)\'" onmouseout="this.style.background=\'transparent\'">' +
                    '<span id="toggle-' + safePath + '" style="width: 16px; font-size: 0.7rem; color: var(--text-muted); margin-right: 6px;">▶</span>' +
                    '<span style="margin-right: 8px;">📁</span>' +
                    '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + escapeHtml(dir.name) + '</span>' +
                    '</div>' +
                    '<div id="children-' + safePath + '" style="display: none; margin-left: 20px;"></div></div>';
            }
            for (const file of files) {
                const fullPath = parentPath ? parentPath + '/' + file.name : file.name;
                const ext = file.name.split('.').pop().toLowerCase();
                const safePath = fullPath.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                html += '<div onclick="openFile(\'' + safePath + '\', \'' + fullPath + '\', \'' + escapeJs(file.name) + '\')" style="display: flex; align-items: center; padding: 6px 12px; cursor: pointer; color: var(--text-secondary); font-size: 0.85rem; border-radius: 4px; margin: 2px 8px; transition: background 0.15s;" onmouseover="this.style.background=\'rgba(59,130,246,0.1)\'" onmouseout="this.style.background=\'transparent\'">' +
                    '<span style="width: 16px; margin-right: 6px;"></span>' +
                    '<span style="margin-right: 8px; font-size: 0.9rem;">' + getFileIcon(ext) + '</span>' +
                    '<span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + escapeHtml(file.name) + '</span>' +
                    '<span style="padding: 2px 6px; border-radius: 3px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; background: ' + getBadgeBg(ext) + '; color: ' + getBadgeColor(ext) + '; margin-left: 8px; flex-shrink:0;">' + ext + '</span>' +
                    '</div>';
            }
            container.innerHTML = html;
        }

        function getFileIcon(ext) {
            const icons = { 'yml': '⚙️', 'yaml': '⚙️', 'json': '📋', 'txt': '📝', 'properties': '🔧', 'log': '📜', 'jar': '🧩', 'sh': '💻', 'bat': '💻' };
            return icons[ext] || '📄';
        }
        
        function getBadgeBg(ext) {
            const bgs = { 'yml': 'rgba(239,68,68,0.25)', 'json': 'rgba(234,179,8,0.25)', 'jar': 'rgba(168,85,247,0.25)', 'txt': 'rgba(34,197,94,0.25)', 'properties': 'rgba(59,130,246,0.25)' };
            return bgs[ext] || 'rgba(107,114,128,0.25)';
        }
        
        function getBadgeColor(ext) {
            const colors = { 'yml': '#f87171', 'json': '#facc15', 'jar': '#c084fc', 'txt': '#4ade80', 'properties': '#60a5fa' };
            return colors[ext] || '#9ca3af';
        }

        async function toggleFolder(safePath, fullPath) {
            const children = document.getElementById('children-' + safePath);
            const toggle = document.getElementById('toggle-' + safePath);
            
            if (children.style.display === 'none') {
                if (children.innerHTML === '') {
                    children.innerHTML = '<div style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Chargement...</div>';
                    try {
                        const response = await fetch('<?= BASE_URL ?>/api/files', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'server_id=' + serverId + '&action=list&path=' + encodeURIComponent(fullPath)
                        });
                        const data = await response.json();
                        if (data.status === 'error') {
                            children.innerHTML = '<div style="padding: 12px; color: var(--danger); font-size: 0.85rem;">' + data.message + '</div>';
                            return;
                        }
                        renderTree(data.items || [], 'children-' + safePath, fullPath);
                    } catch (err) {
                        children.innerHTML = '<div style="padding: 12px; color: var(--danger); font-size: 0.85rem;">Erreur</div>';
                    }
                }
                children.style.display = 'block';
                toggle.style.transform = 'rotate(90deg)';
            } else {
                children.style.display = 'none';
                toggle.style.transform = 'rotate(0deg)';
            }
        }

        function collapseAll() {
            document.querySelectorAll('[id^="children-"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('[id^="toggle-"]').forEach(el => el.style.transform = 'rotate(0deg)');
        }
        
        function loadFiles() {
            loadFolderContents('');
        }
        
        async function loadFolderContents(path, fromTree = false) {
            currentPath = path;
            updateSidebarPath(path);
            
            const wrapper = document.getElementById('file-wrapper');
            wrapper.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-muted);">Chargement...</div>';
            
            try {
                const response = await fetch('<?= BASE_URL ?>/api/files', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'server_id=' + serverId + '&action=list&path=' + encodeURIComponent(path)
                });
                const data = await response.json();
                
                if (data.status === 'error') {
                    wrapper.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--danger);">' + data.message + '</div>';
                    return;
                }
                
                renderFolderContents(data.items || [], path);
                
                // Auto-expand tree to show current path
                if (!fromTree && path) {
                    expandTreeToPath(path);
                }
                
                // Also update tree if navigating from quick access
                if (fromTree && path) {
                    updateTreeSelection(path);
                }
            } catch (err) {
                wrapper.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur: ' + err.message + '</div>';
            }
        }

        async function expandTreeToPath(targetPath) {
            const parts = targetPath.split('/');
            let currentSearchPath = '';
            
            for (let i = 0; i < parts.length; i++) {
                const part = parts[i];
                currentSearchPath = currentSearchPath ? currentSearchPath + '/' + part : part;
                const safePath = currentSearchPath.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                
                const toggle = document.getElementById('toggle-' + safePath);
                const children = document.getElementById('children-' + safePath);
                
                if (toggle && children) {
                    if (children.style.display === 'none' || children.innerHTML === '') {
                        // Need to load children first
                        if (children.innerHTML === '') {
                            try {
                                const response = await fetch('<?= BASE_URL ?>/api/files', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: 'server_id=' + serverId + '&action=list&path=' + encodeURIComponent(currentSearchPath)
                                });
                                const data = await response.json();
                                if (data.status === 'ok') {
                                    renderTree(data.items || [], 'children-' + safePath, currentSearchPath);
                                }
                            } catch (err) {
                                console.error('Error loading tree children:', err);
                            }
                        }
                        children.style.display = 'block';
                        toggle.style.transform = 'rotate(90deg)';
                    }
                }
            }
            updateTreeSelection(targetPath);
        }
        
        function updateSidebarPath(path) {
            // Update sidebar to show current path like "world > region > DIM1"
            const sidebarContent = document.getElementById('sidebar-path');
            if (!path) {
                sidebarContent.innerHTML = '<div style="padding: 16px; color: var(--text-muted); font-size: 0.85rem;">📂 Racine du serveur</div>';
                return;
            }
            
            const parts = path.split('/');
            let html = '<div style="padding: 16px; color: var(--text-muted); font-size: 0.85rem;">';
            html += '<span style="cursor: pointer; color: var(--accent-secondary);" onclick="loadFolderContents(\'\')">📂 Racine</span>';
            
            let accum = '';
            for (let i = 0; i < parts.length; i++) {
                accum += (accum ? '/' : '') + parts[i];
                const isLast = i === parts.length - 1;
                if (isLast) {
                    html += ' <span style="color: var(--text-primary);">› ' + escapeHtml(parts[i]) + '</span>';
                } else {
                    html += ' <span style="color: var(--accent-secondary); cursor: pointer;" onclick="loadFolderContents(\'' + accum.replace(/[^a-zA-Z0-9_\/-]/g, '_') + '\')">› ' + escapeHtml(parts[i]) + '</span>';
                }
            }
            html += '</div>';
            sidebarContent.innerHTML = html;
        }
        
        function updateTreeSelection(path) {
            // Highlight current folder in tree
            document.querySelectorAll('.tree-item').forEach(el => el.classList.remove('active'));
            if (path) {
                const safePath = path.replace(/[^a-zA-Z0-9_-]/g, '_');
                const item = document.querySelector('.tree-item[data-path="' + path + '"]');
                if (item) item.classList.add('active');
            }
        }
        
        function renderFolderContents(items, parentPath) {
            const wrapper = document.getElementById('file-wrapper');
            if (!items || items.length === 0) {
                wrapper.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--text-muted);">Ce dossier est vide</div>';
                return;
            }
            
            // Same folder detection logic
            const knownFolders = ['mods', 'plugins', 'world', 'worlds', 'config', 'configs', 'ops', 'whitelist', 'logs', 'backups', 'cache', 'defaultconfigs', 'datapacks', 'advancements', 'playerdata', 'region', 'DIM-1', 'DIM1'];
            function isFolder(item) {
                if (item.is_dir === true || item.is_dir === 'true') return true;
                const nameLower = item.name.toLowerCase();
                if (knownFolders.includes(nameLower)) return true;
                if (!item.name.includes('.')) return true;
                return false;
            }
            
            const dirs = items.filter(i => isFolder(i)).sort((a, b) => a.name.localeCompare(b.name));
            const files = items.filter(i => !isFolder(i)).sort((a, b) => a.name.localeCompare(b.name));
            
            let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; padding: 20px;">';
            
            // Parent folder link
            if (parentPath) {
                const parent = parentPath.substring(0, parentPath.lastIndexOf('/')) || '';
                const safeParent = parent.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                html += '<div onclick="loadFolderContents(\'' + safeParent + '\')" style="cursor: pointer; padding: 20px; background: rgba(59,130,246,0.1); border: 1px dashed rgba(59,130,246,0.4); border-radius: 8px; text-align: center; transition: all 0.2s;" onmouseover="this.style.background=\'rgba(59,130,246,0.2)\'" onmouseout="this.style.background=\'rgba(59,130,246,0.1)\'">' +
                    '<div style="font-size: 2.5rem;">📤</div>' +
                    '<div style="margin-top: 8px; color: var(--accent-secondary); font-weight: 500;">Retour</div></div>';
            }
            
            // Folders
            for (const dir of dirs) {
                const fullPath = parentPath ? parentPath + '/' + dir.name : dir.name;
                const safePath = fullPath.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                html += '<div oncontextmenu="showContextMenu(event, {name:\'' + escapeJs(dir.name) + '\', path:\'' + fullPath + '\', isFolder:true})" onclick="loadFolderContents(\'' + safePath + '\')" class="folder-item" style="cursor: pointer; padding: 20px; background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); border-radius: 8px; text-align: center; transition: all 0.2s;" onmouseover="this.style.background=\'rgba(251,191,36,0.2)\'" onmouseout="this.style.background=\'rgba(251,191,36,0.1)\'">' +
                    '<div style="font-size: 2.5rem;">📁</div>' +
                    '<div style="margin-top: 8px; color: #fbbf24; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + escapeHtml(dir.name) + '</div>' +
                    '<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Dossier</div></div>';
            }
            
            // Files
            for (const file of files) {
                const fullPath = parentPath ? parentPath + '/' + file.name : file.name;
                const ext = file.name.split('.').pop().toLowerCase();
                const safePathFull = fullPath.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                html += '<div oncontextmenu="showContextMenu(event, {name:\'' + escapeJs(file.name) + '\', path:\'' + fullPath + '\', isFolder:false})" onclick="openFile(\'' + safePathFull + '\', \'' + fullPath + '\', \'' + escapeJs(file.name) + '\')" class="file-item" style="cursor: pointer; padding: 20px; background: rgba(15,23,42,0.5); border: 1px solid var(--border-color); border-radius: 8px; text-align: center; transition: all 0.2s;" onmouseover="this.style.background=\'rgba(59,130,246,0.15)\'; this.style.borderColor=\'var(--accent-primary)\'" onmouseout="this.style.background=\'rgba(15,23,42,0.5)\'; this.style.borderColor=\'var(--border-color)\'">' +
                    '<div style="font-size: 2.5rem;">' + getFileIcon(ext) + '</div>' +
                    '<div style="margin-top: 8px; color: var(--text-primary); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + escapeHtml(file.name) + '</div>' +
                    '<div style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; margin-top: 4px; display: inline-block; background: ' + getBadgeBg(ext) + '; color: ' + getBadgeColor(ext) + ';">' + ext.toUpperCase() + '</div></div>';
            }
            
            html += '</div>';
            wrapper.innerHTML = html;
        }

        async function quickAccess(folderName) {
            if (folderName === 'plugins' && !supportsPlugins) {
                alert('Plugins non disponibles pour ' + serverType);
                return;
            }
            loadFolderContents(folderName);
        }

        function openFile(safePath, fullPath, name) {
            checkApiAndLoad();
        }
        
        async function startServerAndOpenFile(safePath, fullPath, name) {
            const wrapper = document.getElementById('file-wrapper');
            wrapper.innerHTML = '<div style="text-align: center; padding: 40px 20px;"><div style="font-size: 3rem; margin-bottom: 16px;">🎮</div><p style="color: var(--text-muted); margin-bottom: 10px;">Démarrage du serveur...</p><div id="server-start-status" style="margin-bottom: 20px;"></div></div>';
            
            try {
                const statusEl = document.getElementById('server-start-status');
                statusEl.innerHTML = '<span style="color: #facc15; font-size: 0.9rem;">⏳ Vérification de l\'API et du PC...</span>';
                
                // Check and start PC/API if needed - reuse the logic from index.php
                await ensurePcAndApiReady();
                
                statusEl.innerHTML = '<span style="color: #22c55e; font-size: 0.9rem;">✅ API démarrée</span>';
                
                statusEl.innerHTML += '<br><span style="color: #facc15; font-size: 0.9rem;">⏳ Vérification du serveur Minecraft...</span>';
                
                const statusResponse = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId);
                const statusData = await statusResponse.json();
                
                if (statusData.running && statusData.online) {
                    statusEl.innerHTML += '<br><span style="color: #22c55e; font-size: 0.9rem;">✅ Serveur déjà actif</span>';
                } else if (statusData.starting) {
                    statusEl.innerHTML += '<br><span style="color: #facc15; font-size: 0.9rem;">⏳ Serveur en cours de démarrage...</span>';
                    await new Promise(resolve => setTimeout(resolve, 5000));
                } else {
                    statusEl.innerHTML += '<br><span style="color: #facc15; font-size: 0.9rem;">⏳ Démarrage du serveur Minecraft...</span>';
                    
                    const startResponse = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId + '&action=start');
                    const startData = await startResponse.json();
                    
                    if (!startData.success && startData.status !== 'already_running') {
                        throw new Error(startData.message || 'Erreur lors du démarrage');
                    }
                    
                    for (let i = 0; i < 30; i++) {
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        const checkResponse = await fetch('<?= BASE_URL ?>/includes/api_proxy.php?server_id=' + serverId);
                        const checkData = await checkResponse.json();
                        
                        if (checkData.running && checkData.online) {
                            statusEl.innerHTML += '<br><span style="color: #22c55e; font-size: 0.9rem;">✅ Serveur Minecraft démarré!</span>';
                            break;
                        }
                        if (i === 29) {
                            statusEl.innerHTML += '<br><span style="color: #facc15; font-size: 0.9rem;">⚠️ Serveur en cours de démarrage...</span>';
                        }
                    }
                }
                
                await new Promise(resolve => setTimeout(resolve, 1000));
                openFileDirect(safePath, fullPath, name);
                
            } catch (err) {
                const statusEl = document.getElementById('server-start-status');
                statusEl.innerHTML = '<span style="color: #ef4444; font-size: 0.9rem;">❌ Échec du démarrage</span>';
            }
        }
        
        // Helper function to ensure PC and API are ready
        async function ensurePcAndApiReady() {
            // First check if API is already running
            for (let i = 0; i < 3; i++) {
                try {
                    const r = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                    const d = await r.json();
                    if (d.status === 'ok') {
                        return true;
                    }
                } catch(e) {}
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            // PC not on - try to wake it
            const statusCheck = await fetch('<?= BASE_URL ?>/test/system?action=debug');
            const statusData = await statusCheck.json().catch(() => ({}));
            
            if (!statusData.pc_online) {
                // Try WOL
                console.log('[WOL] PC offline, sending WOL...');
                await fetch('<?= BASE_URL ?>/test/system?action=wol');
                console.log('[WOL] WOL sent, waiting for PC...');
                
                // Wait for PC to come online
                for (let i = 0; i < 45; i++) {
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    const checkStatus = await fetch('<?= BASE_URL ?>/test/system?action=debug');
                    const checkData = await checkStatus.json().catch(() => ({}));
                    if (checkData.pc_online) {
                        break;
                    }
                }
            }
            
            // Start API
            await fetch('<?= BASE_URL ?>/test/system?action=start-api-direct');
            
            // Wait for API to be ready
            for (let i = 0; i < 20; i++) {
                await new Promise(resolve => setTimeout(resolve, 1000));
                const check = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                const result = await check.json().catch(() => ({}));
                if (result.status === 'ok') {
                    return true;
                }
            }
            
            throw new Error('API non joignable');
        }

        async function openFileDirect(safePath, fullPath, name) {
            currentFile = fullPath;
            document.getElementById('file-actions').style.display = 'flex';
            document.getElementById('current-file-name').textContent = name;
            updateSidebarPath(currentPath);
            
            const wrapper = document.getElementById('file-wrapper');
            wrapper.innerHTML = '<textarea id="file-content" spellcheck="false" style="width: 100%; height: 100%; min-height: 300px; background: rgba(15,23,42,0.3); border: none; color: var(--text-primary); font-family: Consolas,Monaco,monospace; font-size: 0.9rem; padding: 16px; resize: none; line-height: 1.6; outline: none;"></textarea>';
            
            fetch('<?= BASE_URL ?>/api/files', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'server_id=' + serverId + '&action=read&path=' + encodeURIComponent(fullPath)
            })
            .then(r => r.json())
            .then(data => { document.getElementById('file-content').value = data.content || ''; });
        }

        async function saveCurrentFile() {
            if (!currentFile) return;
            const content = document.getElementById('file-content').value;
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'write');
            formData.append('path', currentFile);
            formData.append('content', content);
            try {
                const response = await fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'error') alert(data.message);
                else alert('✅ Enregistré!');
            } catch (err) { alert('Erreur'); }
        }

        async function deleteCurrentFile() {
            if (!currentFile) return;
            if (!confirm('Supprimer "' + currentFile + '" ?')) return;
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'delete');
            formData.append('path', currentFile);
            try {
                const response = await fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'error') alert(data.message);
                else {
                    currentFile = null;
                    document.getElementById('file-actions').style.display = 'none';
                    document.getElementById('file-wrapper').innerHTML = '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); padding: 40px;"><div style="font-size: 3rem; opacity: 0.5;">📂</div><p style="margin-top: 16px;">Sélectionnez un fichier pour le modifier</p></div>';
                    localStorage.removeItem(CACHE_KEY);
                    loadFiles();
                }
            } catch (err) { alert('Erreur'); }
        }

        function closeCurrentFile() {
            currentFile = null;
            document.getElementById('file-actions').style.display = 'none';
            loadFolderContents(currentPath);
        }

        let modalMode = 'create'; // 'create' or 'rename'
        let contextItem = null;
        
        function showCreateModal(type) {
            modalMode = 'create';
            document.getElementById('modal-title').textContent = type === 'folder' ? 'Nouveau dossier' : 'Nouveau fichier';
            document.getElementById('modal-name').value = '';
            document.getElementById('modal-confirm').textContent = 'Créer';
            document.getElementById('format-select').style.display = type === 'file' ? 'block' : 'none';
            document.getElementById('modal-overlay').style.display = 'block';
            document.getElementById('modal-name').focus();
        }
        
        function showRenameModal(item) {
            modalMode = 'rename';
            contextItem = item;
            document.getElementById('modal-title').textContent = item.isFolder ? 'Renommer le dossier' : 'Renommer le fichier';
            document.getElementById('modal-name').value = item.name;
            document.getElementById('format-select').style.display = 'none';
            document.getElementById('modal-confirm').textContent = 'Renommer';
            document.getElementById('modal-overlay').style.display = 'block';
            document.getElementById('modal-name').focus();
        }
        
        function closeModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('modal-overlay').style.display = 'none';
            contextItem = null;
        }
        
        function confirmModal() {
            const name = document.getElementById('modal-name').value.trim();
            if (!name) { alert('Veuillez entrer un nom'); return; }
            
            if (modalMode === 'create') {
                const format = document.getElementById('modal-format').value;
                const isFolder = document.getElementById('format-select').style.display === 'none';
                
                if (isFolder) {
                    createFolder(name);
                } else {
                    const fullName = name.includes('.') ? name : name + '.' + format;
                    createFile(fullName);
                }
            } else if (modalMode === 'rename' && contextItem) {
                renameItem(contextItem, name);
            }
            
            closeModal();
        }
        
        function createFile(fullName) {
            const finalPath = currentPath ? currentPath + '/' + fullName : fullName;
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'write');
            formData.append('path', finalPath);
            formData.append('content', '');
            fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'error') alert(data.message);
                    else {
                        loadFiles();
                        setTimeout(() => loadFolderContents(currentPath), 100);
                        const safePath = finalPath.replace(/[^a-zA-Z0-9_-]/g, '_');
                        openFile(safePath, finalPath, fullName);
                    }
                });
        }

        function createFolder(name) {
            const finalPath = currentPath ? currentPath + '/' + name : name;
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'mkdir');
            formData.append('path', finalPath);
            fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'error') alert(data.message);
                    else { 
                        loadFiles(); 
                        setTimeout(() => loadFolderContents(currentPath), 100);
                    }
                });
        }
        
        function renameItem(item, newName) {
            const oldPath = item.path;
            const dir = oldPath.substring(0, oldPath.lastIndexOf('/')) || '';
            const newPath = dir ? dir + '/' + newName : newName;
            
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'rename');
            formData.append('path', oldPath);
            formData.append('new_path', newPath);
            
            fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'error') {
                        alert(data.message);
                    } else {
                        loadFiles();
                        setTimeout(() => loadFolderContents(currentPath), 100);
                    }
                });
        }
        
        function deleteItemPath(path, isFolder) {
            const formData = new FormData();
            formData.append('server_id', serverId);
            formData.append('action', 'delete');
            formData.append('path', path);
            fetch('<?= BASE_URL ?>/api/files', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'error') alert(data.message);
                    else {
                        loadFiles();
                        setTimeout(() => loadFolderContents(currentPath), 100);
                    }
                });
        }
        
        function contextDuplicate() {
            if (!contextItem) return;
            const name = contextItem.name;
            const ext = name.includes('.') ? name.substring(name.lastIndexOf('.')) : '';
            const base = name.includes('.') ? name.substring(0, name.lastIndexOf('.')) : name;
            const newName = base + ' (copie)' + ext;
            renameItem({path: contextItem.path, isFolder: contextItem.isFolder, name: name}, newName);
            hideContextMenu();
        }

        // Context menu
        document.addEventListener('click', () => hideContextMenu());
        
        function showContextMenu(e, item) {
            e.preventDefault();
            contextItem = item;
            const menu = document.getElementById('context-menu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        }
        
        function hideContextMenu() {
            document.getElementById('context-menu').style.display = 'none';
        }
        
        function contextOpenFile() {
            if (!contextItem) return;
            if (contextItem.isFolder) {
                loadFolderContents(contextItem.path);
            } else {
                const safePath = contextItem.path.replace(/[^a-zA-Z0-9_\/-]/g, '_');
                openFile(safePath, contextItem.path, contextItem.name);
            }
            hideContextMenu();
        }
        
        function contextRename() {
            if (!contextItem) return;
            showRenameModal(contextItem);
            hideContextMenu();
        }
        
        function contextDelete() {
            if (!contextItem) return;
            if (confirm('Supprimer "' + contextItem.name + '" ?')) {
                deleteItemPath(contextItem.path, contextItem.isFolder);
            }
            hideContextMenu();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function escapeJs(text) {
            return text.replace(/'/g, "\\'");
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.getElementById('modal-overlay').style.display === 'block') {
                confirmModal();
            }
            if (e.key === 'Escape') {
                closeModal();
                hideContextMenu();
            }
            // F2 to rename selected file (when viewing a file)
            if (e.key === 'F2' && currentFile) {
                showRenameModal({name: currentFile.split('/').pop(), path: currentFile, isFolder: false});
            }
        });

        async function checkApiAndLoad() {
            const treeView = document.getElementById('tree-view');
            treeView.innerHTML = '<div style="text-align: center; padding: 40px 20px;"><div style="font-size: 3rem; margin-bottom: 16px;">🖥️</div><p style="color: var(--text-muted); margin-bottom: 20px;">Démarrage de l\'API...</p></div>';
            
            try {
                const r = await fetch('<?= BASE_URL ?>/api/files/ensure?server_id=' + serverId);
                const d = await r.json();
                
                if (d.status === 'ok' || d.status === 'running') {
                    loadFiles();
                } else {
                    showLaunchButton(d.message || 'API non démarrée');
                }
            } catch(e) {
                showLaunchButton('Erreur de connexion');
            }
        }
        
        document.addEventListener('DOMContentLoaded', init);
        
        // Drag and Drop Upload
        var dragCounter = 0;
        
        document.addEventListener('dragenter', function(e) {
            e.preventDefault();
            dragCounter++;
            if (e.dataTransfer.types.indexOf('Files') > -1) {
                var overlay = document.getElementById('drop-overlay');
                if (overlay) overlay.style.display = 'flex';
            }
        });
        
        document.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dragCounter--;
            if (dragCounter === 0) {
                var overlay = document.getElementById('drop-overlay');
                if (overlay) overlay.style.display = 'none';
            }
        });
        
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('drop', async function(e) {
            e.preventDefault();
            dragCounter = 0;
            var overlay = document.getElementById('drop-overlay');
            if (overlay) overlay.style.display = 'none';
            
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                await uploadFilesDragDrop(files);
            }
        });
        
        async function uploadFilesDragDrop(files) {
            // Skip apiReady check, proceed with upload
            
            var allowedExt = ['jpg','jpeg','png','gif','zip','jar','txt','yml','yaml','json','xml','cfg','conf','properties','md','html','css','js','sql','sh','bat','ps1'];
            var maxSize = 500 * 1024 * 1024;
            var uploaded = 0;
            var errors = [];
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                
                if (file.size > maxSize) {
                    errors.push(file.name + ': Trop volumineux');
                    continue;
                }
                
                var ext = file.name.split('.').pop().toLowerCase();
                if (allowedExt.indexOf(ext) === -1) {
                    errors.push(file.name + ': Extension non autorisée');
                    continue;
                }
                
                var formData = new FormData();
                formData.append('file', file);
                formData.append('server_id', serverId);
                formData.append('path', currentPath);
                
                try {
                    var response = await fetch(BASE_URL + '/api/files', { method: 'POST', body: formData });
                    var result = await response.json();
                    if (result.status !== 'ok') errors.push(file.name + ': ' + (result.message || 'Erreur'));
                } catch (err) {
                    errors.push(file.name + ': ' + err.message);
                }
                uploaded++;
            }
            
            if (errors.length > 0) alert('Erreurs:\n' + errors.join('\n'));
            else alert(uploaded + ' fichier(s) uploadé(s)!');
            
            loadFolderContents(currentPath);
        }
        </script>
        <?php
        $content = ob_get_clean();
        $this->renderLayout($content);
    }

    private function renderLayout($content) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Minecraft Panel</title>
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
