# World OS – A Cognitive Operating System for Emergent History

> **World OS không tạo câu chuyện. Nó quản lý điều kiện để lịch sử tự xuất hiện.**

Giống như Linux không viết ứng dụng nhưng mọi ứng dụng sống được nhờ Linux, World OS không viết lore nhưng mọi world đều "có đời sống".

---

## I. WORLD OS LÀ GÌ (ĐỊNH NGHĨA CHUẨN)

**World OS không tạo câu chuyện.**
**Nó quản lý điều kiện để lịch sử tự xuất hiện.**

**Giống như:**
* Linux không viết ứng dụng
* Nhưng mọi ứng dụng sống được nhờ Linux

**World OS:**
* Không viết lore
* Không viết plot
* Nhưng mọi world đều "có đời sống"

---

## II. 4 LỚP KIẾN TRÚC CỐT LÕI (KHÔNG ĐƯỢC LẪN)

```
┌──────────────────────────┐
│  Human Layer (Writer)    │
│  - Bias, Selection       │
└────────────▲─────────────┘
             │
┌────────────┴─────────────┐
│  Historian Layer         │
│  - Memory, Pattern       │
└────────────▲─────────────┘
             │
┌────────────┴─────────────┐
│  World Runtime           │
│  - Simulation, AI        │
└────────────▲─────────────┘
             │
┌────────────┴─────────────┐
│  Cognitive Kernel        │
│  - Archetype, Law, Drift │
└──────────────────────────┘
```

👉 **Cognitive Kernel là trái tim.**
👉 **Tất cả phía trên không được sửa Kernel.**

---

## III. COGNITIVE KERNEL (THỨ PHẢI KHÓA CỨNG)

**1. Archetype System**
* Archetype Pool
* Polarity
* Drift
* Mutation (fork-only)

👉 **Đây là bản năng loài người của hệ.**

---

**2. World Law System** (ADR-0003 → 0006)
* Power ceiling
* Magic / Tech constraint
* Claim-based validation
* World law forking

👉 **Đây là vật lý + đạo đức nền.**

---

**3. Coupling Rules**
* Archetype ↔ Economy
* Archetype ↔ Power
* Legitimacy formula

👉 **Đây là xã hội học, không phải logic game.**

---

## IV. WORLD RUNTIME (CÓ THỂ THAY, NHƯNG KHÔNG PHÁ KERNEL)

**Bao gồm:**
* StoryEngine
* Core Simulator
* Economy Cycle
* Deception / Information
* AI Content Generator (bị sandbox)

👉 **Runtime có thể nâng cấp AI, Kernel không đổi.**

---

## V. HISTORIAN LAYER (KÝ ỨC DÀI HẠN)

**Trách nhiệm duy nhất:**
* Ghi nhớ
* So sánh
* Phát hiện mẫu hình

**Không:**
* Sửa world
* Dự đoán tương lai
* Phán xét đúng sai

👉 **Historian = RAM + HDD của lịch sử.**

---

## VI. HUMAN LAYER (WRITER / DESIGNER)

**Đây là điểm khác biệt lớn nhất với engine thường.**

**Con người:**
* Không viết story
* Không điều khiển world
* Chỉ chọn điều kiện ban đầu và chọn cái gì được nhớ

👉 **Vai trò: Curator of history, không phải Author.**

---

## VII. LAYER COMMUNICATION PROTOCOL

### Layer Interface Rules

```
Human Layer
  ↓ (can only send)
  - Seed configuration
  - Pressure injection
  - Selection markers
  ↑ (receives)
  - Historian summaries
  - Pattern reports

Historian Layer
  ↓ (can only send)
  - Pattern queries
  - Memory retrieval
  ↑ (receives)
  - World events
  - Saga traces

World Runtime
  ↓ (can only send)
  - State updates
  - Event streams
  ↑ (receives)
  - Kernel rules
  - Runtime config

Cognitive Kernel
  ↓ (sends)
  - Immutable laws
  - Archetype definitions
  ↑ (receives)
  - NOTHING (read-only from above)
```

---

## VIII. KERNEL IMMUTABILITY ENFORCEMENT

```php
class CognitiveKernel
{
    // Kernel components are FINAL
    private final ArchetypePool $archetypePool;
    private final WorldLawSystem $lawSystem;
    private final CouplingRules $couplingRules;
    
    // Only kernel can modify itself
    public function __construct()
    {
        $this->archetypePool = new ArchetypePool(
            basePath: '/kernel/archetypes.json'
        );
        
        $this->lawSystem = new WorldLawSystem(
            basePath: '/kernel/laws.json'
        );
        
        $this->couplingRules = new CouplingRules(
            basePath: '/kernel/coupling.json'
        );
        
        // Lock all modifications
        $this->lock();
    }
    
    public function modifyArchetype(): never
    {
        throw new ImmutableKernelException(
            'Cognitive Kernel is read-only from runtime'
        );
    }
}
```

---

## IX. LAYERED ARCHITECTURE BENEFITS

### 1. Kernel Independence
* AI can be upgraded without changing kernel
* Story engine can be replaced
* Historian can evolve

### 2. Memory Persistence
* Kernel + Historian = permanent memory
* Runtime can restart
* History survives

### 3. Human Boundaries
* Clear separation of roles
* Writer can't break causality
* System integrity guaranteed

---

## X. WORLD OS vs TRADITIONAL STORYTELLING ENGINE

| Aspect            | Traditional Engine      | World OS                  |
| ----------------- | ----------------------- | ------------------------- |
| Story Creation    | Human writes            | System emerges            |
| AI Role           | Content generator       | Sandboxed instantiator    |
| History           | Reset per story         | Accumulates permanently   |
| Human Role        | Author                  | Curator                   |
| Determinism       | Scripted                | Replay-deterministic      |
| Memory            | Per-world               | Cross-world + cross-saga  |
| Surprise          | Scripted twists         | Emergent outcomes         |

---

## XI. SYSTEM REQUIREMENTS

### Cognitive Kernel Must Be:
1. Immutable from above layers
2. Version-controlled
3. Governed (ADR process)
4. Minimal (only essentials)

### World Runtime Must Be:
1. Sandboxed (can't modify kernel)
2. Replaceable (modular)
3. Event-sourced (for replay)
4. Stateless (state in kernel/historian)

### Historian Must Be:
1. Read-only to world state
2. Pattern-focused (not narrative)
3. Persistent (survives runtime restart)
4. Human-queryable

### Human Layer Must Be:
1. Bias-only (no direct control)
2. Selection-focused (post-facto)
3. Curator mindset (not author)

---

## XII. FAILURE MODES TO AVOID

❌ **Runtime modifying kernel** - Breaks immutability
❌ **Human editing history** - Destroys emergent property
❌ **AI accessing historian** - Creates meta-knowledge
❌ **Layers merged** - Loses separation of concerns
❌ **Kernel bloat** - Becomes unmaintainable

---

## XIII. EVOLUTION PATH

### Current State (v1.0)
* Cognitive Kernel: ✅ Defined
* World Runtime: ✅ Implemented
* Historian Layer: 📝 Designed
* Human Layer: 📝 Designed

### Next Steps
1. Implement Historian Layer
2. Implement Human-in-the-Loop interfaces
3. Create Kernel governance process
4. Build Saga Runner integration

---

## SỰ THẬT CUỐI

> **World OS is not an application.
> It is the operating system on which infinite civilizations run.**

**Corollaries:**
1. Kernel is law, not suggestion
2. Layers must not merge
3. History accumulates, never resets
4. Humans curate, never dictate
5. Surprise is system health metric
