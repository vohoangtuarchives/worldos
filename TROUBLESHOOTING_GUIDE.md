# 🔧 Troubleshooting Guide - WorldOS

## 🚨 Common Issues & Solutions

### **❌ Class "App\Providers\Response" not found**

**Problem:** Missing import in AppServiceProvider

**Solution:**
```php
// Add these imports at the top of app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
```

**Fixed Code:**
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    // ... rest of the code
}
```

---

## 🚨 Database Issues

### **❌ Database Connection Failed**

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- "No such file or directory" (SQLite)

**Solutions:**

#### **For MySQL:**
```bash
# 1. Check MySQL service
sudo service mysql status
# or on Windows: net start mysql

# 2. Start MySQL if not running
sudo service mysql start

# 3. Check .env configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worldos
DB_USERNAME=root
DB_PASSWORD=your_password

# 4. Test connection
php artisan tinker
DB::connection()->getPdo();
```

#### **For SQLite:**
```bash
# 1. Create database file
touch database/database.sqlite

# 2. Update .env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# 3. Run migrations
php artisan migrate:fresh
```

### **❌ Migration Errors**

**Problem:** "Table already exists" or migration conflicts

**Solutions:**
```bash
# 1. Fresh migration (will delete all data)
php artisan migrate:fresh

# 2. Reset and re-migrate
php artisan migrate:reset
php artisan migrate

# 3. Check migration status
php artisan migrate:status

# 4. Run specific migration
php artisan migrate --path=database/migrations/2024_01_01_000000_create_worlds_table.php
```

---

## 🚨 Frontend Issues

### **❌ Assets Not Loading (404 errors)**

**Symptoms:**
- CSS/JS files returning 404
- Broken styling
- JavaScript not working

**Solutions:**
```bash
# 1. Install npm dependencies
npm install

# 2. Compile assets
npm run build

# 3. Create storage link
php artisan storage:link

# 4. Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 5. Check file permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### **❌ Mix/Vite Build Errors**

**Problem:** Build process fails

**Solutions:**
```bash
# 1. Clear npm cache
npm cache clean --force

# 2. Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# 3. Reinstall dependencies
npm install

# 4. Try development build first
npm run dev

# 5. Check for syntax errors in JS files
npm run lint
```

---

## 🚨 Queue Issues

### **❌ Queue Jobs Not Processing**

**Symptoms:**
- Jobs stuck in queue
- Real-time updates not working
- Background tasks not executing

**Solutions:**
```bash
# 1. Check Redis is running
redis-cli ping
# Should return: PONG

# 2. Start Redis if not running
redis-server

# 3. Check queue configuration in .env
QUEUE_CONNECTION=redis

# 4. Restart queue worker
php artisan queue:restart
php artisan queue:work --tries=3 --timeout=60

# 5. Check queue status
php artisan queue:monitor

# 6. Clear failed jobs
php artisan queue:flush
```

### **❌ Redis Connection Failed**

**Solutions:**
```bash
# 1. Check Redis service
sudo service redis status
# or on Windows: Check Redis service in Services

# 2. Start Redis
sudo service redis start

# 3. Check Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# 4. Test connection
php artisan tinker
Redis::ping();
```

---

## 🚨 Performance Issues

### **❌ Slow Page Loading**

**Solutions:**
```bash
# 1. Optimize caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Clear application cache
php artisan cache:clear

# 3. Check database queries
php artisan tinker
DB::enableQueryLog();
// Run some queries
DB::getQueryLog();

# 4. Optimize autoloader
composer dump-autoload --optimize

# 5. Check memory usage
php artisan tinker
memory_get_usage(true);
```

### **❌ High Memory Usage**

**Solutions:**
```bash
# 1. Increase PHP memory limit
# In .env add:
MEMORY_LIMIT=512M

# 2. Optimize queue worker memory
php artisan queue:work --memory=256

# 3. Check for memory leaks
php artisan tinker
$memory = memory_get_usage(true);
echo "Memory usage: " . ($memory / 1024 / 1024) . " MB\n";

# 4. Restart services
php artisan cache:clear
php artisan queue:restart
```

---

## 🚨 Authentication Issues

### **❌ Session/Authentication Problems**

**Solutions:**
```bash
# 1. Clear session data
php artisan session:table
php artisan migrate

# 2. Regenerate application key
php artisan key:generate

# 3. Clear session cache
php artisan session:flush

# 4. Check session configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

---

## 🚨 File Permission Issues

### **❌ Storage/Write Permission Errors**

**Solutions:**
```bash
# On Linux/macOS
sudo chown -R www-data:www-data storage
sudo chmod -R 755 storage
sudo chmod -R 755 bootstrap/cache

# On Windows
# Right-click storage folder → Properties → Security
# Give full control to your user and IIS_IUSRS

# Alternative: Use icacls
icacls storage /grant "IIS_IUSRS:(OI)(CI)F"
icacls storage /grant "Users:(OI)(CI)F"
```

---

## 🚨 WebSocket/Real-time Issues

### **❌ Real-time Updates Not Working**

**Solutions:**
```bash
# 1. Check queue worker is running
php artisan queue:work

# 2. Check Redis connection
redis-cli ping

# 3. Check browser console for WebSocket errors
# Open browser dev tools → Console tab

# 4. Test manual refresh
# Refresh page to see if updates appear

# 5. Check JavaScript errors
# Open browser dev tools → Console tab
```

---

## 🚨 Environment Issues

### **❌ .env Configuration Problems**

**Solutions:**
```bash
# 1. Verify .env file exists
ls -la .env

# 2. Regenerate application key
php artisan key:generate

# 3. Clear configuration cache
php artisan config:clear

# 4. Check environment
php artisan env
```

### **❌ Missing Environment Variables**

**Essential .env variables:**
```bash
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🚨 Composer Issues

### **❌ Composer Install/Update Errors**

**Solutions:**
```bash
# 1. Clear composer cache
composer clear-cache

# 2. Update composer
composer self-update

# 3. Remove vendor directory
rm -rf vendor

# 4. Reinstall dependencies
composer install --no-dev --optimize-autoloader

# 5. Check PHP version compatibility
php --version
# Should be 8.2 or higher
```

---

## 🚨 NPM Issues

### **❌ NPM Install/Build Errors**

**Solutions:**
```bash
# 1. Clear npm cache
npm cache clean --force

# 2. Remove node_modules
rm -rf node_modules package-lock.json

# 3. Update npm
npm install -g npm@latest

# 4. Reinstall dependencies
npm install

# 5. Check Node.js version
node --version
# Should be 16.0 or higher
```

---

## 🚨 Port Conflicts

### **❌ Port Already in Use**

**Solutions:**
```bash
# 1. Check what's using port 8000
netstat -an | findstr :8000

# 2. Kill process using port 8000 (Windows)
taskkill /PID <PID> /F

# 3. Use different port
php artisan serve --port=8001

# 4. Find and kill process (Linux/macOS)
sudo lsof -i :8000
sudo kill -9 <PID>
```

---

## 🚨 Debug Mode Issues

### **❌ Debug Mode Not Working**

**Solutions:**
```bash
# 1. Enable debug in .env
APP_DEBUG=true

# 2. Clear configuration cache
php artisan config:clear

# 3. Check error reporting
php artisan tinker
config('app.debug');

# 4. View detailed error logs
tail -f storage/logs/laravel.log
```

---

## 🚨 Testing Issues

### **❌ Tests Not Running**

**Solutions:**
```bash
# 1. Install testing dependencies
composer require --dev phpunit/phpunit

# 2. Create tests directory
mkdir -p tests/Feature tests/Unit

# 3. Run tests with verbose output
php artisan test --verbose

# 4. Check test configuration
php artisan config:show testing
```

---

## 🚨 Production Issues

### **❌ Production Environment Errors**

**Solutions:**
```bash
# 1. Set production environment
APP_ENV=production
APP_DEBUG=false

# 2. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize

# 3. Set proper file permissions
chmod -R 755 storage bootstrap/cache

# 4. Check production logs
tail -f storage/logs/laravel.log
```

---

## 🚨 Quick Fix Commands

### **🔧 Reset Everything**
```bash
# Complete reset (will delete all data)
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan queue:restart
```

### **🔧 Restart Services**
```bash
# Restart all services
php artisan cache:clear
php artisan queue:restart
redis-server --restart
php artisan serve --host=0.0.0.0 --port=8000
```

### **🔧 Check System Health**
```bash
# Check all components
php artisan --version
composer --version
node --version
npm --version
redis-cli ping
php artisan migrate:status
```

---

## 🚨 Getting Help

### **📋 Information to Collect**
When asking for help, provide:
1. **Error message** (full stack trace)
2. **Environment details** (OS, PHP version, etc.)
3. **Steps to reproduce**
4. **What you've tried**
5. **Relevant configuration** (.env file, minus secrets)

### **🔍 Debug Commands**
```bash
# System information
php -v
composer --version
node --version
npm --version

# Laravel information
php artisan --version
php artisan env
php artisan route:list

# Database information
php artisan tinker
DB::connection()->getDatabaseName();
```

### **📞 Support Channels**
1. **Check logs**: `storage/logs/laravel.log`
2. **Browser console**: F12 → Console tab
3. **Network tab**: Check failed requests
4. **Laravel docs**: https://laravel.com/docs
5. **Community forums**: Stack Overflow, Laravel forums

---

## 🎯 Prevention Tips

### **🛡️ Regular Maintenance**
```bash
# Weekly maintenance
php artisan cache:clear
php artisan config:clear
php artisan queue:restart
composer update
npm update
```

### **📊 Monitoring**
```bash
# Monitor system resources
php artisan tinker
memory_get_usage(true);
disk_free_space('/');

# Monitor queue
php artisan queue:monitor

# Monitor logs
tail -f storage/logs/laravel.log
```

### **🔄 Backup Strategy**
```bash
# Backup database
php artisan db:backup

# Backup files
cp -r storage storage_backup

# Version control
git add .
git commit -m "Backup before changes"
```

---

## 🎯 Conclusion

**Most issues can be resolved with these steps:**

1. **Check logs** - Always check error logs first
2. **Clear caches** - Cache issues are common
3. **Verify configuration** - Check .env settings
4. **Restart services** - Queue and web server
5. **Update dependencies** - Keep packages current

**Remember:** 90% of issues are configuration or cache-related!

**If all else fails, a fresh install often resolves persistent issues.** 🚀
