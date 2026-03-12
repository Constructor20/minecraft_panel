@echo off
setlocal EnableDelayedExpansion

:: Batch file to start Minecraft API with admin privileges
:: If already running as admin, just start the API
net session >nul 2>&1
if %errorLevel% neq 0 (
    :: Not admin, relaunch with elevation
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

:: Now running as admin
cd /d F:\all_serv
start /b cmd /c "C:\Users\aleix\AppData\Local\Programs\Python\Python312\python.exe minecraft_api.py"
echo API started with admin privileges
