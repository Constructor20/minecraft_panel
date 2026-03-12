$ErrorActionPreference = "Stop"

$taskName = "MinecraftAPI"
$scriptPath = "F:\all_serv\minecraft_api.py"
$pythonPath = "C:\Users\aleix\AppData\Local\Programs\Python\Python312\python.exe"

# Check if task already exists
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Write-Host "Task already exists, unregistering..."
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# Create action
$action = New-ScheduledTaskAction -Execute $pythonPath -Argument $scriptPath

# Create trigger - run at logon
$trigger = New-ScheduledTaskTrigger -AtLogOn

# Create settings
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

# Create principal - run with highest privileges
$principal = New-ScheduledTaskPrincipal -UserId $env:USERNAME -LogonType Interactive -RunLevel Highest

# Register task
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Minecraft Panel API"

Write-Host "Task created successfully!"

# Start the task now
Start-ScheduledTask -TaskName $taskName

Write-Host "API should be starting..."
