# 🕵️ Intelligence Gathering System - WorldOS

## 🎯 Tổng Quan

**Intelligence Gathering System** cho phép WorldOS thu thập, phân tích và xử lý thông tin từ nhiều nguồn khác nhau để worlds có thể học hỏi, thích nghi và ra quyết định thông minh.

## 📊 Các Nguồn Intelligence

### **1. Character Intelligence** 👤
- **Health observations**: Tình trạng thương tích, bệnh tật
- **Behavioral patterns**: Sự thay đổi hành vi, tâm lý
- **Environmental awareness**: Nhận thức nguy hiểm môi trường
- **Reliability**: 70% (tăng theo narrative weight)

### **2. Environmental Intelligence** 🌍
- **Entropy analysis**: Mức độ bất ổn thế giới
- **Resource monitoring**: Mức độ tài nguyên
- **Faction stability**: Sự ổn định các phe phái
- **Reliability**: 90% (cao nhất)

### **3. Event Intelligence** ⚡
- **Event impact analysis**: Tác động của shock events
- **Casualty assessment**: Ước tính thương vong
- **Duration prediction**: Dự đoán thời gian kéo dài
- **Reliability**: 80% (phụ thuộc severity)

### **4. Faction Intelligence** 🏛️
- **Power level monitoring**: Mức độ quyền lực
- **Stability assessment**: Đánh giá sự ổn định
- **Territory control**: Kiểm soát lãnh thổ
- **Reliability**: 60% (phụ thuộc stability)

### **5. Myth Intelligence** 🔮
- **Activity level**: Mức độ hoạt động
- **Power fluctuations**: Biến động sức mạnh
- **Influence tracking**: Theo dõi ảnh hưởng
- **Reliability**: 50% (thấp nhất - thiên về bí ẩn)

## 🔄 Quy Trình Thu Thập

### **Gathering Phase**
```php
$collection = $intelligenceService->gatherIntelligence(
    $world,
    $characters,
    $activeEvents
);
```

### **Processing Phase**
1. **Age Intelligence**: Tăng tuổi thông tin
2. **Decay Accuracy**: Giảm độ chính xác theo thời gian
3. **Remove Old Intelligence**: Xóa thông tin quá cũ (>50 ticks)
4. **Pattern Detection**: Phát hiện pattern và xu hướng
5. **Summary Generation**: Tạo báo cáo tổng hợp

### **Analysis Phase**
- **Threat Assessment**: Đánh giá mối đe dọa
- **Opportunity Identification**: Nhận diện cơ hội
- **Risk Analysis**: Phân tích rủi ro
- **Recommendation Generation**: Tạo khuyến nghị

## 🎮 Sử Dụng Intelligence System

### **Command Line Tools**
```bash
# Thu thập intelligence mới
php artisan world:intelligence --world-id=1 --gather

# Phân tích chi tiết
php artisan world:intelligence --world-id=1 --analyze

# Xem theo nguồn
php artisan world:intelligence --world-id=1 --sources

# Chỉ intelligence có thể hành động
php artisan world:intelligence --world-id=1 --actionable

# Intelligence gần đây
php artisan world:intelligence --world-id=1 --recent

# Phân tích tất cả worlds
php artisan world:intelligence --analyze --gather
```

### **Sample Output**
```
🕵️  Intelligence Analysis for World 1: Thế Giới Kiếm Hiệp

📊 Intelligence Summary:
┌─────────────────┬─────────┐
│ Metric          │ Value   │
├─────────────────┼─────────┤
│ Total Reports   │ 42      │
│ Reliable Reports│ 28      │
│ High Urgency    │ 3       │
│ Avg Accuracy    │ 78.5%   │
│ Overall Status  │ Cautious│
└─────────────────┴─────────┘

📡 Intelligence Sources:
  character: 15 reports
  environment: 8 reports
  event: 6 reports
  faction: 9 reports
  myth: 4 reports

⚠️  Identified Threats:
  • entropy_crisis: World entropy approaching critical levels
    Severity: high
    Mitigation: reduce_conflicts, stabilize_factions

💎 Identified Opportunities:
  • alliance_formation: Stable factions could form alliances
    Potential: high
    Benefits: stability_increase, resource_sharing

💡 Recommendations:
  • Focus on threat mitigation - immediate dangers outweigh opportunities
  • Increase defensive measures and emergency preparedness
```

## 🔍 Pattern Detection

### **Character Death Patterns**
```php
// Detect multiple vulnerable characters
if (count($vulnerableCharacters) >= 3) {
    // Generate pattern intelligence
    "Multiple characters showing vulnerability patterns - potential mass casualty event"
}
```

### **Entropy Spike Patterns**
```php
// Detect critical entropy levels
if ($maxEntropy > 0.7) {
    // Generate warning
    "Critical entropy levels detected - world stability at risk"
}
```

### **Faction Instability Patterns**
```php
// Detect multiple unstable factions
if (count($unstableFactions) >= 2) {
    // Generate conflict warning
    "Multiple unstable factions detected - high conflict probability"
}
```

### **Resource Depletion Patterns**
```php
// Detect resource scarcity
if ($minResourceLevel < 30) {
    // Generate survival threat
    "Critical resource scarcity detected - survival threat"
}
```

### **Myth Convergence Patterns**
```php
// Detect supernatural convergence
if (count($activeMyths) >= 2) {
    // Generate supernatural warning
    "Multiple myth entities showing high activity - supernatural convergence"
}
```

## 📈 Intelligence Quality Metrics

### **Accuracy & Reliability**
```
🟢 High Quality:     Accuracy > 80% + Reliability > 70%
🟡 Medium Quality:  Accuracy 60-80% + Reliability 50-70%
🔴 Low Quality:     Accuracy < 60% + Reliability < 50%
```

### **Age & Decay**
```
🕐 Recent:     Age < 10 ticks (95% accuracy)
📅 Mid-aged:   Age 10-30 ticks (80% accuracy)
📜 Old:        Age 30-50 ticks (60% accuracy)
💀 Expired:    Age > 50 ticks (removed)
```

### **Urgency Levels**
```
🔴 High Urgency:    Threats, Events, Critical Issues
🟡 Medium Urgency:  Character Issues, Faction Problems
🟢 Low Urgency:     General Observations, Background Info
```

## 🎯 Actionable Intelligence

### **Threat Intelligence**
```php
$threats[] = [
    'type' => 'entropy_crisis',
    'severity' => 'high',
    'description' => 'World entropy approaching critical levels',
    'mitigation' => ['reduce_conflicts', 'stabilize_factions']
];
```

### **Opportunity Intelligence**
```php
$opportunities[] = [
    'type' => 'alliance_formation',
    'potential' => 'high',
    'description' => 'Stable factions could form alliances',
    'benefits' => ['stability_increase', 'resource_sharing']
];
```

### **Risk Intelligence**
```php
$risks[] = [
    'type' => 'systemic_risk',
    'level' => 'high',
    'score' => 75.5,
    'mitigation' => ['prioritize_threats', 'improve_intelligence_quality']
];
```

## 🔄 Integration với Existing Systems

### **Với Autonomous World Engine**
- Intelligence được thu thập mỗi tick
- Ảnh hưởng đến decision making
- Cung cấp context cho shock events

### **Với Character Survival System**
- Character observations cung cấp personal intelligence
- Survival probability dựa trên intelligence quality
- Death predictions sử dụng intelligence patterns

### **Với AI Integration**
- AI phân tích intelligence patterns
- AI đề xuất actions dựa trên intelligence
- AI học hỏi từ intelligence effectiveness

## 📊 Database Schema

### **Intelligence Reports Table**
```sql
CREATE TABLE intelligence_reports (
    id VARCHAR(255) PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    type ENUM('character_observation', 'environmental_scan', 'event_analysis', 
             'faction_monitoring', 'myth_analysis', 'pattern_detection') NOT NULL,
    source_type ENUM('character', 'environment', 'event', 'faction', 'myth') NOT NULL,
    source_id VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    metadata JSON,
    accuracy DECIMAL(3,2) NOT NULL,
    reliability DECIMAL(3,2) NOT NULL,
    age INT DEFAULT 0,
    urgency ENUM('high', 'medium', 'low') DEFAULT 'low',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_world_type (world_id, type),
    INDEX idx_source (source_type, source_id),
    INDEX idx_urgency (urgency),
    INDEX idx_age (age),
    INDEX idx_reliability (reliability)
);
```

### **Intelligence Summaries Table**
```sql
CREATE TABLE intelligence_summaries (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    tick INT NOT NULL,
    threat_count INT DEFAULT 0,
    opportunity_count INT DEFAULT 0,
    risk_score DECIMAL(5,2),
    overall_status ENUM('stable', 'cautious', 'concerning', 'critical'),
    recommendations JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_world_tick (world_id, tick),
    INDEX idx_world_status (world_id, overall_status)
);
```

## ⚙️ Configuration

### **Intelligence Settings** (`config/intelligence.php`)
```php
return [
    'gathering' => [
        'max_age' => 50,              // ticks
        'accuracy_decay_rate' => 0.02, // per tick
        'min_accuracy' => 0.3,        // minimum accuracy threshold
    ],
    
    'sources' => [
        'character' => ['reliability' => 0.7, 'priority' => 3],
        'environment' => ['reliability' => 0.9, 'priority' => 5],
        'event' => ['reliability' => 0.8, 'priority' => 2],
        'faction' => ['reliability' => 0.6, 'priority' => 4],
        'myth' => ['reliability' => 0.5, 'priority' => 8],
    ],
    
    'patterns' => [
        'min_samples' => 3,           // minimum samples for pattern detection
        'confidence_threshold' => 0.7, // minimum confidence for patterns
    ],
    
    'performance' => [
        'batch_size' => 100,
        'cache_ttl' => 3600,
        'enable_compression' => true,
    ],
];
```

## 🚀 Advanced Features

### **Cross-World Intelligence Sharing**
```php
// Share intelligence between worlds
$sharedIntelligence = $intelligenceService->shareIntelligence(
    $sourceWorld,
    $targetWorlds,
    $sharingRules
);
```

### **Predictive Intelligence**
```php
// Predict future events based on patterns
$predictions = $intelligenceService->predictEvents(
    $world,
    $historicalIntelligence,
    $predictionHorizon
);
```

### **Intelligence Validation**
```php
// Validate intelligence accuracy
$validation = $intelligenceService->validateIntelligence(
    $intelligence,
    $actualOutcomes
);
```

## 🎯 Best Practices

### **1. Quality Over Quantity**
- Prioritize reliable sources
- Remove low-quality intelligence
- Validate predictions regularly

### **2. Timeliness Matters**
- Age intelligence appropriately
- Remove expired information
- Focus on recent, relevant data

### **3. Source Diversity**
- Gather from multiple sources
- Cross-validate information
- Account for source reliability

### **4. Action-Oriented**
- Focus on actionable intelligence
- Generate clear recommendations
- Track effectiveness of actions

---

## 🎯 Kết Luận

**Intelligence Gathering System** cung cấp cho WorldOS khả năng:

🕵️ **Thu thập thông tin đa nguồn** - Character, Environment, Events, Factions, Myths

🔍 **Phát hiện pattern và xu hướng** - Death patterns, entropy spikes, faction instability

⚡ **Cung cấp intelligence có thể hành động** - Threats, opportunities, risks

📈 **Hỗ trợ decision making thông minh** - Recommendations, predictions, assessments

**Worlds giờ đây có thể "nhìn thấy" và "hiểu" chính mình - một bước quan trọng toward true autonomous intelligence!** 🧠✨
