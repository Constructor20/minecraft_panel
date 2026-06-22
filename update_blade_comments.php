<?php
// Update blade comments to be more casual/human
$views = [
    '/var/www/html/resources/views/servers/index.blade.php',
    '/var/www/html/resources/views/servers/console.blade.php',
    '/var/www/html/resources/views/servers/files.blade.php',
    '/var/www/html/resources/views/admin/index.blade.php',
    '/var/www/html/resources/views/dashboard.blade.php',
    '/var/www/html/resources/views/layouts/app.blade.php',
];

foreach ($views as $path) {
    $v = file_get_contents($path);
    // Replace boring Blade comments with more casual French ones
    $v = str_replace('{{-- Top accent bar --}}', '{{-- Bande de couleur en haut, ça rend les cards plus stylées --}}', $v);
    $v = str_replace('{{-- Header: icon + name + status --}}', '{{-- En-tête avec le type de serveur, son nom, et si c\'est allumé --}}', $v);
    $v = str_replace('{{-- Stats row --}}', '{{-- RAM, port, joueurs en petits blocs --}}', $v);
    $v = str_replace('{{-- Actions --}}', '{{-- Boutons pour agir sur le serveur --}}', $v);
    file_put_contents($path, $v);
    echo "$path done\n";
}
