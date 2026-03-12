# Request elevated shutdown
$arg = "/c shutdown /s /t 30 /c `"Arrêt programmé par le panel Minecraft`""
Start-Process "cmd.exe" -ArgumentList $arg -Verb RunAs
