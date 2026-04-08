@echo off
echo ================================================
echo   DentalGuard - Starting All Services
echo ================================================

echo.
echo [1/2] Starting AI Server...
start "AI Server" cmd /k "cd /d %~dp0 && python ai_server.py"

timeout /t 3 /nobreak >nul

echo [2/2] Starting Laravel Server...
start "Laravel Server" cmd /k "cd /d %~dp0 && php artisan serve"

echo.
echo ================================================
echo   All services started!
echo ================================================
echo.
echo   Dashboard: http://localhost:8000
echo   AI Server: http://localhost:5000
echo.
echo   Press any key to open dashboard...
pause >nul

start http://localhost:8000

exit
