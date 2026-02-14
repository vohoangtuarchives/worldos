# 📦 Material Tracking System - WorldOS

## 🎯 Tổng Quan

**Material Tracking System** cho phép WorldOS theo dõi, quản lý và sắp xếp materials trong autonomous worlds, bao gồm tracking trạng thái, độ bền, và sự phân bố tài nguyên.

## 🏗️ Kiến Trúc Core

### **1. WorldMaterialTracker**
- Main service cho material tracking
- Apply world effects (entropy, shock events)
- Update material states và durability
- Record changes và history

### **2. MaterialInstance**
- Value object cho từng material instance
- Track strength, durability, purity, location, owner
- Support degradation, upgrade, transfer operations

### **3. WorldMaterialCollection**
- Collection với filtering và analysis capabilities
- Type breakdown, location breakdown, owner breakdown
- State tracking và optimization suggestions

### **4. MaterialState**
- Enum cho các trạng thái material:
  - STABLE, WORN, DAMAGED, BROKEN
  - UNSTABLE, CORRUPTED, RETIRED

## 🔄 Quy Trình Tracking

### **1. Material Gathering**
```php
$collection = $materialTracker->trackWorldMaterials($world);
```

### **2. State Updates**
- Apply entropy-based decay
- Apply time-based degradation
- Calculate new states based on conditions

### **3. World Effects Application**
- High entropy effects (fragile materials degrade faster)
- Shock event effects (damage, destruction, corruption)
- Faction effects (ownership changes, protection)

### **4. Change Recording**
- Track all material changes
- Maintain audit trail
- Support historical analysis

## 🎮 Sử Dụng Material System

### **Command Line Tools**
```bash
# Track materials và update states
php artisan world:materials --world-id=1 --track

# Show statistics
php artisan world:materials --world-id=1 --statistics

# Show optimization suggestions
php artisan world:materials --world-id=1 --optimize

# Add material to world
php artisan world:materials --world-id=1 --add="sword,5,strength_level=8"

# Remove material instance
php artisan world:materials --world-id=1 --remove="mat_inst_123"

# Transfer material between worlds
php artisan world:materials --transfer="world1,world2,mat_inst_123"

# Degrade material
php artisan world:materials --world-id=1 --degrade="mat_inst_123"

# Upgrade material
php artisan world:materials --world-id=1 --upgrade="mat_inst_123"
```

### **Sample Output**
```
📦 Material Management for World 1: Thế Giới Kiếm Hiệp

📊 Material Statistics:
┌─────────────────────┬─────────┐
│ Metric              │ Value   │
├─────────────────────┼─────────┤
│ Total Instances     │ 42      │
│ Active Instances    │ 35      │
│ Broken Instances    │ 3       │
│ Average Durability  │ 67.5    │
│ Scarcity Level      │ limited │
│ Abundance Level     │ moderate│
└─────────────────────┴─────────┘

📋 Material Types:
  weapon: 15
  armor: 8
  tool: 7
  resource: 12

📍 Location Distribution:
  north_capital: 12
  south_plains: 8
  east_mountains: 10
  west_coast: 7
  central_desert: 5

🎯 Optimization Suggestions:
  🔴 repair: Repair before complete failure
     Instance: mat_inst_456
  🟡 redistribution: Move to location where material is needed
     Instance: mat_inst_789
```

## 📊 Material States & Conditions

### **State Transitions**
```
STABLE → WORN (durability < 80%)
WORN → DAMAGED (durability < 50%)
DAMAGED → BROKEN (durability <= 0)

STABLE → UNSTABLE (instability > 0.5)
STABLE → CORRUPTED (corruption > 0.3)
ANY → RETIRED (manual removal)
```

### **Durability Levels**
```
100-80%: STABLE (hoạt động bình thường)
79-50%:  WORN (mòn mỏi, cần bảo trì)
49-20%:  DAMAGED (hư hỏng, cần sửa chữa)
19-0%:   BROKEN (hỏng hoàn toàn)
```

### **Special Conditions**
- **Magical Materials**: Bị ảnh hưởng bởi magic collapse
- **Fragile Materials**: Dễ hỏng trong high entropy
- **Underutilized**: Không được sử dụng > 14 ngày
- **Redundant**: Trùng lặp với các materials khác

## 🌍 World Effects trên Materials

### **Entropy Effects**
```php
// High entropy (>0.7)
if ($entropy > 0.7) {
    // Fragile materials degrade faster
    if ($instance->isFragile()) {
        $instance = $instance->degrade(0.05);
    }
    
    // Magical materials become unstable
    if ($instance->isMagical()) {
        $instance = $instance->addInstability(0.1);
    }
}
```

### **Shock Event Effects**
```php
$effects = [
    'plague' => ['contamination' => 0.3, 'degradation' => 0.2],
    'civil_war' => ['damage' => 0.4, 'destruction' => 0.1],
    'magic_collapse' => ['instability' => 0.5, 'corruption' => 0.3],
    'earthquake' => ['damage' => 0.5, 'destruction' => 0.2],
];
```

### **Faction Effects**
- Ownership changes
- Protection bonuses
- Access restrictions

## 📈 Material Analytics

### **Statistics Metrics**
```php
$stats = [
    'total_instances' => 42,
    'active_instances' => 35,
    'retired_instances' => 5,
    'broken_instances' => 2,
    'average_durability' => 67.5,
    'average_strength' => 6.2,
    'total_value' => 1250.75,
    'scarcity_level' => 'limited',
    'abundance_level' => 'moderate',
];
```

### **Type Breakdown**
```php
'types' => [
    'weapon' => 15,
    'armor' => 8,
    'tool' => 7,
    'resource' => 12,
],
```

### **Location Breakdown**
```php
'locations' => [
    'north_capital' => 12,
    'south_plains' => 8,
    'east_mountains' => 10,
    'west_coast' => 7,
    'central_desert' => 5,
],
```

### **State Breakdown**
```php
'states' => [
    'stable' => 25,
    'worn' => 8,
    'damaged' => 4,
    'broken' => 2,
    'retired' => 3,
],
```

## 🎯 Optimization System

### **Optimization Types**
```php
$optimizations = [
    [
        'type' => 'repair',
        'instance_id' => 'mat_inst_123',
        'suggestion' => 'Repair before complete failure',
        'priority' => 'high'
    ],
    [
        'type' => 'redistribution',
        'instance_id' => 'mat_inst_456',
        'suggestion' => 'Redistribute to scarce areas',
        'priority' => 'low'
    ],
];
```

### **Priority Levels**
- **🔴 High**: Critical repairs, immediate failures
- **🟡 Medium**: Maintenance, optimization opportunities
- **🟢 Low**: Redistribution, minor improvements

## 🔄 Material Operations

### **Add Material**
```php
$materialTracker->addMaterialToWorld(
    $world,
    $material,
    $quantity = 5,
    $properties = [
        'strength_level' => 8,
        'durability' => 100,
        'location' => 'north_capital',
        'owner' => 'faction_1'
    ]
);
```

### **Remove Material**
```php
$materialTracker->removeMaterialFromWorld(
    $world,
    $instanceId,
    $reason = 'manual_removal'
);
```

### **Transfer Material**
```php
$materialTracker->transferMaterial(
    $fromWorld,
    $toWorld,
    $instanceId,
    $newOwner = 'faction_2'
);
```

### **Degrade Material**
```php
$materialTracker->degradeMaterial(
    $world,
    $instanceId,
    $degradationAmount = 0.1
);
```

### **Upgrade Material**
```php
$upgrades = [
    ['type' => 'strength', 'amount' => 1.0],
    ['type' => 'durability', 'amount' => 0.2],
    ['type' => 'enchantment', 'amount' => 0.5]
];

$materialTracker->upgradeMaterial($world, $instanceId, $upgrades);
```

## 📊 Database Schema

### **world_materials Table**
```sql
CREATE TABLE world_materials (
    id BIGINT PRIMARY KEY,
    instance_id VARCHAR(255) UNIQUE,
    world_id VARCHAR(255) NOT NULL,
    material_id VARCHAR(255) NOT NULL,
    strength_level DECIMAL(3,1) NOT NULL,
    durability DECIMAL(5,2) NOT NULL,
    purity DECIMAL(3,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NULL,
    state ENUM('stable','worn','damaged','broken','unstable','corrupted','retired'),
    instability DECIMAL(3,2) DEFAULT 0.0,
    corruption DECIMAL(3,2) DEFAULT 0.0,
    metadata JSON,
    created_at TIMESTAMP,
    last_used_at TIMESTAMP NULL,
    retired_at TIMESTAMP NULL,
    retirement_reason VARCHAR(255)
);
```

### **material_changes Table**
```sql
CREATE TABLE material_changes (
    id BIGINT PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    instance_id VARCHAR(255) NOT NULL,
    change_type ENUM('add','update','remove','transfer','degrade','upgrade','retire'),
    old_state JSON,
    new_state JSON,
    reason VARCHAR(255),
    metadata JSON,
    occurred_at TIMESTAMP
);
```

## ⚙️ Configuration

### **Material Settings** (`config/materials.php`)
```php
return [
    'tracking' => [
        'decay_rate' => 0.01,              // 1% per tick
        'degradation_rate' => 0.02,         // 2% per use
        'scarcity_threshold' => 0.3,        // Below this is scarce
        'abundance_threshold' => 0.8,       // Above this is abundant
    ],
    
    'states' => [
        'stable_threshold' => 80,
        'worn_threshold' => 50,
        'damaged_threshold' => 20,
    ],
    
    'effects' => [
        'entropy_multiplier' => 2.0,
        'magical_instability_rate' => 0.1,
        'fragile_degradation_rate' => 0.05,
    ],
    
    'optimization' => [
        'underutilized_days' => 14,
        'redundancy_threshold' => 3,
        'repair_priority_threshold' => 30,
    ],
];
```

## 🚀 Advanced Features

### **Cross-World Material Trading**
```php
// Transfer materials between worlds
$materialTracker->transferMaterial($world1, $world2, $instanceId);
```

### **Material Crafting System**
```php
// Combine materials to create new ones
$craftedMaterial = $materialTracker->craftMaterials($materials, $recipe);
```

### **Material Market System**
```php
// Trade materials between factions
$market->tradeMaterial($buyer, $seller, $instanceId, $price);
```

### **Material History Analysis**
```php
// Analyze material usage patterns
$patterns = $materialTracker->analyzeUsagePatterns($world, $timeframe);
```

## 🎯 Integration với Existing Systems

### **Với Autonomous World Engine**
- Materials được track mỗi tick
- World effects ảnh hưởng đến material states
- Material scarcity ảnh hưởng đến entropy

### **Với Intelligence System**
- Material observations cung cấp intelligence
- Resource scarcity detection
- Material availability predictions

### **Với Character Survival System**
- Characters sử dụng materials
- Material access affects survival probability
- Equipment degradation affects combat

## 🎯 Best Practices

### **1. Regular Maintenance**
- Track material durability
- Schedule repairs before complete failure
- Monitor scarcity levels

### **2. Optimal Distribution**
- Redistribute from abundant to scarce areas
- Consider faction needs and priorities
- Balance material types by location

### **3. Strategic Upgrades**
- Prioritize high-value materials
- Consider world conditions (entropy, events)
- Plan for long-term sustainability

### **4. Historical Analysis**
- Track material lifecycle patterns
- Learn from failure modes
- Optimize acquisition strategies

---

## 🎯 Kết Luận

**Material Tracking System** cung cấp cho WorldOS khả năng:

📦 **Theo dõi materials chi tiết** - Strength, durability, purity, location, ownership

🔄 **State management tự động** - Decay, degradation, repair, retirement

🌍 **World effects integration** - Entropy, shock events, faction influences

📈 **Analytics và optimization** - Statistics, breakdowns, suggestions

🎯 **Strategic resource management** - Distribution, upgrades, trading

**Worlds giờ đây có thể quản lý tài nguyên một cách thông minh và hiệu quả - một yếu tố quan trọng cho sustainable autonomous civilizations!** 🏗️✨
