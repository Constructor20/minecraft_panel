<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 - Page non trouvée</title>
    <style>
        body { font-family: sans-serif; background: #1a1a2e; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .error { text-align: center; }
        h1 { font-size: 5rem; color: #ff6b6b; }
        a { color: #00d9ff; }
    </style>
</head>
<body>
    <div class="error">
        <h1>404</h1>
        <p>Page non trouvée</p>
        <a href="/">Retour à l'accueil</a>
    </div>
</body>
</html>
