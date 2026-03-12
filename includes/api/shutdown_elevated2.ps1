$ErrorActionPreference = "Continue"

# Check if already running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if ($isAdmin) {
    # Already admin, just run shutdown
    shutdown /s /t 30 /c "Arrêt programmé par le panel Minecraft"
} else {
    # Need to elevate
    Start-Process powershell -ArgumentList "-Command", "shutdown /s /t 30 /c 'Arrêt programmé par le panel Minecraft'" -Verb RunAs
}
