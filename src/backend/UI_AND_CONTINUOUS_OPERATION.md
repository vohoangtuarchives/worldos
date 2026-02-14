# 🖥️ UI & Continuous Operation System - WorldOS

## 🎯 Tổng Quan

**UI & Continuous Operation System** cung cấp giao diện web real-time và cơ chế để worlds hoạt động liên tục với monitoring, control, và automation.

## 🏗️ Kiến Trúc UI

### **1. WorldController**
- RESTful API endpoints cho world operations
- Real-time data streaming
- AJAX-based interactions
- JSON responses cho frontend

### **2. Web Interface**
- **Worlds Index**: Overview tất cả worlds
- **World Dashboard**: Real-time monitoring và control
- **Responsive Design**: Bootstrap 5 + Chart.js
- **Real-time Updates**: 5-second refresh intervals

### **3. Continuous Operation Service**
- Background job processing
- Queue-based tick execution
- Rate limiting và error handling
- State management với caching

## 🌐 Web Interface Features

### **Worlds Index Page**
```
📊 Global Stats:
- Total Worlds: 5
- Autonomous: 3
- Running: 2
- Total Ticks: 1,247

📋 World Table:
ID | Name | Preset | Tick | Entropy | Status | Actions
1  | World 1 | martial | 245 | 67.5% | Running | [Dashboard][Start/Stop][Tick]
```

### **World Dashboard**
```
🎛️ Control Panel:
[Start Autonomous] [Single Tick] [Multi Tick(10)] [Refresh]
Tick Speed: [====|====] 5s

📈 Real-time Charts:
- Entropy Over Time (Line chart)
- Population Status (Doughnut chart)

📊 Live Stats:
Current Tick: 245
Entropy Level: 67.5%
Alive Characters: 12
Active Materials: 35

⚡ Recent Events:
- Civil war outbreak (severity: 0.8)
- Magic collapse detected (severity: 0.6)
- Resource scarcity warning (severity: 0.4)

🧠 Intelligence Summary:
- Total Reports: 42
- Reliable Reports: 28
- High Urgency: 3
- Threats: 2 | Opportunities: 1
```

## 🔄 Continuous Operation Mechanism

### **Background Processing**
```php
// Queue-based continuous ticks
ContinuousWorldTickJob::dispatch($worldId)
    ->delay(now()->addSeconds($interval));
```

### **Rate Limiting**
```php
// Prevent concurrent execution
const MAX_CONCURRENT_TICKS = 3;
const DEFAULT_TICK_INTERVAL = 5; // seconds
```

### **State Management**
```php
// Cache-based state tracking
Cache::put("continuous_state_{$worldId}", [
    'running' => true,
    'interval' => 5,
    'total_ticks' => 1247,
    'errors' => 2,
    'uptime_percentage' => 98.5,
]);
```

### **Error Handling**
```php
// Automatic retry with exponential backoff
public int $tries = 3;
public array $backoff = [5, 10, 15]; // seconds
```

## 🎮 UI Interactions

### **World Control**
```javascript
// Start/Stop autonomous mode
function toggleWorld(worldId) {
    $.post(`/worlds/${worldId}/start`)  // or /stop
        .done(function(data) {
            updateWorldStatus(worldId);
            showNotification('World started successfully', 'success');
        });
}

// Single tick execution
function tickWorld(worldId) {
    $.post(`/worlds/${worldId}/tick`, {count: 1})
        .done(function(data) {
            updateCharts(data);
            showNotification('Tick completed', 'success');
        });
}
```

### **Real-time Updates**
```javascript
// 5-second refresh cycle
setInterval(function() {
    updateAllWorldStatuses();
    updateGlobalStats();
}, 5000);

// Fetch real-time data
$.get(`/worlds/${worldId}/realtime`)
    .done(function(data) {
        updateDashboard(data);
        updateCharts(data);
    });
```

### **Chart Updates**
```javascript
// Entropy chart
entropyChart.data.datasets[0].data.push(data.world.entropy * 100);
entropyChart.update();

// Population chart
populationChart.data.datasets[0].data = [
    data.characters.alive, 
    data.characters.dead
];
populationChart.update();
```

## 📊 Real-time Monitoring

### **World Status API**
```json
{
    "success": true,
    "timestamp": "2026-02-13T12:30:00Z",
    "world": {
        "id": "world_1",
        "tick": 245,
        "entropy": 0.675,
        "autonomous": true
    },
    "characters": {
        "total": 15,
        "alive": 12,
        "dead": 3
    },
    "materials": {
        "total": 42,
        "active": 35,
        "broken": 7,
        "average_durability": 67.5
    },
    "intelligence": {
        "total_reports": 42,
        "reliable_reports": 28,
        "high_urgency": 3
    }
}
```

### **Continuous Operation Status**
```json
{
    "world_id": "world_1",
    "running": true,
    "autonomous": true,
    "interval": 5,
    "started_at": "2026-02-13T10:00:00Z",
    "last_tick_at": "2026-02-13T12:30:00Z",
    "total_ticks": 1247,
    "errors": 2,
    "uptime_percentage": 98.5,
    "ticks_per_minute": 12.0,
    "next_tick_in": 3
}
```

## 🎛️ Control Features

### **Start/Stop Autonomous Mode**
```php
// Enable autonomous continuous operation
$world = $world->enableAutonomous();
$continuousService->startContinuousOperation($world->id(), 5); // 5-second intervals

// Stop continuous operation
$continuousService->stopContinuousOperation($world->id());
```

### **Manual Tick Control**
```php
// Single tick
$result = $tickAction->execute($world, $characters);

// Multi-tick
for ($i = 0; $i < 10; $i++) {
    $result = $tickAction->execute($world, $characters);
    $world = $result->world;
}
```

### **Speed Control**
```javascript
// Adjustable tick speed (1-30 seconds)
$('#tick-speed').on('input', function() {
    const speed = $(this).val();
    $('#speed-display').text(speed + 's');
    // Update continuous operation interval
});
```

## 📈 Visual Analytics

### **Entropy Chart**
- **Type**: Line chart
- **Data**: Last 20 ticks
- **Update**: Real-time
- **Color coding**: Green (<30%), Yellow (30-70%), Red (>70%)

### **Population Chart**
- **Type**: Doughnut chart
- **Data**: Alive vs Dead characters
- **Update**: Real-time
- **Colors**: Green (alive), Red (dead)

### **Material Status**
- **Total Materials**: Overall count
- **Stable**: Active materials
- **Damaged**: Need repair
- **Broken**: Destroyed materials

## 🔄 Queue System Integration

### **Job Dispatch**
```php
// Queue next tick automatically
Queue::later(
    now()->addSeconds($interval),
    new ContinuousWorldTickJob($worldId)
);
```

### **Job Processing**
```php
public function handle(ContinuousWorldService $continuousService): void
{
    $success = $continuousService->executeContinuousTick($this->worldId);
    
    if (!$success) {
        $this->release(10); // Retry after 10 seconds
    }
}
```

### **Error Recovery**
```php
// Automatic retry with backoff
public int $tries = 3;
public array $backoff = [5, 10, 15];

// Failed job handling
public function failed(\Throwable $exception): void
{
    Log::error("Continuous tick job permanently failed", [
        'world_id' => $this->worldId,
        'attempts' => $this->attempts(),
    ]);
}
```

## 🌐 Web Routes

### **World Management**
```php
// Main world routes
Route::prefix('worlds')->group(function () {
    Route::get('/', [WorldController::class, 'index']);
    Route::get('/{worldId}/dashboard', [WorldController::class, 'dashboard']);
    
    // Actions
    Route::post('/{worldId}/tick', [WorldController::class, 'tick']);
    Route::post('/{worldId}/start', [WorldController::class, 'start']);
    Route::post('/{worldId}/stop', [WorldController::class, 'stop']);
    
    // Real-time data
    Route::get('/{worldId}/realtime', [WorldController::class, 'realtime']);
    Route::get('/{worldId}/status', [WorldController::class, 'status']);
});
```

### **API Endpoints**
```php
// AJAX endpoints
Route::prefix('api/worlds')->group(function () {
    Route::post('/start-all', [WorldController::class, 'startAll']);
    Route::post('/stop-all', [WorldController::class, 'stopAll']);
    Route::get('/{worldId}/realtime', [WorldController::class, 'realtime']);
});
```

## 🎯 Advanced Features

### **Multi-World Control**
```javascript
// Start all autonomous worlds
function startAllWorlds() {
    $.post('/api/worlds/start-all')
        .done(function(data) {
            showNotification('All worlds started', 'success');
            loadWorldStatuses();
        });
}
```

### **Batch Operations**
```php
// Start all autonomous worlds
$results = $continuousService->startAllAutonomousWorlds(5); // 5-second interval

// Stop all worlds
$results = $continuousService->stopAllWorlds();
```

### **Performance Monitoring**
```php
// Track execution metrics
$metrics = [
    'total_ticks' => $state['total_ticks'],
    'errors' => $state['errors'],
    'uptime_percentage' => $this->calculateUptimePercentage($state),
    'ticks_per_minute' => $this->calculateTicksPerMinute($worldId),
];
```

## 📱 Responsive Design

### **Mobile Support**
- Bootstrap 5 responsive grid
- Touch-friendly controls
- Optimized charts for small screens
- Collapsible sidebars

### **Desktop Features**
- Full-width dashboard
- Multiple chart views
- Advanced control panels
- Keyboard shortcuts

## 🔧 Configuration

### **Continuous Operation Settings**
```php
// config/world.php
'continuous_operation' => [
    'default_tick_interval' => 5, // seconds
    'max_concurrent_ticks' => 3,
    'cache_ttl' => 300, // 5 minutes
    'job_timeout' => 60, // seconds
    'max_retries' => 3,
],
```

### **UI Settings**
```php
// config/ui.php
'dashboard' => [
    'refresh_interval' => 5, // seconds
    'chart_history_size' => 20,
    'notification_duration' => 3000, // milliseconds
    'auto_start_worlds' => false,
],
```

## 🚀 Performance Optimizations

### **Caching Strategy**
```php
// Cache world states
Cache::put("world_state_{$worldId}", $worldData, 300); // 5 minutes

// Cache continuous operation status
Cache::put("continuous_state_{$worldId}", $state, 300);
```

### **Database Optimization**
```php
// Efficient queries
$worlds = World::with(['characters', 'materials'])
    ->where('autonomous', true)
    ->get();

// Batch updates
World::whereIn('id', $worldIds)
    ->update(['last_tick_at' => now()]);
```

### **Frontend Optimization**
```javascript
// Debounce rapid requests
let updateTimeout;
function debouncedUpdate() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(updateData, 1000);
}

// Efficient DOM updates
function updateWorldRow(worldId, data) {
    const row = $(`tr[data-world-id="${worldId}"]`);
    row.find('.tick-count').text(data.world.tick);
    row.find('.entropy-bar').css('width', data.world.entropy * 100 + '%');
}
```

## 🎯 Best Practices

### **1. Rate Limiting**
- Prevent server overload
- Respect queue capacity
- Implement backpressure

### **2. Error Handling**
- Graceful degradation
- Automatic recovery
- User notifications

### **3. User Experience**
- Real-time feedback
- Loading indicators
- Clear status messages

### **4. Performance**
- Efficient data transfer
- Minimal DOM manipulation
- Optimized chart updates

---

## 🎯 Kết Luận

**UI & Continuous Operation System** cung cấp cho WorldOS:

🖥️ **Modern Web Interface** - Responsive dashboard với real-time monitoring

🔄 **Continuous Operation** - Background processing với queue-based execution

📊 **Real-time Analytics** - Live charts, stats, và status updates

🎛️ **Advanced Controls** - Start/stop, speed control, batch operations

🚀 **Performance Optimized** - Caching, rate limiting, efficient updates

**WorldOS giờ đây có giao diện đẹp và cơ chế hoạt động liên tục - worlds có thể chạy 24/7 với full monitoring và control!** 🌐✨
