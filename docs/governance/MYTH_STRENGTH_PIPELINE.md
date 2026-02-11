# Myth Strength & Influence Pipeline

> **Không phải myth nào cũng ngang nhau.**
> Myth chỉ trở nên nguy hiểm (hoặc thiêng liêng)
> khi nó có **độ mạnh (Strength)** và **đường ảnh hưởng rõ ràng**.

---

## I. ĐỊNH NGHĨA CHUẨN: MYTH STRENGTH

**Myth Strength** = mức độ mà một myth:
* Bám rễ vào ký ức tập thể
* Được truyền qua thế hệ
* Được tổ chức hoá

**Strength KHÔNG PHẢI độ đúng.**
**Strength = độ không thể bị quên.**

---

## II. NGUỒN GỐC STRENGTH (RẤT QUAN TRỌNG)

Myth strength **không gán tay**.
Nó được **suy ra từ Myth Threshold input**.

### Các yếu tố cấu thành

1. **Impact Depth** – Tổn thất / thay đổi bao sâu?
2. **Population Reach** – Bao nhiêu faction chịu ảnh hưởng?
3. **Duration** – Kéo dài bao lâu?
4. **Institutionalization Potential** – Có thể tổ chức hoá không?

---

## III. THANG STRENGTH (ENGINE-FRIENDLY)

### Level 1 – Weak Echo

* **Strength:** 0.2 – 0.4
* **Trạng thái:** *whispered memory*

**Ảnh hưởng:**
* Soft seed bias
* Narrative flavor

👉 Myth tồn tại, nhưng không ai sống vì nó.

---

### Level 2 – Cultural Anchor

* **Strength:** 0.4 – 0.7
* **Trạng thái:** *shared belief*

**Ảnh hưởng:**
* Taboo
* Ritual
* Social norm

👉 Myth trở thành **văn hoá**.

---

### Level 3 – Active Myth

* **Strength:** ≥ 0.7
* **Trạng thái:** *organized belief*

**Ảnh hưởng:**
* Religion / cult
* Prophecy
* Forbidden doctrine

👉 Myth **hành động thông qua con người**.

---

## IV. PIPELINE 1 → 2 → 3 (KHÔNG NHẢY CÓC)

Đây là điểm **hoàn toàn đúng**.

```
Myth Trace Created
  ↓ (strength < 0.4)
Level 1: Soft Bias
  ↓ (strength ≥ 0.4)
Level 2: Cultural Embedding
  ↓ (strength ≥ 0.7)
Level 3: Active Myth
```

❌ **Không cho phép:**
* Active myth nếu chưa có văn hoá
* Religion nếu chưa có myth
* Nhảy từ Level 1 → Level 3

---

## V. VÍ DỤ CỤ THỂ (END-TO-END)

### World #18 – Myth Origin: THE SILENCE

**Myth Threshold Calculation:**
* Impact: cao
* Irreversible: cao
* Compression: rất cao
* Duration: dài

**→ Myth Strength = 0.78 (Level 3)**

---

### World #42 (Receives Echo)

**Myth Strength in this world: 0.55 (Level 2)**

**Ảnh hưởng:**
* Cultural taboo: *knowledge hoarding*
* Ritual: *Day of Silence*
* Social norm: *Scholars are mistrusted*

---

### World #87 (Strong Echo)

**Myth Strength in this world: 0.82 (Level 3)**

**Ảnh hưởng:**
* **Cult of Silence** (organized religion)
* Doctrine: *Truth must be earned through loss*
* Prophecy: *The Second Silence will purify*
* Active suppression of knowledge sharing

👉 Không world nào giống nhau, nhưng myth **tiến hoá hợp lý**.

---

## VI. QUY TẮC AI (CỰC KỲ QUAN TRỌNG)

**AI:**
* ❌ Không được nâng strength arbitrarily
* ❌ Không được tạo myth level 3 nếu level 2 chưa tồn tại
* ❌ Không được skip pipeline
* ✅ Chỉ được instantiate theo level hiện tại
* ✅ Phải respect cultural evolution path

**Strength = engine-calculated, not AI-decided.**

---

## VII. STRENGTH CALCULATION FORMULA

```
Myth Strength (initial) = MythScore (from Threshold)

Myth Strength (in future world) = 
  Origin Strength * 0.5
+ Recurrence Match * 0.3
+ Random Variation * 0.2
```

**Example:**
* Origin: 0.78
* Recurrence Match: 0.6 (world has KNOWLEDGE_CONTROLLED)
* Random: 0.7

**→ Future Strength = 0.78 * 0.5 + 0.6 * 0.3 + 0.7 * 0.2 = 0.71 (Level 3)**

---

## VIII. DB SCHEMA GỢI Ý

### myth_traces (extends world_traces)

* `id`
* `archetype` (e.g., FORBIDDEN_KNOWLEDGE)
* `symbol` (e.g., THE_SILENCE)
* `strength` (float, 0-1)
* `level` (1|2|3, computed from strength)
* `origin_signature` (json)
* `source_world_id`
* `created_at`

### myth_propagations (track echoes)

* `id`
* `myth_trace_id`
* `target_world_id`
* `propagated_strength` (float, may differ from origin)
* `manifestation` (json - how it appears in target world)
* `created_at`

---

## IX. STRENGTH DECAY

Myth strength can decay over time if not reinforced:

```
IF no worlds reference myth in N generations
THEN strength *= 0.9 (decay factor)
```

This prevents myth inflation.

---

## X. EXAMPLE WORKFLOW

**Step 1: Myth Created**
```json
{
  "myth_id": 1,
  "archetype": "FORBIDDEN_KNOWLEDGE",
  "symbol": "THE_SILENCE",
  "strength": 0.78,
  "level": 3
}
```

**Step 2: World #42 Created**
* Has primitive: KNOWLEDGE_CONTROLLED
* Myth propagation system checks for matching myths
* Finds "THE_SILENCE" with strength 0.78
* Calculates propagated strength: 0.55 (Level 2)

**Step 3: AI Generation for World #42**
* AI sees myth at Level 2 (Cultural Anchor)
* AI allowed to: create taboos, rituals, norms
* AI forbidden to: create organized religion (Level 3 only)

**Result:** World has cultural echo, not full religion

---

## XI. FAILURE MODES CẦN TRÁNH

❌ **Myth strength inflation** - Every myth becomes Level 3
❌ **Skip pipeline** - Cult without culture
❌ **AI override** - AI boosts strength manually
❌ **No decay** - Dead myths never fade

---

## HISTORICAL TRUTH

> **A myth does not rule the world when it is born,
> but when people begin to live by it.**

**Corollaries:**
1. Strength must be earned through impact, not assigned
2. Levels must progress sequentially (1 → 2 → 3)
3. AI must respect level constraints
4. Myths must be allowed to fade if not reinforced
