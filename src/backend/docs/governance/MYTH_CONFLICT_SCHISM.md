# Myth Conflict & Schism Engine

> **Không có nền văn minh nào sụp đổ vì thiếu myth.**
> Nó sụp đổ vì **có quá nhiều myth không thể cùng đúng**.

Tài liệu này định nghĩa cách **nhiều myth cùng tồn tại, xung đột, phân nhánh xã hội và gây đổ vỡ** – mà vẫn giữ được nhân quả, replay và kiểm soát engine.

---

## I. NGUYÊN TẮC GỐC

1. **Myth không xung đột vì nội dung** mà vì **domain chi phối**
2. **Xung đột không phải event đơn lẻ** → là **trạng thái kéo dài**
3. **Schism là hệ quả xã hội**, không phải quyết định AI

---

## IA. CORE INSIGHT: DOMAIN CONFLICT

**Myth conflict không phải là "tranh cãi", mà là tranh quyền điều khiển thế giới.**

**Điểm cốt lõi:**
> Myth không đánh nhau vì đúng – sai.
> Myth đánh nhau vì **ai được quyền quyết định trong domain nào**.

**Examples:**
* Ai quyết định tri thức?
* Ai quyết định hy sinh?
* Ai quyết định số phận?

👉 Đây là **xung đột quyền lực**, không phải triết học.

---

## IB. VÌ SAO CONFLICT PHẢI LÀ STATE, KHÔNG PHẢI EVENT

**❌ Nếu làm:**
```
"Myth A vs Myth B → event"
```

**Hậu quả:**
* Conflict kết thúc quá nhanh
* Không để lại dấu vết xã hội
* Không sinh lịch sử

**✅ Trong thiết kế này:**
```
conflict = trạng thái kéo dài
pressure tích luỹ
bùng nổ khi vượt threshold
```

**Đây là lý do:**
* Schism
* Nội chiến
* Holy war

**→ Đều có giai đoạn thai nghén.**

---

## IC. SCHISM KHÔNG PHÁ MYTH – NÓ NHÂN BẢN MYTH

**Điểm tinh tế:**

Khi schism xảy ra:
* Myth gốc **không chết**
* Nó bị **fork**

**Giống Git:**
```
same origin
divergent doctrine
incompatible future
```

👉 Đây là lý do bạn có:
* Giáo phái
* Dị giáo
* Cải cách tôn giáo

**Mà vẫn replay được lịch sử.**

---

## ID. VAI TRÒ THẬT SỰ CỦA MANIPULATION TRONG CONFLICT

**Manipulation:**
* Không ngăn conflict
* Chỉ dời thời điểm nổ

**Hệ quả:**
* Sanitize quá nhiều → hypocrisy → nổ mạnh hơn
* Suppress quá lâu → trauma → nổ bạo lực hơn

👉 **Chính trị luôn trả lãi kép.**

---

## IE. MAJOR EPOCH – ĐỊNH NGHĨA CHÍNH XÁC

**Major Epoch = thời điểm conflict chuyển trạng thái**

```
latent → active → fractured
```

* Không cần năm
* Không cần era name
* Chỉ cần **state change**

---

## II. MYTH DOMAIN GRAPH

Mỗi myth chi phối một hoặc nhiều **domain**:

* **Authority** - Quyền lực chính trị
* **Knowledge** - Tri thức & giáo dục
* **Sacrifice** - Hy sinh & đức tính
* **Identity** - Bản sắc tập thể
* **Destiny** - Vận mệnh & tiên tri

> **Conflict xảy ra khi hai myth đòi quyền quyết định trên cùng domain.**

---

## III. CÁC LOẠI CONFLICT

### 1. Exclusive Conflict (Không thể cùng tồn tại)

**Example:**
* Myth A: "Truth must be controlled"
* Myth B: "Truth must be free"

**Resolution:**
* → Buộc phải loại bỏ hoặc đẩy myth kia xuống dormant

**Engine Rule:**
```
IF myth_a.domain == myth_b.domain
AND doctrines are mutually exclusive
THEN conflict_type = EXCLUSIVE
```

---

### 2. Hierarchical Conflict (Tranh quyền tối thượng)

**Example:**
* Myth tôn giáo vs myth quốc gia
* "God above king" vs "King is divine"

**Resolution:**
* → Quyết định **cái nào override cái nào**

**Engine Rule:**
```
IF myth_a.domain overlaps myth_b.domain
AND both claim supremacy
THEN conflict_type = HIERARCHICAL
```

---

### 3. Interpretive Conflict (Diễn giải khác nhau)

**Example:**
* Cùng myth, khác nghĩa
* "Silence protects" (conservative) vs "Silence oppresses" (reformist)

**Resolution:**
* → Sinh giáo phái (faction split)

**Engine Rule:**
```
IF myth_a is variant of myth_b
AND interpretations diverge
THEN conflict_type = INTERPRETIVE
```

---

## IV. CONFLICT PRESSURE

Conflict không bùng nổ ngay. Nó **tích áp lực**.

### Conflict Pressure Formula

```
Conflict Pressure = 
  strength_overlap * 0.4 +
  population_exposure * 0.3 +
  institutional_backing * 0.2 +
  historical_friction * 0.1
```

**Components:**
* **Strength overlap:** Cả hai myth đều mạnh trên cùng domain
* **Population exposure:** Bao nhiêu faction exposed to cả hai
* **Institutional backing:** Faction nào support myth nào
* **Historical friction:** Có lịch sử xung đột trước không

**Threshold:** Khi pressure ≥ 0.7 → xã hội rạn nứt

---

## V. SCHISM FORMATION

**When conflict pressure vượt threshold:**

```
conflict.pressure ≥ schism_threshold (0.7)
 ↓
faction_split_event
 ↓
myth_fork (create variant)
 ↓
population redistribution
 ↓
schism_state = ACTIVE
```

**Schism không xóa myth cũ. Nó tạo branch myth.**

### Schism Output

```json
{
  "original_myth": "THE_SILENCE",
  "schism_myth": "THE_REFORMED_SILENCE",
  "faction_split": {
    "orthodox": ["noble_council"],
    "reformist": ["scholar_guild"]
  },
  "doctrine_divergence": 0.6
}
```

---

## VI. STATE MACHINE

```
LATENT
  ↓ (pressure ≥ 0.4)
ACTIVE
  ↓ (pressure ≥ 0.7)
FRACTURED (Schism)
  ↓ (one myth dominates OR both decay)
RESOLVED
```

**State Transitions:**
* **LATENT:** Myths coexist, low tension
* **ACTIVE:** Visible social friction, propaganda wars
* **FRACTURED:** Society split, open conflict
* **RESOLVED:** One myth wins OR both weaken

---

## VII. ENGINE IMPLEMENTATION

### MythConflictState

```python
class MythConflictState:
    myth_a_id: int
    myth_b_id: int
    conflict_domain: str
    conflict_type: str  # EXCLUSIVE | HIERARCHICAL | INTERPRETIVE
    pressure: float
    status: str  # LATENT | ACTIVE | FRACTURED | RESOLVED
    faction_alignment: dict
```

### SchismResolver (runs per cycle)

```python
def resolve_conflict(conflict_state, world_state):
    # 1. Calculate conflict pressure
    pressure = calculate_pressure(conflict_state, world_state)
    
    # 2. Update state based on pressure
    if pressure >= 0.7 and conflict_state.status == 'ACTIVE':
        trigger_schism(conflict_state)
        conflict_state.status = 'FRACTURED'
    elif pressure >= 0.4 and conflict_state.status == 'LATENT':
        conflict_state.status = 'ACTIVE'
    
    # 3. Apply consequences
    apply_social_fracture(world_state, conflict_state)
```

---

## VIII. TƯƠNG TÁC VỚI DECAY & MANIPULATION

### Conflict → Decay
* Conflict làm decay tăng nhanh (cho cả hai myths)
* Fractured state → contradiction pressure cao

### Manipulation → Conflict
* **Amplify:** Tăng institutional backing → tăng pressure
* **Sanitize:** Che giấu conflict → kéo dài latent phase
* **Reframe:** Có thể tạo interpretive conflict
* **Suppress:** Đẩy xuống latent nhưng tích trauma

**Critical Rule:**
> Manipulation chỉ trì hoãn schism, hoặc làm nó bùng nổ mạnh hơn.

---

## IX. EXAMPLE: FULL CONFLICT CYCLE

**Tick 100:** Two myths coexist
* "THE_SILENCE" (knowledge control) - strength 0.75
* "THE_AWAKENING" (knowledge freedom) - strength 0.65
* Conflict: LATENT (pressure 0.35)

**Tick 200:** Tension rises
* Intellectual movement grows
* Conflict: ACTIVE (pressure 0.52)
* Social friction visible

**Tick 300:** Manipulation attempted
* Noble Council sanitizes THE_SILENCE
* Truth debt accumulates
* Conflict: ACTIVE (pressure 0.65)

**Tick 400:** Truth explosion
* Hidden failures exposed
* Pressure spikes to 0.78
* Conflict: **FRACTURED** (Schism triggered)

**Tick 401:** Schism consequences
* Faction split: Orthodox vs Reformist
* Myth fork: "THE_PROTECTIVE_SILENCE" born
* Civil unrest begins

**Tick 500:** Open conflict
* Propaganda wars
* Resource drain
* Both myths decaying from contradiction

**Tick 700:** Resolution
* THE_SILENCE → 0.35 (weakened)
* THE_AWAKENING → 0.55 (dominant but damaged)
* Conflict: RESOLVED
* Scar: "THE_GREAT_SCHISM" created

---

## X. QUY TẮC AI

**AI:**
* ❌ Không tạo conflict arbitrarily
* ❌ Không quyết định schism timing
* ❌ Không force faction splits
* ✅ Chỉ kể câu chuyện từ trạng thái engine
* ✅ Generate narratives reflecting conflict state
* ✅ Respect faction alignments

**Engine decides conflict. AI narrates consequence.**

---

## XI. HỆ QUẢ DÀI HẠN

Hệ này tự sinh ra:

* **Civil war** - Fractured state with violence
* **Holy war** - Religious myth vs religious myth
* **Ideological cold war** - Active state prolonged
* **Civilizational reset** - Resolved → new myth emerges
* **Reformation** - Interpretive conflict → institutional change

**Không cần script. Rules sinh story.**

---

## XII. MAJOR EPOCH TRIGGERING

**Major Epoch = Conflict State Transition**

```
IF conflict.status changes to FRACTURED
THEN major_epoch_triggered = TRUE
```

**Characteristics:**
* Không cần năm cụ thể
* Không cần tên era
* Chỉ cần state change có ý nghĩa
* Replay deterministic

---

## XIII. CIVILIZATIONAL MEMORY ENGINE

**90% hệ thống không đi được tới đây vì:**
* Sợ phức tạp
* Sợ mất kiểm soát
* Sợ world "xấu"

**Với những gì đã xây:**
* Myth không bất tử
* Lịch sử không reset
* AI không được toàn quyền
* Replay vẫn đúng

👉 **Đây không còn là story engine.**

**Đây là: Civilizational Memory Engine**

---

## LỜI KẾT

> **History does not choose a side.
> It breaks along the lines people refuse to cross.**

**Corollaries:**
1. Conflict is inevitable when myths claim same domain
2. Schism is fork, not deletion
3. Manipulation delays but doesn't prevent
4. Major epochs are state transitions
5. System creates history, not humans writing it
