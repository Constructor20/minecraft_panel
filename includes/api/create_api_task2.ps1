$ErrorActionPreference = "Continue"

$taskName = "MinecraftAPI"
$scriptPath = "F:\all_serv\minecraft_api.py"
$pythonPath = "C:\Users\aleix\AppData\Local\Programs\Python\Python312\python.exe"

# Remove existing task
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

# Create action
$action = New-ScheduledTaskAction -Execute $pythonPath -Argument $scriptPath

# Trigger at logon
$trigger = New-ScheduledTaskTrigger -AtLogOn

# Settings
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable:$false

# Principal with highest privileges - use SYSTEM for reliability
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

# Register
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Minecraft Panel API" | Out-Null

Write-Host "Task created with SYSTEM account and Highest privileges"

# Run now
Start-ScheduledTask -TaskName $taskName

Write-Host "API starting..."
