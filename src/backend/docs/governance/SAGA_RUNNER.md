# Saga Runner – Creative Explosion Orchestrator

> **Saga Runner không tạo câu chuyện.**
> Nó tạo **điều kiện để lịch sử tự sinh ra**.

Tài liệu này định nghĩa **Saga Runner** – bộ điều phối chạy nhiều world liên tiếp, thu myth legacy, và khởi tạo world mới **mà không phá nhân quả**.

---

## I. VAI TRÒ CỐT LÕI

Saga Runner là **control-plane ở tầng meta**, đứng trên Simulator.

**Nhiệm vụ:**
1. Khởi tạo saga
2. Chạy chuỗi world
3. Thu thập myth legacy
4. Bias world kế tiếp
5. Ghi dấu lịch sử hệ thống

**Saga Runner không can thiệp logic world.**

---

## IA. SAGA RUNNER ĐỨNG Ở ĐÂU TRONG HỆ?

**Nó không thay thế Simulator.**
**Nó không thay thế AI Generator.**

👉 **Nó là control plane meta, giống như:**
* Kubernetes control plane
* Không chạy container
* Chỉ quyết định chạy cái gì, khi nào, và nhớ cái gì

---

## IB. DOMAIN PLACEMENT (RẤT QUAN TRỌNG)

**KHÔNG để Saga Runner trong StoryEngine.**

```
app/
 └─ Domains/
     ├─ StoryEngine/        (world-level)
     ├─ WorldManagement/    (control plane hiện tại)
     └─ Saga/               👈 mới
         ├─ SagaRunner.php
         ├─ WorldSeedGenerator.php
         ├─ MythLegacyExtractor.php
         ├─ SagaObserver.php
         └─ DTO/
```

👉 **Saga là domain cao hơn world.**

---

## IC. MỐI QUAN HỆ VỚI SIMULATIONMANAGER

**Hiện tại:**
* SimulationManager = chạy 1 world

**Saga Runner:**
* Gọi SimulationManager nhiều lần
* Với seed khác nhau
* Có trí nhớ xuyên world

```php
foreach ($worlds as $i => $seed) {
    $world = $simulationManager->run($seed);
    $observer->record($world);
    
    if ($world->collapsed()) {
        $legacy = $legacyExtractor->extract($world);
        $seed = $seedGenerator->next($legacy);
    }
}
```

**Không hack core. Không sửa simulator loop.**

---

## ID. VÌ SAO SAGA RUNNER PHẢI LÀ ARTISAN COMMAND

**Vì:**
* Nó chạy lâu
* Không cần HTTP
* Dễ batch
* Dễ replay

**Command này không dành cho user, mà cho nhà sử học.**

```bash
php artisan saga:run
```

---

## IE. REPLAY & AUDIT (ĐIỂM ĂN TIỀN)

**Saga Runner không ghi story, nó ghi:**
* World ID
* Collapse type
* Myth legacy
* Archetype pool

👉 **Sau này bạn có thể:**
* Chạy lại saga
* So sánh divergence
* Phân tích "vì sao lịch sử khác đi"

**Đây là thứ game + AI project rất hiếm có.**

---

## IF. DẤU HIỆU BẠN BUILD ĐÚNG

**Bạn build đúng khi:**
* Chạy 2 saga cùng archetype → ra lịch sử khác
* Myth xuất hiện lại nhưng méo mó
* World #5 tránh một sai lầm world #1 mà không biết vì sao

**Nếu kết quả làm bạn bất ngờ → kiến trúc đúng.**

---

## II. KIẾN TRÚC TỔNG THỂ

```
SagaRunner
 ├─ WorldSeedGenerator      (tạo seed cho world mới)
 ├─ SimulationManager        (đã có - chạy 1 world)
 ├─ SagaObserver            (ghi lịch sử meta)
 ├─ MythLegacyExtractor     (nén myth → archetype)
 └─ SagaArchive             (lưu trữ saga history)
```

---

## III. DÒNG CHẢY 1 SAGA

### Step 1 – Saga Initialization

```php
SagaContext {
  saga_id: uuid
  archetype_pool: []        // từ Myth Legacy cũ hoặc seed ban đầu
  world_count: int          // số world sẽ chạy
  carry_weight: float       // mức ảnh hưởng legacy sang world mới (0-1)
}
```

**Properties:**
* `archetype_pool`: Từ Myth Legacy cũ hoặc seed ban đầu
* `carry_weight`: Mức ảnh hưởng legacy sang world mới (0 = không ảnh hưởng, 1 = ảnh hưởng tối đa)

---

### Step 2 – World Loop

**Cho mỗi world trong saga:**

```
1. Generate World Seed (biased by archetype_pool)
   ↓
2. Run Simulation (gọi SimulationManager)
   ↓
3. Observe Events (SagaObserver tracking)
   ↓
4. Detect Collapse (civilization collapse event)
   ↓
5. Extract Myth Legacy (nén myth → archetype)
   ↓
6. Update archetype_pool for next world
```

⚠️ **Saga Runner chỉ quan sát, không sửa world.**

---

### Step 3 – World Termination Condition

**World kết thúc khi:**
* Civilization collapse event triggered
* Hoặc max cycle reached (timeout)

**Không ép happy ending.**

---

## IV. WORLD SEED GENERATOR (META)

**World seed được bias bởi myth legacy trước đó:**

```php
WorldSeed {
  laws: LawProfile              // WFR selection
  dominant_archetypes: []       // từ legacy pool
  cultural_tension: float       // inherited from previous collapse
  primitive_bias: []            // archetype → primitive mapping
}
```

**Important:**
* Không copy myth (doctrine)
* Chỉ copy archetype + emotion
* Bias primitive selection, không force

**Example:**
```php
// World #3 collapsed with "FORBIDDEN_KNOWLEDGE" archetype
// World #4 seed generation:
$seed = [
  'laws' => generateLawProfile(),
  'archetypes' => ['FORBIDDEN_KNOWLEDGE' => 0.7],
  'primitive_bias' => [
    'KNOWLEDGE_CONTROLLED' => +0.3,  // higher chance
    'FAITH_DOMINANT' => +0.2
  ]
];
```

---

## V. MYTH LEGACY EXTRACTION

**Chỉ chạy sau collapse.**

**Extractor workflow:**

```php
function extractLegacy(World $world): ArchetypePool
{
    // 1. Lọc myth strength > threshold
    $significantMyths = $world->myths()
        ->where('strength', '>=', 0.5)
        ->get();
    
    // 2. Nén thành archetype
    $archetypes = [];
    foreach ($significantMyths as $myth) {
        $archetype = compressToArchetype($myth);
        $archetypes[] = $archetype;
    }
    
    // 3. Gán emotional weight
    foreach ($archetypes as $archetype) {
        $archetype->weight = calculateEmotionalWeight(
            $myth->strength,
            $world->collapse_type
        );
    }
    
    // 4. Không giữ doctrine
    return new ArchetypePool($archetypes);
}
```

**Output example:**
```json
{
  "archetype": "FORBIDDEN_KNOWLEDGE",
  "emotional_tags": ["fear", "loss", "hubris"],
  "weight": 0.75,
  "pattern": "knowledge_control → collapse"
}
```

---

## VI. SAGA OBSERVER (RẤT QUAN TRỌNG)

**Observer ghi các meta-events:**

```php
SagaObserver {
    recordMythBirth(World, Myth)
    recordMythDecay(World, Myth, DecayReason)
    recordSchism(World, Conflict)
    recordCollapse(World, CollapseType)
    recordLegacyTransfer(FromWorld, ToWorld, Archetypes)
}
```

👉 **Đây là dữ liệu cho "historian mode".**

**Use cases:**
* Phân tích pattern lịch sử
* Debug tại sao myth lặp lại
* Trace archetype evolution

---

## VII. LƯU TRỮ (EVENT-SOURCED)

### saga_runs

```sql
CREATE TABLE saga_runs (
    id UUID PRIMARY KEY,
    archetype_seed JSON,           -- initial archetypes
    carry_weight FLOAT,
    world_count INT,
    created_at TIMESTAMP,
    completed_at TIMESTAMP
);
```

### saga_worlds

```sql
CREATE TABLE saga_worlds (
    id UUID PRIMARY KEY,
    saga_id UUID REFERENCES saga_runs(id),
    world_id UUID REFERENCES worlds(id),
    order INT,                     -- world sequence in saga
    collapse_type VARCHAR,
    myth_legacy_extracted JSON,
    created_at TIMESTAMP
);
```

### saga_legacies

```sql
CREATE TABLE saga_legacies (
    id UUID PRIMARY KEY,
    saga_id UUID REFERENCES saga_runs(id),
    source_world_id UUID,
    archetype VARCHAR,
    emotional_tags JSON,
    weight FLOAT,
    carried_to_next BOOLEAN,
    created_at TIMESTAMP
);
```

### saga_observations (meta-events)

```sql
CREATE TABLE saga_observations (
    id UUID PRIMARY KEY,
    saga_id UUID,
    world_id UUID,
    event_type VARCHAR,            -- MYTH_BIRTH | SCHISM | COLLAPSE
    event_data JSON,
    tick INT,
    created_at TIMESTAMP
);
```

---

## VIII. ARTISAN COMMAND

```bash
php artisan saga:run \
  --worlds=5 \
  --archetypes=forbidden_knowledge,sacrifice \
  --carry=0.6
```

**Flags:**
* `--worlds`: Số world sẽ chạy trong saga
* `--archetypes`: Archetype seed ban đầu (comma-separated)
* `--carry`: Mức legacy bias (0-1, default 0.5)
* `--max-ticks`: Max ticks per world (default: 1000)
* `--output`: Export saga history (json|csv)

**Example:**
```bash
php artisan saga:run --worlds=10 --archetypes=lost_paradise --carry=0.8
```

---

## IX. QUY TẮC AI

**AI:**
* ❌ Không biết saga exists
* ❌ Không biết world trước đó
* ✅ Chỉ nhận seed hiện tại
* ✅ Generate story từ primitives + archetypes (as narrative bias)

**Saga Runner là người duy nhất nhớ.**

---

## X. IMPLEMENTATION EXAMPLE

```php
namespace App\Domains\Saga;

class SagaRunner
{
    public function __construct(
        private WorldSeedGenerator $seedGenerator,
        private SimulationManager $simulator,
        private MythLegacyExtractor $legacyExtractor,
        private SagaObserver $observer,
        private SagaArchive $archive
    ) {}
    
    public function run(SagaConfig $config): SagaResult
    {
        $saga = $this->archive->createSaga($config);
        $archetypePool = $config->initialArchetypes;
        
        for ($i = 1; $i <= $config->worldCount; $i++) {
            // 1. Generate seed
            $seed = $this->seedGenerator->generate(
                $archetypePool,
                $config->carryWeight
            );
            
            // 2. Run world
            $world = $this->simulator->run($seed);
            
            // 3. Observe
            $this->observer->observeWorld($saga, $world);
            
            // 4. Wait for collapse or timeout
            while (!$world->hasCollapsed() && !$world->hasTimedOut()) {
                $this->simulator->tick($world);
                $this->observer->recordEvents($saga, $world);
            }
            
            // 5. Extract legacy
            if ($world->hasCollapsed()) {
                $legacy = $this->legacyExtractor->extract($world);
                $archetypePool = $archetypePool->merge($legacy);
            }
            
            // 6. Archive
            $this->archive->recordWorld($saga, $world, $legacy);
        }
        
        return new SagaResult($saga, $this->archive->getSummary($saga));
    }
}
```

---

## XI. EXAMPLE: 5-WORLD SAGA

**Initial seed:** `FORBIDDEN_KNOWLEDGE` archetype

**World 1:**
* Primitives: KNOWLEDGE_CONTROLLED, FAITH_DOMINANT
* Collapse: IDEOLOGICAL (knowledge myth failed)
* Legacy: "FORBIDDEN_KNOWLEDGE" (weight 0.8)

**World 2:**
* Primitives: KNOWLEDGE_OPEN, FAITH_LOW (biased away from failure)
* Collapse: EXHAUSTION (economic collapse)
* Legacy: "INTELLECTUAL_HUBRIS" (weight 0.6)

**World 3:**
* Primitives: KNOWLEDGE_CONTROLLED (archetype pressure), ECONOMY_BALANCED
* Schism: Knowledge control vs freedom
* Collapse: VIOLENT (civil war)
* Legacy: "SCHISM_KNOWLEDGE" (weight 0.9)

**World 4:**
* Primitives: DECENTRALIZED (reacting to schism)
* Myth: "THE_FRACTURED_TRUTH" (variant of FORBIDDEN_KNOWLEDGE)
* Collapse: EXHAUSTION
* Legacy: "TRUTH_FATIGUE" (weight 0.5)

**World 5:**
* Primitives: KNOWLEDGE_CONTROLLED (archetype still strong)
* Different execution: Theocracy, not secular
* Collapse: IDEOLOGICAL (different reason)
* Legacy: "SACRED_IGNORANCE" (new variant)

**Observation:** Same archetype, 5 different death modes.

---

## XII. FAILURE MODES CẦN TRÁNH

❌ **AI knows saga context** - Breaks isolation
❌ **Deterministic outcomes** - Same archetype → same story
❌ **No collapse allowed** - Forces happy endings
❌ **Legacy = full myth copy** - Violates archetype principle
❌ **Manual world seeding** - Defeats automation purpose

---

## SỰ THẬT CUỐI

> **A saga is not a long story.
> It is many civilizations failing to forget the same thing.**

**Corollaries:**
1. Saga orchestrates, doesn't dictate
2. Each world dies on its own terms
3. Legacy is memory, not destiny
4. Surprise is success metric
5. Saga Runner is historian, not author
