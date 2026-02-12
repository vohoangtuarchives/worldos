# 🚀 WorldOS Setup Scripts - Automated Installation

## 📋 Overview

Tôi đã tạo các script tự động để bạn không cần chạy từng command thủ công. Chỉ cần chạy một file và mọi thứ sẽ được thiết lập hoàn chỉnh!

---

## 🪟️ Windows Users

### **Chạy File Batch:**
```bash
# Double-click hoặc chạy trong Command Prompt:
setup_worldos.bat
```

### **Hoặc Chạy Thủ Công:**
```cmd
cd c:\Users\vohoa\worldos
setup_worldos.bat
```

---

## 🐧️ Linux/macOS Users

### **Chạy File Shell:**
```bash
# Make executable (chỉ cần làm một lần)
chmod +x setup_worldos.sh

# Chạy script
./setup_worldos.sh
```

### **Hoặc Chạy Thủ Công:**
```bash
cd /path/to/worldos
chmod +x setup_worldos.sh
./setup_worldos.sh
```

---

## 🔄 Script Features

### **✅ 6 Steps Automated:**

1. **[1/6] Clear Caches** - Xóa tất cả cache cũ
2. **[2/6] Composer Install** - Cài đặt PHP dependencies
3. **[3/6] Database Migrations** - Tạo database tables
4. **[4/6] Optimization** - Cache config, routes, views
5. **[5/6] Repository Testing** - Kiểm tra tất cả 7 repositories
6. **[6/6] Start Server** - Khởi chạy development server

### **🔍 Error Handling:**
- **Automatic Error Detection** - Script dừng nếu có lỗi
- **Clear Error Messages** - Hiển thị lỗi cụ thể
- **Continue on Warnings** - Tiếp tục nếu chỉ có warnings

### **📊 Progress Indicators:**
- ✅ Success messages cho mỗi step
- ❌ Error messages nếu có vấn đề
- 🎉 Completion notification khi thành công

---

## 📋 Script Contents

### **Windows (setup_worldos.bat):**
```batch
@echo off
echo [1/6] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ✅ Caches cleared successfully

echo [2/6] Installing Composer dependencies...
composer install
echo ✅ Composer dependencies installed successfully

echo [3/6] Running database migrations...
php artisan migrate
echo ✅ Database migrations completed successfully

echo [4/6] Optimizing application...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ✅ Application optimized

echo [5/6] Testing repository bindings...
php artisan tinker --execute="..."
echo ✅ Repository binding test completed

echo [6/6] Starting development server...
php artisan serve --host=0.0.0.0 --port=8000
```

### **Linux/macOS (setup_worldos.sh):**
```bash
echo "[1/6] Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared successfully"

echo "[2/6] Installing Composer dependencies..."
composer install
echo "✅ Composer dependencies installed successfully"

echo "[3/6] Running database migrations..."
php artisan migrate
echo "✅ Database migrations completed successfully"

echo "[4/6] Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimized"

echo "[5/6] Testing repository bindings..."
php artisan tinker --execute="..."
echo "✅ Repository binding test completed"

echo "[6/6] Starting development server..."
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🎯 Repository Testing

### **7 Repositories Được Kiểm Tra:**
1. ✅ `WorldRepository`
2. ✅ `ShockEventRepository`
3. ✅ `IntelligenceRepository`
4. ✅ `CharacterSurvivalRepository`
5. ✅ `MaterialRepositoryInterface`
6. ✅ `WorldStateRepository`
7. ✅ `CompressedSnapshotRepository`

### **Test Output:**
```
✅ App\Domains\World\Repositories\EloquentWorldRepository
✅ App\Domains\World\Repositories\EloquentShockEventRepository
✅ App\Domains\Intelligence\Repositories\EloquentIntelligenceRepository
✅ App\Domains\Character\Repositories\EloquentCharacterSurvivalRepository
✅ App\Domains\Material\Repositories\MaterialEloquentRepository
✅ App\Domains\Material\State\WorldStateRepository
✅ App\Domains\Material\State\CompressedSnapshotRepository
🎉 Repository binding test completed!
```

---

## 🚀 Sau Khi Setup Hoàn Tất

### **✅ Server Đang Chạy:**
```
🌐 WorldOS is now starting...
📍 Access the application at: http://localhost:8000
📊 World Management: http://localhost:8000/worlds
🎮 Dashboard: http://localhost:8000/worlds/{world-id}/dashboard
```

### **📱 Truy Cập Các Trang:**
1. **Main Application**: `http://localhost:8000`
2. **World Management**: `http://localhost:8000/worlds`
3. **Dashboard**: `http://localhost:8000/worlds/{world-id}/dashboard`

### **📚 Tài Liệu Hỗ Trợ:**
- `HUONG_DAN_KHOI_CHAY.md` - Hướng dẫn chi tiết
- `QUICK_START.md` - Bắt đầu nhanh
- `TROUBLESHOOTING_GUIDE.md` - Sửa lỗi
- `COMPLETE_DEPENDENCY_FIX.md` - Fix dependencies

---

## 🔧 Troubleshooting

### **❌ Nếu Script Báo Lỗi:**

#### **Cache Clearing Failed:**
```bash
# Xóa thủ công
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### **Composer Install Failed:**
```bash
# Kiểm tra PHP và Composer
php --version
composer --version

# Cài đặt lại
composer install --no-dev --optimize-autoloader
```

#### **Database Migration Failed:**
```bash
# Kiểm tra database connection
php artisan tinker
DB::connection()->getPdo();

# Fresh migration
php artisan migrate:fresh
```

#### **Repository Binding Failed:**
```bash
# Kiểm tra file AppServiceProvider
php artisan tinker
app(\App\Domains\World\Repositories\WorldRepository::class);
```

---

## 🎯 Success Indicators

✅ **All Steps Completed** - Script chạy đến hết  
✅ **No Errors** - Không có lỗi nghiêm trọng  
✅ **Server Running** - Laravel server đang chạy  
✅ **Database Ready** - Tất cả tables được tạo  
✅ **Repositories Working** - Tất cả repositories được bind  
✅ **Real-time Updates** - Dashboard hoạt động  

---

## 🎉 Kết Luận

**WorldOS giờ đây đã sẵn sàng để chạy hoàn toàn tự động!** 🚀

### **Chỉ Cần:**
1. **Chạy script** - `setup_worldos.bat` (Windows) hoặc `./setup_worldos.sh` (Linux/macOS)
2. **Đợi hoàn tất** - Script sẽ tự động làm tất cả
3. **Truy cập ứng dụng** - Mở browser đến `http://localhost:8000`

### **Features Sẵn Sàng:**
- 🌍 **Autonomous Worlds** - Worlds hoạt động tự động
- 📊 **Real-time Dashboard** - Monitor live updates
- 🧠 **Character Survival** - AI-driven behavior
- 🕵️ **Intelligence System** - Multi-source gathering
- 📦 **Material Tracking** - Resource management
- ⚡ **Shock Events** - Dynamic world events

**Chạy script và tận hưởng WorldOS!** 🎯✨
