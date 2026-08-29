@echo off
setlocal
cd /d "%~dp0duitku_app"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0duitku_app\tool\build_release.ps1" %*
pause
