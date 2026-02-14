# World Diversity Engine

> **Mục tiêu**: Đa dạng thế giới một cách có kiểm soát.
> Không phải nhiều dữ liệu hơn, mà là **kết hợp đúng**.

---

## I. VẤN ĐỀ THỰC SỰ (NÓI THẲNG)

Nếu chỉ:
* Thêm nhiều primitive
* Thêm nhiều seed

→ Bạn sẽ có **đa dạng giả** (cosmetic diversity).

Thế giới chỉ thực sự khác nhau khi:

> **CÙNG DỮ LIỆU NỀN nhưng kết hợp theo logic khác**.

**Do đó cần 2 trụ cột song song:**
1. Kho dữ liệu nền đa chiều
2. Rule Engine để kết hợp hợp lý

---

## II. KHO DỮ LIỆU NỀN – THIẾT KẾ ĐÚNG CÁCH

### Nguyên tắc vàng

1. **Ít nhưng sâu** (small × composable)
2. **Không trùng semantic**
3. **Mỗi primitive = 1 lập trường triết học**

---

## III. CẤU TRÚC DỮ LIỆU NỀN (CANONICAL SET)

### 1️⃣ Axis-based Primitives (trục thế giới)

Thay vì enum phẳng → dùng **axis**.

#### Ví dụ: Governance Axis

* `CENTRALIZED`
* `FEDERATED`
* `FRAGMENTED`

👉 Sau đó kết hợp với hình thức cụ thể.

---

### 2️⃣ Tension Primitives (mâu thuẫn nền)

Định nghĩa **lực kéo ngược chiều**.

**Ví dụ:**
* `ORDER` ↔ `CHAOS`
* `TRADITION` ↔ `PROGRESS`
* `FAITH` ↔ `REASON`

👉 Diversity sinh ra từ **độ căng**, không phải số lượng.

---

### 3️⃣ Constraint Primitives (giới hạn)

Primitive không chỉ mô tả cái có, mà còn mô tả **cái không thể**.

**Ví dụ:**
* `MAGIC_FORBIDDEN`
* `IMMORTALITY_RARE`
* `KNOWLEDGE_CONTROLLED`

---

## IV. RULES KẾT HỢP – PHẦN QUAN TRỌNG NHẤT

Nếu không có rule → primitive sẽ **xung đột ngầm**.

---

## V. COMBINATION RULE ENGINE

### Rule Type A – Compatibility Rules

Xác định cái gì **được phép đi cùng**.

```yaml
IF Governance = CENTRALIZED
THEN PowerSource != POPULAR_CONSENSUS
```

---

### Rule Type B – Tension Rules

Không cấm, nhưng tạo **hệ quả**.

```yaml
IF ValueSystem = HONOR_BASED
AND Economy = MARKET
THEN SocialInstability += MEDIUM
```

---

### Rule Type C – Emergence Rules

Khi tổ hợp đủ mạnh → sinh hiện tượng mới.

```yaml
IF Faith > 0.8
AND Knowledge_Controlled = TRUE
THEN MythEmergence = TRUE
```

---

## VI. DIVERSITY KHÔNG ĐẾN TỪ RANDOM

❌ Random primitive
❌ Noise-based mutation

✅ Controlled permutation
✅ Rule-weighted selection

> **Diversity = số lượng tổ hợp hợp lệ, không phải số primitive.**

---

## VII. IMPLEMENTATION GỢI Ý (ENGINE LEVEL)

### Data Layer

* `world_primitives` (existing)
* `primitive_axes` (new)
* `primitive_tensions` (new)
* `combination_rules` (new)

### Rule Layer

* `combination_rules` - Compatibility validation
* `emergence_rules` - Emergent phenomena triggers

### Runtime Flow

```
WorldBuilder
 → select axes values
 → apply constraint primitives
 → validate compatibility rules
 → inject tension primitives
 → evaluate emergence rules
 → finalize world profile
```

---

## VIII. FAILURE MODES CẦN TRÁNH

❌ **Primitive quá cụ thể** → story hóa (không nên có `THE_GREAT_WAR_OF_X`)
❌ **Rule quá lỏng** → world loạn, không coherent
❌ **Rule quá cứng** → world giống nhau, mất diversity

---

## IX. EXAMPLE: WORLD CREATION FLOW

**Step 1: Select Axis Values**
```
Governance: FEDERATED
Economy: COIN_BASED
Culture: HONOR_BASED
```

**Step 2: Apply Constraints**
```
MAGIC_FORBIDDEN = TRUE
→ Removes all magic-dependent primitives from pool
```

**Step 3: Validate Compatibility**
```
Rule: IF Economy = COIN_BASED AND Culture = HONOR_BASED
→ WARNING: Potential tension (honor vs profit)
→ Create tension primitive: HONOR_VS_WEALTH
```

**Step 4: Check Emergence**
```
Rule: IF Governance = FEDERATED AND Honor_Tension = HIGH
→ TRIGGER: Regional_Honor_Codes (different honor systems per region)
```

**Step 5: Finalize**
```
World Profile = {
  axes: [FEDERATED, COIN_BASED, HONOR_BASED],
  constraints: [MAGIC_FORBIDDEN],
  tensions: [HONOR_VS_WEALTH],
  emergent: [REGIONAL_HONOR_CODES]
}
```

---

## X. GOVERNANCE LAW

> **World diversity is not chaos.
> It is disciplined difference.**

**Corollaries:**
1. Every primitive must have **semantic uniqueness**
2. Every combination must pass **rule validation**
3. Every tension must have **mechanical consequence**

---

## NEXT STEPS (IMPLEMENTATION)

1. **Extend WFR** - Add axis/tension/constraint types
2. **Build Rule Engine** - Compatibility, Tension, Emergence validators
3. **Update WorldBuilder** - Use rule-based combination instead of free selection
4. **UI for Rules** - Read-only rule viewer + proposal system (like primitives)
