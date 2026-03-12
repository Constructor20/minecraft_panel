# Create a scheduled task that runs with highest privileges
$taskName = "MinecraftPanelShutdown"
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}
$action = New-ScheduledTaskAction -Execute "shutdown.exe" -Argument "/s /t 30 /c `"Arrêt programmé par le panel Minecraft`""
$trigger = New-ScheduledTaskTrigger -AtLogOn
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries
$principal = New-ScheduledTaskPrincipal -UserId "Administrators" -LogonType Password -RunLevel Highest
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Scheduled shutdown for Minecraft Panel"
Start-ScheduledTask -TaskName $taskName
