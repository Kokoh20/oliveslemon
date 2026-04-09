@echo off
setlocal ENABLEDELAYEDEXPANSION

REM Project root = folder of this script
set "ROOT=%~dp0"
set "PORT=3000"
set "HOST=127.0.0.1"

REM Check PHP availability
where php >nul 2>&1
if errorlevel 1 (
  echo [Error] PHP is not installed or not in PATH.
  echo Install PHP (e.g., via Scoop: scoop install php) then re-run this script.
  pause
  exit /b 1
)

REM Start PHP built-in server minimized in a separate window
start "PHP Server" /min cmd /c "cd /d "%ROOT%" && php -S %HOST%:%PORT% -t ."

REM Small wait to let server boot
timeout /t 2 /nobreak >nul

REM Open Store in default browser
start "" http://%HOST%:%PORT%/index.html

endlocal
