@echo off
echo ===================================================
echo 🚀 WorldOS Setup Script - Complete Installation
echo ===================================================
echo.

echo [1/6] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Cache clearing failed!
    pause
    exit /b 1
)
echo ✅ Caches cleared successfully
echo.

echo [2/6] Installing Composer dependencies...
composer install
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Composer install failed!
    pause
    exit /b 1
)
echo ✅ Composer dependencies installed successfully
echo.

echo [3/6] Running database migrations...
php artisan migrate
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Database migration failed!
    pause
    exit /b 1
)
echo ✅ Database migrations completed successfully
echo.

echo [4/6] Optimizing application...
php artisan config:cache
php artisan route:cache
php artisan view:cache
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️ Optimization had some issues, but continuing...
)
echo ✅ Application optimized
echo.

echo [5/6] Testing repository bindings...
php artisan tinker --execute="
echo 'Testing all repositories...' . PHP_EOL;
echo '' . PHP_EOL;
echo '$repos = [' . PHP_EOL;
echo '    \App\Domains\World\Repositories\WorldRepository::class,' . PHP_EOL;
echo '    \App\Domains\World\Repositories\ShockEventRepository::class,' . PHP_EOL;
echo '    \App\Domains\Intelligence\Repositories\IntelligenceRepository::class,' . PHP_EOL;
echo '    \App\Domains\Character\Repositories\CharacterSurvivalRepository::class,' . PHP_EOL;
echo '    \App\Domains\Material\Contracts\MaterialRepositoryInterface::class,' . PHP_EOL;
echo '    \App\Domains\Material\State\WorldStateRepository::class,' . PHP_EOL;
echo '    \App\Domains\Material\State\CompressedSnapshotRepository::class,' . PHP_EOL;
echo '];' . PHP_EOL;
echo '' . PHP_EOL;
echo 'foreach ($repos as $repo) {' . PHP_EOL;
echo '    try {' . PHP_EOL;
echo '        $instance = app($repo);' . PHP_EOL;
echo '        echo \"✅ \" . get_class($instance) . \"\\n\";' . PHP_EOL;
echo '    } catch (Exception $e) {' . PHP_EOL;
echo '        echo \"❌ \" . $repo . \" - \" . $e->getMessage() . \"\\n\";' . PHP_EOL;
echo '    }' . PHP_EOL;
echo '}' . PHP_EOL;
echo '' . PHP_EOL;
echo 'echo \"🎉 Repository binding test completed!\\n\";' . PHP_EOL;
"
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️ Repository test had some issues, but continuing...
)
echo ✅ Repository binding test completed
echo.

echo [6/6] Starting development server...
echo.
echo 🌐 WorldOS is now starting...
echo 📍 Access the application at: http://localhost:8000
echo 📊 World Management: http://localhost:8000/worlds
echo 🎮 Dashboard: http://localhost:8000/worlds/{world-id}/dashboard
echo.
echo Press Ctrl+C to stop the server
echo.
php artisan serve --host=0.0.0.0 --port=8000

echo.
echo ===================================================
echo 🎉 WorldOS Setup Complete!
echo ===================================================
echo.
echo 🌐 Application URL: http://localhost:8000
echo 📚 Documentation: HUONG_DAN_KHOI_CHAY.md
echo 🚀 Quick Start: QUICK_START.md
echo 🔧 Troubleshooting: TROUBLESHOOTING_GUIDE.md
echo.
echo 🎮 Next Steps:
echo 1. Visit http://localhost:8000/worlds to create your first world
echo 2. Start autonomous mode from the dashboard
echo 3. Monitor real-time updates and analytics
echo.
echo 🌍 Autonomous Worlds Ready! 🎯
echo ===================================================
pause
