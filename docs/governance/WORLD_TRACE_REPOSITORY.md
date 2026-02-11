# World Trace Repository (WTR)

> **WTR là ký ức lịch sử của toàn bộ hệ thống**.
> Không phục vụ một world. Không phục vụ một story.
> Phục vụ **sự tiến hoá của khả năng kể chuyện**.

---

## I. ĐỊNH NGHĨA CHUẨN (CỰC KỲ QUAN TRỌNG)

**World Trace** = dấu tích *đã xảy ra* khi một world được sinh ra, vận hành, và kết thúc (hoặc ổn định).

**Trace KHÔNG:**
* Thay đổi primitive
* Can thiệp vào world đang chạy

**Trace CHỈ:**
* Ghi nhận
* Trừu tượng hoá
* Làm giàu kho lịch sử

---

## II. VỊ TRÍ KIẾN TRÚC

```
World Simulation
   ↓
World Events
   ↓
Trace Extractor
   ↓
World Trace Repository (WTR)
   ↓
Seed Bias / Myth / Pattern Reference
```

👉 WTR **không nằm trên đường critical runtime**.

---

## III. PHÂN LOẠI TRACE (CORE MODEL)

### 1️⃣ Pattern Trace (mẫu hình lịch sử)

Ghi nhận *cái gì thường xảy ra*.

**Ví dụ:**
* Faith-Dominant Collapse
* Centralized Power Stagnation
* Magic Inflation

```json
{
  "type": "PATTERN",
  "signature": ["FAITH_HIGH", "KNOWLEDGE_CONTROLLED"],
  "outcome": "SOCIAL_COLLAPSE",
  "confidence": 0.72
}
```

---

### 2️⃣ Myth Origin Trace (nguồn gốc thần thoại)

Khi một sự kiện đủ mạnh → trở thành myth.

**Ví dụ:**
* Silent Reformation
* First Forbidden Spell

```json
{
  "type": "MYTH",
  "origin_event": "REFORMATION_SILENT",
  "archetype": "FORBIDDEN_KNOWLEDGE",
  "echo_strength": 0.6
}
```

---

### 3️⃣ Failure Trace (dấu tích thất bại)

World chết cũng có giá trị.

**Ví dụ:**
* Economic Deadlock
* Infinite War Loop

```json
{
  "type": "FAILURE",
  "cause": ["RESOURCE_SCARCE", "TRADE_BLOCKED"],
  "lesson": "MARKET_WITHOUT_TRUST"
}
```

---

### 4️⃣ Stability Trace (hiếm nhưng quý)

Ghi nhận *điều gì giúp world tồn tại lâu*.

```json
{
  "type": "STABILITY",
  "balance": ["FAITH", "REASON"],
  "duration": 1200
}
```

---

## IV. TRACE KHÔNG PHẢI CANON

Đây là điểm sống còn.

* **Trace** = kinh nghiệm hệ thống
* **Canon** = quyết định sáng tác

**Trace:**
* Gợi ý
* Tạo bias
* Tạo myth echo

**Trace KHÔNG ép** world sau phải lặp lại.

---

## V. TRACE EXTRACTION RULES

Trace chỉ được tạo khi:
* World kết thúc
* Hoặc đạt ngưỡng sự kiện lớn

**Trace Extractor:**
* Đọc event stream
* Gom theo window
* Trừu tượng hoá

👉 AI có thể hỗ trợ **sau khi event đã đóng**.

---

## VI. DATABASE SCHEMA (GỢI Ý)

### world_traces

* `id`
* `trace_type` (pattern | myth | failure | stability)
* `signature` (json) - Primitive combination or event pattern
* `outcome` (string) - What happened
* `confidence` (float) - How reliable is this trace (0-1)
* `source_world_id` - Which world generated this trace
* `lesson` (text) - Human-readable insight
* `created_at`

**Indexes:**
* `trace_type`
* `confidence`
* `source_world_id`

---

## VII. SỬ DỤNG TRACE Ở WORLD SAU

### Seed Biasing
* Tăng xác suất seed liên quan trace mạnh

### Myth Injection
* Myth có thể "rơi" vào world mới (echo effect)

### Warning Signal
* Admin / AI thấy tổ hợp nguy hiểm (prevent known failures)

### Pattern Recognition
* Identify successful/failed combinations for Diversity Engine

---

## VIII. TRACE LIFECYCLE

```
World Running → Events Generated
    ↓
World Ends / Reaches Milestone
    ↓
Trace Extractor Analyzes Event Stream
    ↓
Trace Created (Pattern/Myth/Failure/Stability)
    ↓
WTR Storage (Immutable)
    ↓
Referenced by Future Worlds (Bias/Warning/Insight)
```

---

## IX. EXAMPLE: TRACE CREATION

**Scenario:** World "Theocracy Alpha" collapsed after 800 ticks.

**Event Analysis:**
1. Faith primitives dominated (strength > 0.9)
2. Knowledge controlled heavily
3. Multiple reform attempts failed
4. Final collapse via social fracture

**Trace Generated:**
```json
{
  "type": "FAILURE",
  "signature": {
    "primitives": ["THEOCRACY", "KNOWLEDGE_CONTROLLED", "FAITH_DOMINANT"],
    "tension": "FAITH_VS_REASON"
  },
  "outcome": "SOCIAL_COLLAPSE",
  "cause": ["REFORM_SUPPRESSION", "KNOWLEDGE_MONOPOLY"],
  "lesson": "Absolute faith without knowledge flow leads to brittleness",
  "confidence": 0.85,
  "source_world_id": 42,
  "duration_ticks": 800
}
```

**Future Use:**
* Seeds with similar primitive combinations get WARNING tag
* AI prompted to consider knowledge flow when faith is high
* Myth "The Silent Reformation" may echo into new worlds

---

## X. FAILURE MODE CẦN TRÁNH

❌ **Cho trace sửa primitive** - Trace không được modify WFR
❌ **Cho trace auto-canon** - Trace là suggestion, không phải truth
❌ **Cho trace điều khiển AI trực tiếp** - AI reads traces for bias, not commands
❌ **Over-fitting** - Trace from 1 world doesn't define universal law

---

## XI. GOVERNANCE LAW

> **A world may perish.
> Its trace must not.**

**Corollaries:**
1. Every completed world MUST generate at least one trace
2. Traces are IMMUTABLE once created
3. Traces inform but never command
4. Pattern confidence degrades if not reinforced by new worlds

---

## XII. RELATIONSHIP TO OTHER SYSTEMS

**WFR (World Foundation Repository):**
* WFR = What worlds CAN be
* WTR = What worlds HAVE been

**Myths:**
* Myths = In-world narrative memory
* Traces = Cross-world system memory

**Seeds:**
* Seeds = Story inputs
* Traces = Story outcomes (inform future seeds)

---

## HISTORICAL LAW

> **Wisdom comes not from preventing all failures,
> but from remembering them.**
