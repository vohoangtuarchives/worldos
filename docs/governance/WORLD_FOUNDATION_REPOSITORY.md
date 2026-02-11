# World Foundation Repository (WFR)

> Kho dữ liệu nền tảng để **tạo yếu tố sơ khai của world**.
> Đây là lớp *trước Seed*, *trước Story*, *trước AI*.

---

## I. VẤN ĐỀ CỐT LÕI

Hiện tại hệ thống có:

* Seed (xung lực)
* Law (giới hạn)
* Simulator (diễn biến)

Nhưng **thiếu nền**:

> AI + Simulator đang phải **tự bịa vật liệu ban đầu**.

Hậu quả nếu không có kho nền:

* World bị "generic"
* Emergence lặp pattern
* AI hallucinate văn hoá / kinh tế / xã hội

---

## II. ADR - WORLD FOUNDATION GOVERNANCE & VERSIONING

### Mục tiêu

Khóa tầng nền của world khỏi:

* cảm hứng nhất thời
* yêu cầu business ngắn hạn
* sự "sáng tạo" quá tay của AI

**Nếu không có ADR này**, sớm hay muộn sẽ:

* sửa primitive để phục vụ 1 story
* rồi phá toàn bộ canon

---

## III. QUYẾT ĐỊNH KIẾN TRÚC (ADR CORE)

### 🧱 Quyết định

**World Foundation Repository (WFR) là IMMUTABLE trong runtime**

Primitive:

* chỉ được add (theo version)
* không sửa nghĩa
* không xóa

World chỉ tham chiếu primitive theo **version**

---

## IV. VERSIONING STRATEGY

### Semantic version cho WFR

* WFR v1.0.0
* WFR v1.1.0 (add primitive)
* WFR v2.0.0 (break ontology)

### Rule:

* World đang chạy → gắn cứng 1 version
* **Không auto-upgrade**

👉 Đây là chỗ nhiều hệ chết vì "update nền".

---

## V. CHANGE PROCESS (GOVERNANCE)

### Primitive mới KHÔNG được thêm bởi:

* Admin thường
* AI
* Runtime event

### Chỉ được thêm khi:

Viết ADR mới, chỉ rõ:

* Primitive này mở ra cái gì?
* Cấm cái gì?
* Tác động đến power balance?

> **Primitive = luật hiến pháp ngầm**

---

## VI. FAILURE MODES CẦN TRÁNH

❌ "Thêm tạm primitive cho story này"
❌ "AI đề xuất primitive mới"
❌ "World A có primitive riêng"

👉 **Primitive là global canon, không phải asset.**

---

## VII. ĐỊNH NGHĨA: WORLD FOUNDATION REPOSITORY

**WFR = tập hợp các primitive đã chuẩn hoá**, dùng để:

* Khởi tạo world
* Làm vật liệu cho Seed
* Làm vocabulary cho AI

> WFR **không kể chuyện**, chỉ định nghĩa *cái có thể tồn tại*.

---

## VIII. PHÂN LỚP DỮ LIỆU NỀN (BẮT BUỘC)

### 1️⃣ Civilizational Primitives (Xã hội)

Định nghĩa **cách con người sống cùng nhau**.

Ví dụ entity:

* GovernanceForm: monarchy, republic, theocracy
* SocialClass: noble, merchant, peasant, outcast
* LawTradition: codified, customary, divine

---

### 2️⃣ Cultural Primitives (Văn hoá)

Định nghĩa **cách họ nghĩ & tin**.

Ví dụ:

* ValueSystem: honor-based, wealth-based, faith-based
* Taboo: blood_magic, regicide
* RitualType: coronation, harvest_festival

---

### 3️⃣ Economic Primitives (Kinh tế)

Định nghĩa **cách tài nguyên vận hành**.

Ví dụ:

* ResourceType: grain, mana, iron
* TradeModel: barter, coin-based, tribute
* ScarcityLevel: abundant, scarce

---

### 4️⃣ Power Primitives (Quyền lực)

Định nghĩa **quyền lực đến từ đâu**.

Ví dụ:

* PowerSource: military, magic, bloodline, knowledge
* Legitimacy: divine_right, popular_support

---

### 5️⃣ Ontological Primitives (Bản thể học)

Định nghĩa **thế giới cho phép cái gì tồn tại**.

Ví dụ:

* BeingType: human, spirit, construct
* DeathRule: permanent, reincarnation, reversible
* MagicNature: wild, structured, forbidden

---

## IX. NGUYÊN TẮC THIẾT KẾ DỮ LIỆU

### 1. Primitive ≠ Instance

* Primitive: "Monarchy"
* Instance: "House of Aurelion"

👉 WFR chỉ chứa Primitive.

---

### 2. Finite & Opinionated

* Không open-ended enum
* Mỗi primitive là **một lựa chọn triết học**

---

### 3. Composable, not hierarchical

* Primitive có thể kết hợp
* Không hard-code tree phức tạp

---

## X. WFR ↔ SEED ↔ WORLD LAW

### Seed creation rule:

Seed **PHẢI tham chiếu WFR**.

Ví dụ:

* CONFLICT seed phải chỉ rõ:
  * GovernanceForm
  * PowerSource

---

### Law validation:

World Law có thể:

* Cho phép / cấm primitive

Ví dụ:

* MagicSystemType = NONE
* → cấm mọi PowerSource = magic

---

## XI. AI USAGE RULES

### AI được phép:

* Kết hợp primitive
* Tạo instance từ primitive

### AI **KHÔNG được phép**:

* Tạo primitive mới
* Sửa nghĩa primitive

---

## XII. AI PROMPT CONTRACT

### Nguyên tắc cứng

AI KHÔNG BAO GIỜ được:

* tạo primitive mới
* đổi nghĩa primitive
* suy luận ngoài primitive list

AI CHỈ ĐƯỢC:

* chọn
* kết hợp
* instantiate

### Prompt Contract - Conceptual

```
"You are operating inside a governed world.
All concepts MUST reference existing Primitive IDs.
If a concept cannot be expressed using primitives,
you MUST ask for a primitive extension request."
```

### Ví dụ: Seed → AI

**Input cho AI:**
```json
{
  "seed_type": "CONFLICT",
  "primitives": {
    "governance_form": "MONARCHY",
    "power_source": "DIVINE_RIGHT",
    "value_system": "HONOR_BASED"
  }
}
```

**AI được phép trả về:**
```json
{
  "instances": {
    "ruling_house": "House of Solen",
    "conflict_trigger": "Loss of divine sign"
  }
}
```

**AI không được phép:**

❌ invent:
* new religion system
* new power source
* new social class

---

## XIII. VALIDATION LAYER

### Components needed:

* PrimitiveGuard
* AIResponseValidator

### Flow:

```
AI output
 → validate primitive refs
 → reject unknown concept
 → request clarification OR fail fast
```

👉 **Fail sớm còn hơn story đẹp nhưng sai nền.**

---

## XIV. IMPLEMENTATION GỢI Ý (LARAVEL-FRIENDLY)

### Storage

Bảng `world_primitives`:

* id
* domain (civilization, culture, economy, power, ontological)
* code (MONARCHY, HONOR_BASED, etc.)
* description
* constraints (json) - what this enables/forbids
* version (WFR version)
* tags (json)

---

### Access Layer

* WorldFoundationRepository
* Read-only
* Cached

---

### Governance

* Primitive change = World Engine v2 only
* Không edit runtime

---

## XV. FAILURE MODES (CẦN TRÁNH)

❌ Để AI tự định nghĩa văn hoá
❌ Primitive quá chi tiết (trở thành story)
❌ Cho phép sửa primitive theo world

---

## FOUNDATION LAW (KHẮC CỐT)

> **A world may evolve its stories,
> but never its foundations.**
