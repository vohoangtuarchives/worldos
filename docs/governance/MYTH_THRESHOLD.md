# Myth Threshold – When History Becomes Memory

> **Myth Threshold là ranh giới nơi sự kiện không còn là lịch sử thuần túy,
> mà trở thành ký ức tập thể của hệ thống.**

Đây là lựa chọn **đúng** nếu mục tiêu của bạn là:
* Nhiều câu chuyện
* Nhiều world
* Nhưng có *bóng của quá khứ* lặp lại một cách có ý nghĩa

---

## I. VẤN ĐỀ MÀ MYTH THRESHOLD GIẢI QUYẾT

**Nếu chỉ lưu event:**
* Dữ liệu nhiều
* Ý nghĩa thấp

**Nếu chỉ lưu world end:**
* Quá trễ
* Mất những khoảnh khắc định hình

👉 Myth Threshold giải quyết câu hỏi:

> **Khi nào một sự kiện đủ mạnh để không bị quên?**

---

## II. ĐỊNH NGHĨA CHUẨN

**Myth Threshold** = ngưỡng mà tại đó một chuỗi sự kiện được:
* Trừu tượng hoá
* Bóp méo
* Biểu tượng hoá

và được ghi vào **World Trace Repository** dưới dạng *Myth Trace*.

**Myth ≠ fact.**
**Myth = fact đã bị lịch sử xử lý.**

---

## III. 4 TRỤC ĐÁNH GIÁ MYTH THRESHOLD

Một event / chuỗi event vượt threshold khi **nhiều trục cùng cao**.

### 1️⃣ Impact (Tác động)

* Bao nhiêu faction bị ảnh hưởng?
* Có thay đổi luật chơi không?

**Ví dụ:**
* Thay đổi power structure
* Xoá sổ tri thức

**Score:** 0-1 based on scope

---

### 2️⃣ Irreversibility (Không thể đảo ngược)

* Có quay lại trạng thái cũ được không?

**Ví dụ:**
* Mất vĩnh viễn một loại magic
* Extinction một being type

**Score:** 1 = hoàn toàn không đảo ngược, 0 = có thể phục hồi

---

### 3️⃣ Narrative Compression (Dễ kể lại)

* Có thể gói trong 1 biểu tượng không?

**Ví dụ:**
* "The First Silence"
* "The Broken Crown"

👉 Cái gì *kể được* mới thành myth.

**Score:** 1 = rất dễ symbol hoá, 0 = quá phức tạp để kể

---

### 4️⃣ Recurrence Potential (Khả năng vang vọng)

* Có thể lặp lại trong world khác không?

**Ví dụ:**
* Tri thức bị cấm
* Đức tin cực đoan

**Score:** 1 = universal pattern, 0 = unique to this world

---

## IV. CÔNG THỨC ĐƠN GIẢN (ENGINE-FRIENDLY)

```text
MythScore =
  Impact * 0.35
+ Irreversibility * 0.30
+ Compression * 0.20
+ Recurrence * 0.15

IF MythScore ≥ 0.7 → Create Myth Trace
```

📌 Không cần chính xác tuyệt đối.
📌 Quan trọng là **nhất quán**.

---

## V. MYTH TRACE TRÔNG NHƯ THẾ NÀO?

### Ví dụ

**World #18 – Event Cluster:**
* Knowledge centralization
* Faith dominance
* Public silence

**→ Myth Trace Created:**

```json
{
  "type": "MYTH",
  "archetype": "FORBIDDEN_KNOWLEDGE",
  "symbol": "THE_SILENCE",
  "origin_signature": ["FAITH_HIGH", "KNOWLEDGE_CONTROLLED"],
  "distortion": 0.6,
  "myth_score": 0.78,
  "source_world_id": 18,
  "created_at": "2026-01-15T10:30:00Z"
}
```

👉 Không lưu chi tiết ai, khi nào.
👉 Lưu **ý nghĩa cô đọng**.

---

## VI. MYTH KHÔNG PHẢI CANON

Rất quan trọng:

**Myth có thể:**
* Sai
* Bị hiểu nhầm
* Bị lợi dụng

**Nhưng myth:**
* Ảnh hưởng seed selection
* Ảnh hưởng taboo formation
* Ảnh hưởng cultural bias

👉 Myth tạo *màu* cho world sau, không áp đặt kết cục.

---

## VII. MYTH TRONG WORLD SAU

### ✅ AI được phép:

* Nhắc myth như truyền thuyết
* Tạo faction tin / không tin
* Reference myth như cultural memory
* Use myth for narrative flavor

### ❌ AI bị cấm:

* Coi myth là fact
* Ép world lặp lại kết cục
* Force myth outcome
* Override primitives with myth

---

## VIII. EXTRACTION WORKFLOW

```
Event Stream
 ↓
Event Window (e.g., 50-100 events)
 ↓
Myth Threshold Calculator
 ↓
Compute 4 axes scores
 ↓
IF MythScore ≥ 0.7
 ↓
Extract Myth Trace
 ↓
Store in WTR
 ↓
Available for future worlds
```

---

## IX. EXAMPLE CALCULATION

**Event Cluster:** "The Great Library Burns"

**Axis Scores:**
* **Impact:** 0.9 (affects all factions, knowledge loss)
* **Irreversibility:** 1.0 (knowledge permanently lost)
* **Compression:** 0.8 (easy symbol: "The Burning")
* **Recurrence:** 0.7 (book burning is universal pattern)

**MythScore:**
```
= 0.9 * 0.35 + 1.0 * 0.30 + 0.8 * 0.20 + 0.7 * 0.15
= 0.315 + 0.30 + 0.16 + 0.105
= 0.88
```

**Result:** ✅ Myth Trace Created - "THE_BURNING"

---

## X. VÌ SAO MYTH THRESHOLD LÀ LỰA CHỌN ĐÚNG?

* **Sớm hơn world end** - Capture pivotal moments during runtime
* **Sâu hơn event** - Abstract to meaningful patterns
* **Phù hợp storytelling** - Myths are narrative-friendly

> **History becomes myth long before the world dies.**

---

## XI. INTEGRATION WITH OTHER SYSTEMS

### With WTR:
* Myth Threshold triggers Myth Trace creation
* Stored alongside Pattern/Failure/Stability traces

### With Seed Bias Engine:
* Myths with high recurrence score bias seed selection
* Warnings for dangerous myth patterns

### With Myth Propagation:
* Cross-world myth echoes
* Cultural memory transmission

---

## XII. TUNING PARAMETERS

### Threshold (default: 0.7)
* Lower (0.5-0.6): More myths, less selective
* Higher (0.8-0.9): Fewer myths, only pivotal events

### Weights (default: 0.35/0.30/0.20/0.15)
* Adjust based on desired myth character
* Example: Emphasize recurrence for universal myths

---

## MYTH LAW

> **What is remembered shapes the future,
> not what truly happened.**

**Corollaries:**
1. Myth must be threshold-gated, not random
2. Myth must be abstracted, not literal
3. Myth must be propagatable, not world-specific
4. Myth must be influential, not definitive
