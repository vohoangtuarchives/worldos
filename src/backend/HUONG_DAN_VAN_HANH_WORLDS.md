# 🌍 Hướng Dẫn Vận Hành Worlds - WorldOS

## 🚀 Bắt Đầu Nhanh

### **1. Tạo World Mới**
```bash
# Tạo world võ thuật với autonomous mode
php artisan world:manage create --name="Thế Giới Kiếm Hiệp" --preset=martial --autonomous

# Tạo world tiên hiệp
php artisan world:manage create --name="Thiên Giới Tu Tiên" --preset=immortal --autonomous

# Tạo world mạt thế
php artisan world:manage create --name="Thế Giới Sau Tận Thế" --preset=apocalypse --autonomous
```

### **2. Khởi Động World**
```bash
# Bật autonomous mode cho world
php artisan world:manage start --world-id=1

# Kiểm tra trạng thái
php artisan world:manage status --world-id=1
```

### **3. Chạy Simulation**
```bash
# Chạy 10 tick
php artisan world:tick --world-id=1 --count=10

# Chạy continuous (background)
php artisan world:tick --world-id=1 &
```

## 📋 Các Lệnh Quản Lý

### **World Management**
```bash
# Liệt kê tất cả worlds
php artisan world:manage list

# Xem chi tiết world
php artisan world:manage status --world-id=1

# Xem trạng thái tất cả worlds
php artisan world:manage status
```

### **World Operations**
```bash
# Tạo world mới
php artisan world:manage create --name="Tên World" --preset=<type> --autonomous

# Khởi động autonomous mode
php artisan world:manage start --world-id=<id>

# Dừng autonomous mode
php artisan world:manage stop --world-id=<id>

# Reset world (cảnh báo!)
php artisan world:manage reset --world-id=<id> --force
```

### **Simulation Control**
```bash
# Chạy simulation
php artisan world:tick --world-id=<id> --count=<số>

# Dry run (xem trước)
php artisan world:tick --world-id=<id> --dry-run

# Force tick (kể cả non-autonomous)
php artisan world:tick --world-id=<id> --force
```

## 🎯 Các Loại Preset

### **1. Martial (Võ thuật)**
- **Năng lượng**: Nội lực
- **Xã hội**: Bang phái, giang hồ
- **Nhân vật**: Main character, master, rival, side characters
- **Sự kiện**: Đấu tranh giang hồ, bí kíp, danh dự

### **2. Immortal (Tiên hiệp)**
- **Năng lượng**: Linh khí
- **Xã hội**: Tông môn, cảnh giới
- **Nhân vật**: Tu sĩ, tông chủ, trưởng lão
- **Sự kiện**: Độ kiếp, tranh linh mạch, phi thăng

### **3. Apocalypse (Mạt thế)**
- **Năng lượng**: Tài nguyên khan hiếm
- **Xã hội**: Băng nhóm sinh tồn
- **Nhân vật**: Survivor, scientist, scavenger
- **Sự kiện**: Zombie, resource wars, mutation

### **4. Tech (Công nghệ)**
- **Năng lượng**: Công nghệ, AI
- **Xã hội**: Corporation, research labs
- **Nhân vật**: Researcher, CEO, hacker
- **Sự kiện**: AI rebellion, cyber attacks, tech wars

### **5. Myth (Thần thoại)**
- **Năng lượng**: Ma thuật, thần lực
- **Xã hội**: Oracle, chosen ones
- **Nhân vật**: Chosen one, oracle, guardian
- **Sự kiện**: Prophecy, divine intervention, ancient evils

## 📊 Theo Dõi World

### **Status Indicators**
- **Tick**: Số lần simulation đã chạy
- **Entropy**: Mức độ bất ổn (0.0 - 1.0)
- **Autonomous**: 🤖 đang chạy / ⏸️ manual
- **Characters**: Tổng số nhân vật
- **Alive/Dead**: Số nhân vật sống/chết

### **Entropy Levels**
```
0.0 - 0.2: 🟢 Stable (Yên bình)
0.3 - 0.5: 🟡 Normal (Bình thường)
0.6 - 0.8: 🟠 Turbulent (Bất ổn)
0.9 - 1.0: 🔴 Critical (Nguy cơ sụp đổ)
```

### **Shock Events Theo Entropy**
```
Entropy < 0.3: Natural disasters (earthquake, famine)
Entropy 0.3-0.7: Social conflicts (civil war, invasion)
Entropy > 0.7: Catastrophic events (magic collapse, myth awakening)
```

## ⚙️ Configuration

### **Environment Variables**
```env
# Bắt/tắt autonomous mode
WORLD_AUTONOMOUS=true

# Tick interval (giây)
WORLD_TICK_INTERVAL=300

# Debug logging
WORLD_DEBUG_LOGGING=true

# Save snapshots
WORLD_SAVE_SNAPSHOTS=false
```

### **Config File** (`config/world.php`)
```php
'entropy' => [
    'base_increment' => 0.02,        // Tăng entropy mỗi tick
    'collapse_threshold' => 0.9,     // Ngưỡng sụp đổ
    'critical_threshold' => 0.7,     // Ngưỡng nguy hiểm
],

'character_survival' => [
    'base_survival_rate' => 0.8,     // Tỷ lệ sống cơ bản
    'survival_threshold' => 0.3,     // Ngưỡng chết
    'main_character_protection' => 0.4, // Bảo vệ main character
],
```

## 🔄 Workflow Thực Tế

### **1. Setup World Mới**
```bash
# Step 1: Tạo world
php artisan world:manage create --name="Thử Nghiệm" --preset=martial --autonomous

# Step 2: Kiểm tra status
php artisan world:manage status --world-id=1

# Step 3: Chạy simulation thử
php artisan world:tick --world-id=1 --count=5 --dry-run
```

### **2. Monitor Development**
```bash
# Chạy 10 tick và xem kết quả
php artisan world:tick --world-id=1 --count=10

# Kiểm tra status sau khi chạy
php artisan world:manage status --world-id=1

# Xem log nếu có
tail -f storage/logs/laravel.log | grep "World tick"
```

### **3. Advanced Operations**
```bash
# Reset world nếu cần
php artisan world:manage reset --world-id=1 --force

# Tạo batch worlds
for i in {1..5}; do
    php artisan world:manage create --name="World $i" --preset=martial --autonomous
done

# Chạy tất cả autonomous worlds
php artisan world:tick
```

## 🚨 Cảnh Báo Quan Trọng

### **⚠️ Main Character Death**
- Main character có thể chết khi:
  - Entropy > 0.7
  - Hoàn thành narrative ≥ 60%
  - Survival probability < 0.3
- Death là **feature**, không phải bug
- World tiếp tục evolve sau character death

### **⚠️ World Collapse**
- Entropy > 0.9 có thể gây collapse
- Collapse không xóa world
- World có thể "rebirth" với new conditions

### **⚠️ Performance**
- Multiple worlds consume resources
- Monitor memory và CPU usage
- Use queue cho heavy simulations

## 📱 Monitoring Tools

### **Real-time Status**
```bash
# Watch world status
watch -n 5 'php artisan world:manage status'

# Monitor entropy growth
php artisan world:tick --world-id=1 --count=1 | grep "Entropy"
```

### **Log Analysis**
```bash
# Xem recent world events
grep "World tick" storage/logs/laravel.log | tail -10

# Xem character deaths
grep "Character died" storage/logs/laravel.log | tail -10

# Xem shock events
grep "Shock event generated" storage/logs/laravel.log | tail -10
```

## 🎯 Best Practices

### **1. Start Small**
- Bắt đầu với 1 world
- Test với low entropy settings
- Monitor character survival rates

### **2. Backup Regularly**
```bash
# Export world data
php artisan world:export --world-id=1

# Create database backup
mysqldump worldos > backup_$(date +%Y%m%d).sql
```

### **3. Monitor Closely**
- Watch entropy levels
- Track death rates
- Adjust config if needed

### **4. Human Oversight**
- Don't let AI run completely autonomous
- Review major events
- Override when necessary

## 🔧 Troubleshooting

### **Common Issues**

**Q: World không chạy autonomous?**
```bash
# Kiểm tra config
php artisan config:cache
php artisan world:manage start --world-id=<id>
```

**Q: Character chết quá nhiều?**
```bash
# Kiểm tra entropy level
php artisan world:manage status --world-id=<id>

# Adjust config nếu cần
# Edit config/world.php -> character_survival section
```

**Q: Performance chậm?**
```bash
# Reduce tick count
php artisan world:tick --world-id=<id> --count=1

# Enable caching
php artisan config:cache
php artisan route:cache
```

---

**🌍 WorldOS Autonomous Engine - Worlds vận hành tự động, characters có thể chết, stories emerge naturally!**
