<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Minecraft Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #1a1a2e;
            color: #fff;
        }
        h1 { color: #4ade80; }
        h2 { color: #60a5fa; border-bottom: 1px solid #374151; padding-bottom: 10px; }
        .section {
            background: #16213e;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary { background: #4ade80; color: #000; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-warning { background: #f59e0b; color: #000; }
        .btn-info { background: #3b82f6; color: #fff; }
        .btn-success { background: #22c55e; color: #fff; }
        .btn-purple { background: #a855f7; color: #fff; }
        .btn-large { padding: 15px 30px; font-size: 16px; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        #output {
            background: #0f0f23;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 500px;
            overflow-y: auto;
            margin-top: 20px;
        }
        .status { margin: 10px 0; font-size: 18px; }
        .status-ok { color: #4ade80; }
        .status-error { color: #ef4444; }
        .status-loading { color: #f59e0b; }
        .progress-bar {
            height: 20px;
            background: #374151;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4ade80, #22c55e);
            width: 0%;
            transition: width 0.3s;
        }
        input, select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #374151;
            background: #1f2937;
            color: #fff;
            margin: 5px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test Panel Minecraft</h1>
    
    <div class="section">
        <h2>🔧 Configuration</h2>
        <label>ID Serveur:</label>
        <input type="number" id="serverId" value="7" min="1" style="width: 60px;">
    </div>
    
    <div class="section">
        <h2>1. Démarrage Complet (WOL + API + Serveur)</h2>
        <button class="btn btn-success btn-large" onclick="fullStart()">🚀 Démarrage Complet</button>
        <div class="progress-bar"><div class="progress-fill" id="progressStart"></div></div>
    </div>
    
    <div class="section">
        <h2>2. Arrêt Complet (Serveur + API + PC)</h2>
        <button class="btn btn-danger btn-large" onclick="fullStop()">🛑 Arrêt Complet</button>
        <div class="progress-bar"><div class="progress-fill" id="progressStop"></div></div>
    </div>
    
    <div class="section">
        <h2>3. Système (PC Tour)</h2>
        <button class="btn btn-primary" onclick="testWol()">📡 WOL (Allumer PC)</button>
        <button class="btn btn-info" onclick="testPing()">🔍 Vérifier PC</button>
        <button class="btn btn-success" onclick="testApiHealth()">🐍 Vérifier API</button>
    </div>
    
    <div class="section">
        <h2>4. API Python</h2>
        <button class="btn btn-success" onclick="testStartApi()">▶️ Démarrer API</button>
        <button class="btn btn-danger" onclick="testStopApi()">⏹️ Arrêter API</button>
    </div>
    
    <div class="section">
        <h2>5. Serveur Minecraft</h2>
        <button class="btn btn-success" onclick="testStartMc()">▶️ Démarrer serveur</button>
        <button class="btn btn-danger" onclick="testStopMc()">⏹️ Arrêter serveur</button>
        <button class="btn btn-info" onclick="testStatusMc()">🔍 Status serveur</button>
    </div>
    
    <div class="section">
        <h2>6. Arrêt PC</h2>
        <button class="btn btn-danger" onclick="testShutdown()">🔴 Éteindre PC</button>
    </div>
    
    <div class="section">
        <h2>7. Fichiers Serveur</h2>
        <button class="btn btn-purple" onclick="testEnsureApiAndFiles()">📁 Lancer API + Accéder fichiers</button>
    </div>
    
    <div id="status"></div>
    <div id="output"></div>

    <script>
        // Configuration centralisée
        const CONFIG = {
            PC_IP: '192.168.1.22',
            PC_MAC: '2c:f0:5d:7f:e3:2b',
            API_KEY: '6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8=',
            BASE_URL: '',
            DEFAULT_SERVER_ID: 7
        };
        
        const BASE_URL = CONFIG.BASE_URL;
        const API_KEY = CONFIG.API_KEY;
        const PC_IP = CONFIG.PC_IP;
        
        function getServerId() {
            return parseInt(document.getElementById('serverId').value) || CONFIG.DEFAULT_SERVER_ID;
        }
        
        function setProgress(id, percent) {
            document.getElementById(id).style.width = percent + '%';
        }
        
        function log(msg, type = '') {
            const output = document.getElementById('output');
            const time = new Date().toLocaleTimeString();
            output.textContent += `[${time}] ${msg}\n`;
            output.scrollTop = output.scrollHeight;
        }
        
        function setStatus(msg, type = 'ok') {
            document.getElementById('status').innerHTML = `<div class="status status-${type}">${msg}</div>`;
        }
        
        // ============ DEMARRAGE COMPLET ============
        async function fullStart() {
            const serverId = getServerId();
            log('🚀 === DEMARRAGE COMPLET ===');
            setStatus('Démarrage en cours...', 'loading');
            setProgress('progressStart', 10);
            
            // Step 1: WOL
            await testWol();
            setProgress('progressStart', 30);
            
            // Step 2: Start API
            await testStartApi();
            setProgress('progressStart', 60);
            
            // Step 3: Start MC Server
            await testStartMc();
            setProgress('progressStart', 100);
            log('✅ Démarrage complet terminé!');
        }
        
        // ============ ARRÊT COMPLET ============
        async function fullStop() {
            const serverId = getServerId();
            log('🛑 === ARRÊT COMPLET ===');
            setStatus('Arrêt en cours...', 'loading');
            setProgress('progressStop', 20);
            
            // Step 1: Stop MC Server
            await testStopMc();
            setProgress('progressStop', 50);
            
            // Step 2: Stop API
            await testStopApi();
            setProgress('progressStop', 80);
            
            // Step 3: Shutdown PC
            await testShutdown();
            setProgress('progressStop', 100);
            log('✅ Arrêt complet terminé!');
        }
        
        // ============ FONCTIONS SIMPLES ============
        async function testWol() {
            log('📡 Envoi WOL...');
            try {
                const resp = await fetch(BASE_URL + '/test_api.php?action=wol');
                const data = await resp.json();
                log('WOL: ' + JSON.stringify(data));
                if (data.wol_sent) {
                    log('✅ WOL envoyé! (attente PC...)', 'ok');
                    setStatus('WOL envoyé - attente PC...', 'loading');
                }
            } catch(e) {
                log('❌ Erreur WOL: ' + e.message, 'error');
            }
        }
        
        async function testPing() {
            log('🔍 Vérification PC...');
            try {
                const resp = await fetch(BASE_URL + '/test_api.php?action=ping');
                const data = await resp.json();
                log('PC: ' + (data.pc_online ? 'ONLINE' : 'OFFLINE') + ' | API: ' + (data.api_running ? 'ON' : 'OFF'));
                setStatus(data.pc_online ? 'PC Online' : 'PC Offline', data.pc_online ? 'ok' : 'error');
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testApiHealth() {
            log('🐍 Vérification API...');
            try {
                const resp = await fetch('http://' + PC_IP + ':8080/health');
                const data = await resp.json();
                log('API Health: ' + JSON.stringify(data));
                log('✅ API en ligne', 'ok');
                setStatus('API en ligne', 'ok');
            } catch(e) {
                log('❌ API hors ligne: ' + e.message, 'error');
                setStatus('API hors ligne', 'error');
            }
        }
        
        async function testStartApi() {
            log('▶️ Démarrage API...');
            try {
                const resp = await fetch(BASE_URL + '/test_api.php?action=start-api');
                const data = await resp.json();
                log('Start API: ' + JSON.stringify(data));
                for (let i = 0; i < 15; i++) {
                    await new Promise(r => setTimeout(r, 2000));
                    try {
                        const health = await fetch('http://' + PC_IP + ':8080/health');
                        if (health.ok) {
                            log('✅ API démarrée!', 'ok');
                            setStatus('API démarrée!', 'ok');
                            return;
                        }
                    } catch(e) {}
                }
                log('⚠️ API pas encore démarrée', 'loading');
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testStopApi() {
            log('⏹️ Arrêt API...');
            try {
                const resp = await fetch(BASE_URL + '/test_api.php?action=stop-api');
                const data = await resp.json();
                log('Stop API: ' + JSON.stringify(data));
                log('✅ API arrêtée', 'ok');
                setStatus('API arrêtée', 'ok');
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testStartMc() {
            const serverId = getServerId();
            log('▶️ Démarrage serveur MC ' + serverId + '...');
            try {
                const resp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + serverId + '&action=start');
                const data = await resp.json();
                log('Start MC: ' + JSON.stringify(data));
                if (data.status === 'success') {
                    log('⏳ Serveur en cours de démarrage...', 'loading');
                    setStatus('Serveur en cours de démarrage...', 'loading');
                    for (let i = 0; i < 20; i++) {
                        await new Promise(r => setTimeout(r, 3000));
                        try {
                            const statusResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + serverId);
                            const statusData = await statusResp.json();
                            if (statusData.running && statusData.online) {
                                log('✅ Serveur en ligne! RAM: ' + statusData.ram + 'MB', 'ok');
                                setStatus('Serveur en ligne!', 'ok');
                                return;
                            }
                        } catch(e) {}
                    }
                } else {
                    log('❌ Erreur: ' + data.message, 'error');
                    setStatus('Erreur: ' + data.message, 'error');
                }
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testStopMc() {
            const serverId = getServerId();
            log('⏹️ Arrêt serveur MC ' + serverId + '...');
            try {
                const resp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + serverId + '&action=stop');
                const data = await resp.json();
                log('Stop MC: ' + JSON.stringify(data));
                log('✅ Serveur arrêté', 'ok');
                setStatus('Serveur arrêté', 'ok');
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testStatusMc() {
            const serverId = getServerId();
            log('🔍 Status serveur MC ' + serverId + '...');
            try {
                const resp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + serverId);
                const data = await resp.json();
                log('Status MC: ' + JSON.stringify(data));
                if (data.running) {
                    log('✅ Serveur en ligne - RAM: ' + data.ram + 'MB, CPU: ' + data.cpu + '%', 'ok');
                    setStatus('Serveur en ligne', 'ok');
                } else {
                    log('❌ Serveur hors ligne', 'error');
                    setStatus('Serveur hors ligne', 'error');
                }
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testShutdown() {
            log('🔴 Arrêt du PC...');
            if (!confirm('Voulez-vous vraiment arrêter le PC?')) return;
            try {
                const resp = await fetch(BASE_URL + '/test_api.php?action=shutdown-pc');
                const data = await resp.json();
                log('Shutdown: ' + JSON.stringify(data));
                log('✅ PC va s\'éteindre dans 30 secondes', 'ok');
                setStatus('PC va s\'éteindre', 'ok');
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
            }
        }
        
        async function testEnsureApiAndFiles() {
            const serverId = getServerId();
            log('📁 === LANCEMENT API + ACCÈS FICHIERS ===');
            setStatus('Vérification API...', 'loading');
            
            // Step 1: Check if PC is online
            log('🔍 Vérification PC...');
            try {
                const debugResp = await fetch(BASE_URL + '/test_api.php?action=ping');
                const debugData = await debugResp.json();
                
                if (!debugData.pc_online) {
                    log('📡 PC hors ligne - Envoi WOL...');
                    await fetch(BASE_URL + '/test_api.php?action=wol');
                    
                    // Wait for PC
                    for (let i = 0; i < 30; i++) {
                        await new Promise(r => setTimeout(r, 2000));
                        const pingResp = await fetch(BASE_URL + '/test_api.php?action=ping');
                        const pingData = await pingResp.json();
                        if (pingData.pc_online) {
                            log('✅ PC allumé!');
                            break;
                        }
                    }
                }
            } catch(e) {
                log('⚠️ Erreur WOL: ' + e.message);
            }
            
            // Step 2: Start API if needed
            log('🐍 Vérification/Démarrage API...');
            try {
                const ensureResp = await fetch(BASE_URL + '/api/files/ensure?server_id=' + serverId);
                const ensureData = await ensureResp.json();
                log('Ensure: ' + JSON.stringify(ensureData));
                
                if (ensureData.status === 'ok') {
                    log('✅ API déjà démarrée - Accès fichiers OK!', 'ok');
                    setStatus('Accès fichiers OK!', 'ok');
                } else {
                    log('🐍 API arrêtée - Démarrage...');
                    await fetch(BASE_URL + '/test_api.php?action=start-api');
                    
                    // Wait for API
                    for (let i = 0; i < 15; i++) {
                        await new Promise(r => setTimeout(r, 2000));
                        try {
                            const health = await fetch('http://' + PC_IP + ':8080/health');
                            if (health.ok) {
                                log('✅ API démarrée - Accès fichiers OK!', 'ok');
                                setStatus('Accès fichiers OK!', 'ok');
                                return;
                            }
                        } catch(e) {}
                    }
                    log('⚠️ API en cours de démarrage', 'loading');
                    setStatus('API en cours de démarrage...', 'loading');
                }
            } catch(e) {
                log('❌ Erreur: ' + e.message, 'error');
                setStatus('Erreur: ' + e.message, 'error');
            }
        }
    </script>
</body>
</html>
