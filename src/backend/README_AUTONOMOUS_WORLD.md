# 🌍 Autonomous World Engine - Documentation

## 🎯 Tổng Quan

**Autonomous World Engine** là hệ thống cho phép WorldOS tự tiến hóa, tạo ra sự kiện và có thể giết main character một cách hợp lý dựa trên entropy và quy luật vật lý.

## 🏗️ Kiến Trúc Core

### 1. **CharacterSurvivalAggregate**
- Quản lý xác suất sống còn của nhân vật
- Tính toán survival probability dựa trên entropy, risk factors, và narrative weight
- Hỗ trợ plot armor và protection mechanisms

### 2. **SurvivalCheckEngine**
- Engine kiểm tra sinh tồn dựa trên entropy
- Xử lý multiple character survival checks
- Predict survival trends và identify high-risk characters

### 3. **ShockEvent System**
- 7 loại sự kiện: plague, civil_war, magic_collapse, famine, invasion, earthquake, myth_awakening
- Tự động generate dựa trên entropy và world state
- Apply risk modifiers đến characters

### 4. **TickWorldAction**
- Main engine tick cho autonomous world
- Update entropy, generate shock events, check survival
- Transaction-safe với proper error handling

## 📊 Cơ Chế Hoạt Động

### **Entropy-Driven Evolution**
```
Tick 1: Entropy 0.02 → Minor earthquake
Tick 5: Entropy 0.15 → Famine in south plains  
Tick 10: Entropy 0.35 → Civil war outbreak
Tick 15: Entropy 0.65 → Magic collapse
Tick 20: Entropy 0.85 → Myth awakening + character deaths
```

### **Character Survival Logic**
```
Base Survival: 0.8
- Entropy Modifier: -0.4 (high entropy)
- Risk Factors: -0.2 (injury + danger)
+ Plot Armor: +0.3 (main character protection)
+ Narrative Weight: +0.1 (story importance)
= Final: 0.6 survival probability
```

### **Main Character Death Conditions**
- Entropy > 0.7
- Narrative completion ≥ 60%
- Survival probability < 0.3
- Shock event severity > 0.8

## 🎮 Sử Dụng

### **Command Line**
```bash
# Run single world tick
php artisan world:tick --world-id=1

# Run multiple ticks
php artisan world:tick --world-id=1 --count=10

# Run all autonomous worlds
php artisan world:tick

# Force tick non-autonomous world
php artisan world:tick --world-id=2 --force

# Dry run simulation
php artisan world:tick --world-id=1 --dry-run
```

### **Programmatic Usage**
```php
use App\Application\World\Actions\TickWorldAction;

$world = $worldRepository->findById(1);
$characters = $characterRepository->findByWorldId(1);

$result = $tickAction->execute($world, collect($characters));

if ($result->hasDeaths()) {
    // Handle character deaths
    foreach ($result->survivalResults as $survivalResult) {
        if (!$survivalResult->survived) {
            // Process death
        }
    }
}
```

## ⚙️ Configuration

### **Environment Variables**
```env
WORLD_AUTONOMOUS=true
WORLD_TICK_INTERVAL=300
WORLD_DEBUG_LOGGING=true
WORLD_SAVE_SNAPSHOTS=false
```

### **Config File** (`config/world.php`)
- Entropy thresholds và increment rates
- Shock event weights và probabilities
- Character survival parameters
- Performance settings

## 📈 Monitoring & Metrics

### **Tick Metrics**
- Death count và survival rate
- Average survival probability
- Shock event count và types
- World stability score
- Execution time tracking

### **Survival Analytics**
- Survival trend prediction
- High-risk character identification
- Group survival rate calculation
- Entropy impact analysis

## 🔄 Integration với Existing Systems

### **AI Integration**
- AI không trực tiếp control world
- AI chỉ analyze và predict
- AI đề xuất mutation và shock events
- Human override luôn available

### **Story Engine**
- Story phải adapt đến shock events
- Character death tạo narrative opportunities
- World evolution tạo new story material
- Author có thể delay/redirect events

### **Database Schema**
- `character_survival`: Track survival data
- `world_dynamics`: World state per tick
- `shock_events`: Event history và effects

## ⚠️ Important Considerations

### **Narrative Impact**
- Main character death là feature, không phải bug
- Death phải có narrative meaning
- World continues after character death
- New protagonists emerge naturally

### **Performance**
- Batch processing cho multiple worlds
- Caching cho entropy calculations
- Queue system cho async ticks
- Proper indexing cho survival queries

### **Safety Mechanisms**
- Transaction safety
- Rollback capability
- Configurable thresholds
- Manual override options

## 🚀 Future Enhancements

### **Advanced Features**
- Multi-world interactions
- Cross-world shock events
- Character inheritance systems
- World memory và learning

### **AI Enhancement**
- Predictive analytics
- Pattern recognition
- Automatic balancing
- Narrative optimization

## 📝 Best Practices

1. **Start Small**: Test với low entropy worlds
2. **Monitor Closely**: Watch death rates và stability
3. **Backup Often**: Save world snapshots regularly
4. **Human Oversight**: Don't let AI run completely autonomous
5. **Gradual Rollout**: Enable features incrementally

---

**Warning**: This system fundamentally changes WorldOS from a story-driven platform to an autonomous civilization engine. Use with caution and proper testing.
