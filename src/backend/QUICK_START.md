# 🚀 Quick Start - WorldOS (5 Minutes)

## ⚡ Super Fast Setup

### **1. Clone & Install**
```bash
git clone <repository-url>
cd worldos

# Install dependencies
composer install && npm install
```

### **2. Environment Setup**
```bash
# Copy environment file
copy .env.example .env

# Generate key
php artisan key:generate

# Setup SQLite (fastest)
php artisan migrate
```

### **3. Build & Start**
```bash
# Build frontend
npm run build

# Start server
php artisan serve
```

### **4. Access & Create**
```
🌐 http://localhost:8000/worlds
🎮 Click "Create World"
📊 Access dashboard: http://localhost:8000/worlds/1/dashboard
```

---

## 🎮 One-Command World Creation

```bash
# Create and start world in one command
php artisan world:manage --action=create --name="My World" --preset=martial --characters=10 && php artisan world:manage --action=start --world-id=1
```

---

## 📊 Immediate Features Available

✅ **World Management** - Create, start, stop worlds  
✅ **Real-time Dashboard** - Live charts and stats  
✅ **Character Survival** - Autonomous character behavior  
✅ **Intelligence System** - Multi-source intelligence gathering  
✅ **Material Tracking** - Resource management and optimization  
✅ **Shock Events** - Dynamic world events  
✅ **Lifecycle Analysis** - World longevity predictions  

---

## 🔧 Quick Troubleshooting

### **Issue: "Connection refused"**
```bash
# Check if port 8000 is free
netstat -an | findstr :8000

# Use different port
php artisan serve --port=8001
```

### **Issue: "Database not found"**
```bash
# Create database file
touch database/database.sqlite

# Re-run migrations
php artisan migrate:fresh --seed
```

### **Issue: "Assets not loading"**
```bash
# Recreate storage link
php artisan storage:link

# Clear cache
php artisan cache:clear
```

---

## 🎯 Test Your World

### **1. Create World**
- Visit `http://localhost:8000/worlds`
- Click "Create World"
- Name: "Test World"
- Preset: "martial"
- Characters: 5

### **2. Start Autonomous Mode**
- Go to dashboard
- Click "Start Autonomous"
- Watch real-time updates

### **3. Monitor Progress**
- Watch entropy increase
- See character interactions
- Track material degradation
- View intelligence reports

---

## 📱 Mobile Access

```
📱 http://localhost:8000/worlds
📊 Responsive dashboard works on mobile
🎮 Touch-friendly controls
```

---

## 🎯 Success Indicators

✅ **World created** - Appears in world list  
✅ **Autonomous mode** - Green "Running" status  
✅ **Real-time updates** - Charts updating every 5 seconds  
✅ **Characters alive** - Population > 0  
✅ **Materials tracked** - Resource count > 0  
✅ **Intelligence active** - Reports being generated  

---

## 🚀 Next Steps

1. **Explore Dashboard** - Try all controls and charts
2. **Create Multiple Worlds** - Test different presets
3. **Run CLI Commands** - Try world management commands
4. **Check Logs** - Monitor system performance
5. **Read Full Documentation** - See HUONG_DAN_KHOI_CHAY.md

---

## 🎯 You're Ready!

**WorldOS is now running with all features active!** 🎮✨

Your autonomous worlds are ready to:
- 🧠 Think and make decisions
- 🎭 Interact with each other  
- 📊 Generate intelligence
- 📦 Manage resources
- 🌍 Evolve over time

**Enjoy your autonomous worlds!** 🌐🚀
