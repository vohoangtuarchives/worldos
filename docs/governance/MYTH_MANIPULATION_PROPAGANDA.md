# Myth Manipulation & Propaganda Layer

> **Nếu myth là ký ức tập thể, thì tuyên truyền là kỹ thuật chỉnh hình ký ức.**

Tài liệu này định nghĩa cách faction **cố tình can thiệp vào myth** – nhưng **không phá nhân quả, không cho AI quyền bịa lịch sử**.

---

## I. NGUYÊN TẮC KHÔNG THOẢ HIỆP

1. ❌ Faction **không thể tạo myth từ hư vô**
2. ❌ Không thể nâng level myth trực tiếp
3. ❌ Không thể đảo ngược sự thật lịch sử

**Faction chỉ thao túng cách myth được diễn giải và lan truyền.**

---

## II. MANIPULATION ≠ DECAY

| Aspect   | Decay                            | Manipulation             |
| -------- | -------------------------------- | ------------------------ |
| Nature   | Thế giới làm myth không sống nổi | Faction cố giữ myth sống |
| Agency   | Bị động                          | Chủ động                 |
| Driver   | Engine-driven                    | Actor-driven             |

**Hai hệ này song song, không thay thế nhau.**

---

## III. 4 KIỂU MANIPULATION CỐT LÕI

### 1. Amplification (Khuếch đại)

Faction tăng **exposure** của myth.

**Methods:**
* Lễ hội
* Biểu tượng
* Giáo dục

**Effect:**
* → Strength không tăng ngay
* → Nhưng decay chậm lại (reduce contradiction/hypocrisy vectors)

---

### 2. Sanitization (Làm sạch ký ức)

Cắt bỏ phần myth gây đau đớn.

**Examples:**
* Bỏ yếu tố thất bại
* Che nguồn gốc đẫm máu

**Effect:**
* → Giảm contradiction pressure
* → Tăng khả năng tồn tại
* → Nhưng tích lũy "truth debt"

---

### 3. Reframing (Định nghĩa lại)

Không phủ nhận myth. Thay đổi **ý nghĩa**.

**Example:**
* Từ "im lặng để sống" → "im lặng để bảo vệ"

**Effect:**
* → Tạo **biến thể myth** (variant)
* → Original myth không mất, nhưng split strength
* → Risk: Schism (myth war)

---

### 4. Suppression (Đàn áp ký ức)

Không giết myth. Giết **khả năng lan truyền**.

**Methods:**
* Cấm nghi lễ
* Cấm ngôn ngữ
* Xoá biểu tượng

**Effect:**
* → Myth chuyển sang dormant
* → Nhưng tích lũy **trauma debt**
* → High risk: Violent resurgence

---

## IV. COST & RISK (RẤT QUAN TRỌNG)

Manipulation **luôn có giá**.

| Action      | Cost                         | Risk                           |
| ----------- | ---------------------------- | ------------------------------ |
| Amplify     | Economy / Stability          | Resource drain                 |
| Sanitize    | Truth tension (hidden)       | Eventual exposure backlash     |
| Reframe     | Faction coherence            | Schism / Myth war              |
| Suppress    | Trauma accumulation          | Violent myth resurgence        |

👉 **Không có manipulation miễn phí.**

---

## V. PROPAGANDA SYSTEM (ENGINE VIEW)

### PropagandaAction (data structure)

```json
{
  "faction_id": "noble_council",
  "target_myth_id": "THE_SILENCE",
  "method": "SANITIZE",
  "intensity": 0.7,
  "cost_profile": {
    "economy": -0.2,
    "truth_debt": +0.4
  }
}
```

---

### Propaganda Resolver (engine component)

**Per Simulation Cycle:**
1. Evaluate active propaganda actions
2. Apply exposure changes (amplify/suppress)
3. Spawn myth variants (reframe)
4. Add hidden decay debt (sanitize)
5. Track accumulated trauma (suppress)
6. Calculate costs to faction

---

## VI. TƯƠNG TÁC VỚI AI

**AI:**
* ✅ Có thể tạo **propaganda narrative** (how it's presented)
* ✅ Có thể generate faction manipulation attempts
* ❌ Không được quyết định hiệu quả
* ❌ Không được thay đổi strength trực tiếp
* ❌ Không được override engine calculations

**AI kể. Engine quyết.**

---

## VII. EXAMPLE: FULL MANIPULATION CYCLE

**Tick 100:** "THE_SILENCE" myth (strength 0.68)
* Contradiction pressure building (economic issues)

**Tick 150:** Noble Council executes Sanitization
* Remove "economic collapse" from myth narrative
* Cost: Truth debt +0.3
* Effect: Contradiction pressure -0.2

**Tick 250:** Myth appears stable (strength 0.70)
* But truth debt accumulating

**Tick 400:** Truth exposure event
* Hidden economic failures revealed
* Truth debt explodes into trauma
* Myth strength drops 0.70 → 0.45 (sudden collapse)

**Tick 500:** Suppression attempted
* Censor myth discussion
* Trauma accumulation +0.5

**Tick 600:** Violent myth resurgence
* Popular uprising citing THE_SILENCE
* Counter-myth "THE_AWAKENING" born
* Strength redistributes

---

## VIII. MYTH VARIANT SYSTEM

When Reframing succeeds, create myth variant:

```json
{
  "parent_myth_id": "THE_SILENCE",
  "variant_name": "THE_PROTECTIVE_SILENCE",
  "strength": 0.35,  // Split from parent
  "narrative_delta": "silence preserves order, not just survival"
}
```

**Parent myth strength:** 0.68 → 0.50 (lost 0.18 to variant)
**Variant myth strength:** 0.35 (new)

**Risk:** If variants diverge too much → myth war

---

## IX. TRUTH DEBT MECHANICS

Hidden counter that accumulates from Sanitization:

```
truth_debt = Σ(sanitization_intensity × duration)

IF truth_debt ≥ threshold (e.g., 1.0)
AND exposure_event occurs
THEN: 
  myth.strength -= truth_debt * explosion_factor
  trauma_event_triggered = TRUE
```

**Truth is patient, but collects interest.**

---

## X. HỆ QUẢ DÀI HẠN (THỨ BẠN MUỐN)

Hệ này tự sinh ra:

* **Historical revisionism** - State sanitization over generations
* **State religion** - Amplification becomes institutionalized
* **Forbidden history** - Suppression creates underground knowledge
* **Myth wars** - Reframing creates schisms
* **Cyclical violence** - Truth debt explosions

**Không cần viết kịch bản. Rules sinh story.**

---

## XI. QUY TẮC GOVERNANCE

### Faction Capabilities
* Can attempt propaganda (within resources)
* Cannot guarantee success
* Cannot escape costs

### Engine Responsibilities
* Calculate effectiveness
* Track hidden debts
* Trigger consequences
* Maintain causality

### AI Boundaries
* Generate narratives
* Respect engine outcomes
* Cannot override costs
* Cannot create ex nihilo

---

## XII. FAILURE MODES CẦN TRÁNH

❌ **Cost-free manipulation** - Always extract price
❌ **AI-driven success** - Engine must validate
❌ **Instant myth creation** - Must have foundation
❌ **No backlash** - Truth debt must explode eventually
❌ **Perfect propaganda** - Failure is feature, not bug

---

## SỰ THẬT CUỐI

> **Those who control myths do not control truth.
> They only decide how long truth must wait.**

**Corollaries:**
1. Every manipulation has cost
2. Truth debt always compounds
3. Suppression breeds violence
4. Reframing risks schism
5. No faction can myth-create, only myth-shape
