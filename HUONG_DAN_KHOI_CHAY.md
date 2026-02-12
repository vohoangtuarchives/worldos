# 🚀 Hướng Dẫn Khởi Chạy WorldOS - Hoàn Chỉnh

## 📋 Yêu Cầu Hệ Thống

### **Phần Cứng Tối Thiểu**
- **CPU**: 2 cores trở lên
- **RAM**: 4GB trở lên  
- **Storage**: 10GB free space
- **OS**: Windows 10/11, macOS, Linux

### **Phần Mềm Yêu Cầu**
- **PHP**: 8.2 trở lên
- **Composer**: 2.0 trở lên
- **Node.js**: 16.0 trở lên
- **NPM**: 8.0 trở lên
- **Database**: MySQL 8.0 hoặc SQLite 3.x
- **Redis**: 6.0 trở lên (recommended)

---

## 🛠️ Bước 1: Cài Đặt Môi Trường

### **1.1 Cài Đặt PHP & Composer**
```bash
# Windows (sử dụng XAMPP/WAMP)
# Download XAMPP từ https://www.apachefriends.org/
# PHP 8.2+ được include sẵn

# Kiểm tra phiên bản
php --version
composer --version
```

### **1.2 Cài Đặt Node.js & NPM**
```bash
# Download từ https://nodejs.org/
# Hoặc sử dụng nvm
nvm install 18
nvm use 18

# Kiểm tra phiên bản
node --version
npm --version
```

### **1.3 Cài Đặt Database**
```bash
# Option 1: MySQL (recommended)
# Download từ https://dev.mysql.com/downloads/
# Tạo database: worldos

# Option 2: SQLite (development)
# SQLite được include trong PHP
```

### **1.4 Cài Đặt Redis**
```bash
# Windows
# Download từ https://github.com/microsoftarchive/redis/releases

# Linux
sudo apt-get install redis-server

# macOS
brew install redis
```

---

## 📁 Bước 2: Setup Project

### **2.1 Clone Repository**
```bash
git clone <repository-url>
cd worldos
```

### **2.2 Cài Đặt PHP Dependencies**
```bash
composer install --optimize-autoloader --no-dev
```

### **2.3 Cài Đặt Frontend Dependencies**
```bash
npm install
```

### **2.4 Setup Environment File**
```bash
# Copy file môi trường
copy .env.example .env

# Generate application key
php artisan key:generate
```

---

## ⚙️ Bước 3: Cấu Hình Environment

### **3.1 Cấu Hình Database**
```bash
# Mở file .env và cấu hình:

# Option 1: MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worldos
DB_USERNAME=root
DB_PASSWORD=your_password

# Option 2: SQLite (development)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### **3.2 Cấu Hình Cache & Queue**
```bash
# Redis configuration
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### **3.3 Cấu Hình Services**
```bash
# OpenAI API (cho AI features)
# Tạo file config/services.php
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-3.5-turbo
```

---

## 🗄️ Bước 4: Database Setup

### **4.1 Tạo Database**
```bash
# MySQL
mysql -u root -p
CREATE DATABASE worldos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# SQLite (tự động tạo)
touch database/database.sqlite
```

### **4.2 Run Migrations**
```bash
php artisan migrate
```

### **4.3 Seed Database**
```bash
php artisan db:seed
```

---

## 🎨 Bước 5: Frontend Build

### **5.1 Compile Assets**
```bash
# Development
npm run dev

# Production
npm run build
```

### **5.2 Link Assets**
```bash
php artisan storage:link
```

---

## 🚀 Bước 6: Khởi Chạy Services

### **6.1 Start Development Server**
```bash
# Terminal 1: Laravel server
php artisan serve --host=0.0.0.0 --port=8000
```

### **6.2 Start Queue Worker**
```bash
# Terminal 2: Queue worker
php artisan queue:work --tries=3 --timeout=60
```

### **6.3 Start Redis**
```bash
# Terminal 3: Redis server
redis-server
```

### **6.4 Start Scheduler**
```bash
# Terminal 4: Task scheduler
php artisan schedule:work
```

---

## 🌐 Bước 7: Truy Cập Application

### **7.1 Main Application**
```
http://localhost:8000
```

### **7.2 World Management**
```
http://localhost:8000/worlds
```

### **7.3 Dashboard**
```
http://localhost:8000/worlds/{world-id}/dashboard
```

---

## 🎮 Bước 8: Tạo và Quản Lý Worlds

### **8.1 Tạo World Mới**
```bash
# Via CLI
php artisan world:manage --action=create --name="My World" --preset=martial

# Via UI
# Truy cập http://localhost:8000/worlds/create
```

### **8.2 Khởi Chạy Autonomous Mode**
```bash
# Via CLI
php artisan world:manage --action=start --world-id=1

# Via UI
# Truy cập dashboard và click "Start Autonomous"
```

### **8.3 Tick World**
```bash
# Single tick
php artisan world:tick --world-id=1

# Multiple ticks
php artisan world:tick --world-id=1 --count=10

# All worlds
php artisan world:tick --all
```

---

## 📊 Bước 9: Monitoring & Debugging

### **9.1 Check System Status**
```bash
# Check queue status
php artisan queue:monitor

# Check cache status
php artisan cache:status

# Check database connection
php artisan tinker
DB::connection()->getPdo();
```

### **9.2 View Logs**
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue logs
tail -f storage/logs/queue.log

# Custom logs
tail -f storage/logs/worldos.log
```

### **9.3 Debug Tools**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reset queue
php artisan queue:restart
```

---

## 🔧 Bước 10: Cấu Hình Production

### **10.1 Environment Production**
```bash
# Update .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Production database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=worldos_prod
DB_USERNAME=worldos_user
DB_PASSWORD=secure_password
```

### **10.2 Optimize Performance**
```bash
# Optimize autoloader
composer dump-autoload --optimize

# Optimize cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize assets
npm run build
```

### **10.3 Setup Supervisor**
```bash
# Install supervisor
sudo apt-get install supervisor

# Create config file
sudo nano /etc/supervisor/conf.d/worldos-worker.conf
```

```ini
[program:worldos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/worldos/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/worldos/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start worldos-worker:*
```

---

## 🎯 Bước 11: Testing System

### **11.1 Run Tests**
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter WorldTest

# Run with coverage
php artisan test --coverage
```

### **11.2 Manual Testing**
```bash
# Test API endpoints
curl -X GET http://localhost:8000/api/worlds

# Test WebSocket connection
wscat -c ws://localhost:8000/ws/worlds/1
```

---

## 🚨 Bước 12: Troubleshooting

### **12.1 Common Issues**

#### **Issue: Database Connection Failed**
```bash
# Solution:
# 1. Check database service is running
# 2. Verify .env database credentials
# 3. Check database exists
# 4. Test connection manually
php artisan tinker
DB::connection()->getPdo();
```

#### **Issue: Queue Not Processing**
```bash
# Solution:
# 1. Check Redis is running
redis-cli ping

# 2. Restart queue worker
php artisan queue:restart
php artisan queue:work

# 3. Check queue configuration
php artisan config:cache
```

#### **Issue: Assets Not Loading**
```bash
# Solution:
# 1. Run npm install
npm install

# 2. Compile assets
npm run build

# 3. Create storage link
php artisan storage:link

# 4. Clear cache
php artisan cache:clear
```

#### **Issue: WebSocket Connection Failed**
```bash
# Solution:
# 1. Check WebSocket server is running
# 2. Verify firewall settings
# 3. Check browser console for errors
# 4. Test with polling fallback
```

### **12.2 Performance Issues**

#### **High Memory Usage**
```bash
# Optimize PHP memory limit
# In .env: 
MEMORY_LIMIT=512M

# Optimize queue worker
php artisan queue:work --memory=256
```

#### **Slow Database Queries**
```bash
# Enable query logging
DB::enableQueryLog();

# Check slow queries
php artisan tinker
DB::getQueryLog();

# Add database indexes
php artisan migrate --step
```

---

## 📱 Bước 13: Mobile Access

### **13.1 Responsive Design**
- Desktop: Full functionality
- Tablet: Optimized dashboard
- Mobile: Essential features

### **13.2 PWA Support**
```bash
# Build PWA assets
npm run build:pwa

# Service worker tự động được tạo
# Truy cập qua mobile browser
```

---

## 🔐 Bước 14: Security Setup

### **14.1 Basic Security**
```bash
# Set file permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Generate secure key
php artisan key:generate

# Enable HTTPS in production
APP_URL=https://your-domain.com
```

### **14.2 Advanced Security**
```bash
# Setup firewall
sudo ufw allow 8000
sudo ufw allow 6379

# Setup SSL certificate
certbot --nginx -d your-domain.com
```

---

## 🎯 Bước 15: First World Creation

### **15.1 Via CLI**
```bash
# Create martial world
php artisan world:manage \
    --action=create \
    --name="Thế Giới Kiếm Hiệp" \
    --preset=martial \
    --characters=20

# Create tech world  
php artisan world:manage \
    --action=create \
    --name="Cyberpunk Future" \
    --preset=tech \
    --characters=15
```

### **15.2 Via Web Interface**
1. Truy cập `http://localhost:8000/worlds`
2. Click "Create World"
3. Điền thông tin:
   - Name: "My First World"
   - Preset: "martial"
   - Initial Characters: 10
4. Click "Create"

### **15.3 Start Autonomous Mode**
```bash
# Via CLI
php artisan world:manage --action=start --world-id=1

# Via UI
1. Truy cập dashboard: `http://localhost:8000/worlds/1/dashboard`
2. Click "Start Autonomous"
3. Watch real-time updates
```

---

## 🎮 Quick Start Commands

```bash
# 1. Install dependencies
composer install && npm install

# 2. Setup environment
copy .env.example .env && php artisan key:generate

# 3. Setup database
php artisan migrate && php artisan db:seed

# 4. Build frontend
npm run build

# 5. Start services
php artisan serve --host=0.0.0.0 --port=8000
php artisan queue:work
redis-server

# 6. Create first world
php artisan world:manage --action=create --name="Test World" --preset=martial

# 7. Start autonomous mode
php artisan world:manage --action=start --world-id=1

# 8. Access application
# http://localhost:8000/worlds/1/dashboard
```

---

## 🎯 Verification Checklist

### **✅ Pre-Launch Checklist**
- [ ] PHP 8.2+ installed
- [ ] Composer dependencies installed
- [ ] Node.js 16+ installed
- [ ] NPM dependencies installed
- [ ] Database configured and migrated
- [ ] Redis running
- [ ] Environment file configured
- [ ] Application key generated
- [ ] Assets compiled
- [ ] Storage linked
- [ ] Queue worker running
- [ ] Scheduler running

### **✅ Post-Launch Verification**
- [ ] Application accessible via browser
- [ ] World creation working
- [ ] Autonomous mode functioning
- [ ] Real-time updates working
- [ ] Charts displaying correctly
- [ ] API endpoints responding
- [ ] WebSocket connection established
- [ ] Queue processing jobs
- [ ] Logs being written
- [ ] Performance acceptable

---

## 🎯 Success Indicators

### **🌐 Application Running**
```
✅ Laravel server: http://localhost:8000
✅ Queue worker: Processing jobs
✅ Redis server: Connected
✅ Database: Migrated and seeded
✅ Frontend: Compiled and linked
```

### **🎮 World System Working**
```
✅ World creation: Success
✅ Character generation: Success
✅ Autonomous mode: Running
✅ Real-time updates: Working
✅ Charts: Displaying data
✅ Intelligence: Gathering
✅ Materials: Tracking
```

### **📊 Monitoring Active**
```
✅ Logs: Writing correctly
✅ Performance: Acceptable
✅ Memory: Within limits
✅ Database: Responsive
✅ Cache: Hit rate good
```

---

## 🎯 Kết Luận

**WorldOS giờ đây đã sẵn sàng hoạt động!** 🚀

Với hướng dẫn này, bạn có thể:

🏗️ **Setup hoàn chỉnh** - Từ dependencies đến production

🎮 **Quản lý worlds** - Tạo, start, stop, monitor

📊 **Real-time monitoring** - Dashboard với live updates

🔧 **Troubleshooting** - Solutions cho common issues

🚀 **Performance optimization** - Production-ready configuration

**Chúc bạn có trải nghiệm tuyệt vời với WorldOS!** 🎯✨

**Worlds của bạn giờ đây có thể hoạt động tự động 24/7 với full monitoring và control!** 🌐🎮
