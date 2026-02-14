@echo off
echo ===================================================
echo 🚀 WorldOS Quick Setup
echo ===================================================
echo.

echo [1/4] Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ✅ Caches cleared
echo.

echo [2/4] Installing dependencies...
composer install
echo ✅ Dependencies installed
echo.

echo [3/4] Running migrations...
php artisan migrate
echo ✅ Migrations completed
echo.

echo [4/4] Starting server...
echo 🌐 WorldOS starting at http://localhost:8000
echo 📊 World Management: http://localhost:8000/worlds
echo.
echo Press Ctrl+C to stop
echo.
php artisan serve --host=0.0.0.0 --port=8000
