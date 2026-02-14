# Myth Decay & Counter-Myth System

> **Myth sinh ra không để bất tử.**
> Một world sống là world có khả năng **quên, nghi ngờ và phản bội ký ức của chính nó**.

Tài liệu này định nghĩa **cách myth suy yếu, biến dạng, bị giết hoặc bị thay thế** – mà không phá replay, không trao quyền cho AI tuỳ tiện.

---

## I. NGUYÊN TẮC GỐC (KHÔNG THOẢ HIỆP)

1. Myth **không tự decay theo thời gian**
2. Myth chỉ decay khi:
   * Mâu thuẫn với thực tại sống
   * Bị tổn hại bởi hành động có ý nghĩa
3. Decay **không xóa myth** → chỉ làm yếu influence

**Myth không chết vì bị lãng quên.**
**Myth chết vì không còn sống được nữa.**

---

## II. DECAY VECTOR – MYTH BỊ TỔN THƯƠNG NHƯ THẾ NÀO

Mỗi myth có nhiều **decay vector**, không bao giờ chỉ một.

### 1. Contradiction Pressure

Thực tại liên tục chứng minh myth sai.

**Ví dụ:**
* Doctrine: "tri thức gây sụp đổ"
* World reality: tri thức kiểm soát vẫn phục hồi kinh tế

**→ strength -= Δ (nhỏ, tích luỹ)**

---

### 2. Internal Hypocrisy

Faction sống trái myth nhưng **vẫn dùng myth để cai trị**.

**Ví dụ:**
* Giáo sĩ giữ tri thức cấm
* Elite được miễn taboo

**→ decay nhanh hơn contradiction**

---

### 3. Trauma Override

Một event lớn **phản myth trực diện**.

**Ví dụ:**
* Sacrifice theo doctrine gây diệt vong

**→ strength drop đột ngột**

---

### 4. Counter-Myth Emergence

Myth khác **cạnh tranh cùng domain**.

**Ví dụ:**
* "Silence preserves order"
* vs "Truth heals decay"

**→ strength bị siphon**

---

## III. DECAY KHÔNG PHẢI TUYẾN TÍNH

**Không có:**
```
strength -= time
```

**Có:**
```
strength -= Σ(decay_vectors × exposure)
```

* **World yên ổn** = myth ổn
* **World biến động** = myth bị thử thách

---

## IV. THRESHOLD NGƯỢC – MYTH REGRESSION

Giống sinh myth, decay cũng có threshold.

| Strength | Trạng thái      | Behavior                      |
| -------- | --------------- | ----------------------------- |
| ≥ 0.7    | Active Myth     | Religion, cult, organized     |
| 0.4-0.7  | Cultural Anchor | Taboo, ritual, social norm    |
| 0.2-0.4  | Weak Echo       | Narrative flavor, soft bias   |
| < 0.2    | Dormant Trace   | Stored in WTR, no active role |

⚠️ **Myth không biến mất hoàn toàn.**
Dormant myth có thể **hồi sinh** nếu conditions match lại.

---

## V. COUNTER-MYTH SYSTEM (CỰC KỲ QUAN TRỌNG)

Counter-myth **không phải phản đối trực tiếp**.
Nó cạnh tranh bằng **ý nghĩa thay thế**.

### Quy tắc sinh Counter-Myth

1. **Phải cùng domain** (e.g., cả hai về knowledge/faith)
2. **Phải giải thích tốt hơn nỗi đau hiện tại**
3. **Phải có ritual / narrative seed**

### Ví dụ

**Original Myth:** "THE_SILENCE" (knowledge must be controlled)
* Strength: 0.75 (Active)

**Counter-Myth:** "THE_AWAKENING" (truth liberates)
* Born from: intellectual resistance movement
* Strength: 0.45 (Cultural Anchor)

**Competition:**
* As "THE_AWAKENING" grows → "THE_SILENCE" decays
* Not replacement, but **strength redistribution**

---

## VI. ENGINE IMPLEMENTATION (TÓM GỌN)

### MythState (extends myth_traces)

* `id`
* `archetype`
* `strength` (current, mutable)
* `level` (computed from strength)
* `decay_exposure` (accumulated contradiction/hypocrisy)
* `competing_myths[]` (array of counter-myth IDs)

### Decay Resolver (runs each simulation cycle)

```python
def resolve_decay(myth, world_state):
    # 1. Evaluate contradictions
    contradiction = check_reality_vs_doctrine(myth, world_state)
    
    # 2. Detect hypocrisy events
    hypocrisy = detect_elite_violations(myth, world_state)
    
    # 3. Check trauma
    trauma = check_myth_failed_events(myth, world_state)
    
    # 4. Apply counter-myth siphon
    siphon = sum_competing_myths_strength(myth)
    
    # 5. Calculate decay
    total_decay = (
        contradiction * 0.1 +
        hypocrisy * 0.2 +
        trauma * 0.3 +
        siphon * 0.15
    )
    
    myth.strength -= total_decay
    myth.strength = max(0, myth.strength)  # Floor at 0
    
    # 6. Update level based on new strength
    myth.level = compute_level(myth.strength)
```

---

## VII. RESURRECTION CONDITION

Dormant myths (strength < 0.2) can resurrect if:

```
IF world conditions match myth origin signature
AND no strong counter-myth exists
THEN strength += resurrection_boost (0.2-0.4)
```

**Example:**
* "THE_SILENCE" dormant (strength 0.15)
* New regime: KNOWLEDGE_CONTROLLED + FAITH_HIGH
* → Strength boost to 0.45 (Cultural Anchor level)

---

## VIII. QUY TẮC AI (KHOÁ CỨNG)

**AI:**
* ❌ Không được giết myth trực tiếp
* ❌ Không được decay strength manually
* ❌ Không được tạo counter-myth tự do
* ✅ Chỉ được tạo narrative seed nếu engine cho phép
* ✅ Phải respect myth level constraints

**Decay = engine-calculated.**
**Narrative = AI-generated (within bounds).**

---

## IX. EXAMPLE: FULL DECAY CYCLE

**Tick 100:** "THE_SILENCE" active (strength 0.78)
* Organized religion, knowledge suppression

**Tick 200:** Contradiction accumulates
* Economic stagnation blamed on ignorance
* strength → 0.72

**Tick 300:** Hypocrisy exposed
* Elite hoard forbidden books
* strength → 0.63 (drops to Cultural Anchor)

**Tick 400:** Counter-myth emerges
* "THE_AWAKENING" born (strength 0.42)
* Siphon effect begins
* "THE_SILENCE" → 0.55

**Tick 500:** Trauma event
* Inquisition massacre backfires
* strength → 0.35 (Weak Echo)

**Tick 600:** Dormancy
* strength → 0.18 (Dormant Trace)
* Myth stored in WTR, no active influence

**Tick 800:** Potential resurrection
* New authoritarian regime
* Conditions match origin signature
* (Engine may boost strength if appropriate)

---

## X. FAILURE MODES CẦN TRÁNH

❌ **Auto-decay by time** - Myths must die meaningfully
❌ **AI-driven myth killing** - Only engine can decay
❌ **Counter-myths without foundation** - Need narrative seed
❌ **Instant myth death** - Regression should be gradual (except trauma)
❌ **No resurrection path** - Dormant myths can return

---

## SỰ THẬT KHÓ CHỊU

> **Revolutions do not destroy myths.
> They merely replace the one people can no longer live with.**

**Corollaries:**
1. Myth decay must be earned through world events
2. Counter-myths must compete, not simply negate
3. Dormant myths remember and can return
4. The death of a myth is always a birth of another
