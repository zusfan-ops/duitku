@echo off
setlocal
cd /d "%~dp0"
echo ===================================================
echo   DuitKu Mobile - Auto Release Builder
echo ===================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0tool\build_release.ps1" %*
pause
