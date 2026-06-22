<?php
$v = file_get_contents("/var/www/html/resources/views/admin/index.blade.php");
$s = strpos($v, "=== PERMISSIONS ===");
$ss = strrpos(substr($v, 0, $s), "{{--");
$e = strpos($v, "{{-- SERVER MODAL", $s);
echo substr($v, $ss, $e - $ss);
