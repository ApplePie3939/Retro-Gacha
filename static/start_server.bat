@echo off
echo ========================================
echo   Furumachi Retro Gacha Walk Server
echo ========================================
echo.

REM PHP path (XAMPP)
set PHP_PATH=C:\xampp\php\php.exe

REM Check if PHP exists
if not exist "%PHP_PATH%" (
    echo [ERROR] PHP not found: %PHP_PATH%
    echo Please install XAMPP first.
    pause
    exit /b 1
)

REM Initialize database if not exists
if not exist "db\gacha.db" (
    echo Initializing database...
    "%PHP_PATH%" init_db.php
    if errorlevel 1 (
        echo [ERROR] Database initialization failed
        pause
        exit /b 1
    )
    echo.
)

REM Show local IP addresses
echo Your IP addresses:
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /R "IPv4"') do (
    for /f "tokens=1" %%b in ("%%a") do (
        echo   http://%%b:8080
    )
)
echo.
echo Local: http://localhost:8080
echo Share the IP URL above with people on the same Wi-Fi
echo Press Ctrl+C to stop the server
echo ========================================
echo.

REM Start server (bind to all interfaces)
"%PHP_PATH%" -S 0.0.0.0:8080

echo.
echo Server stopped.
pause
