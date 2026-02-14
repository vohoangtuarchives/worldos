# ADR-0008: AI Ontology Contract

> **Purpose**: Prevent AI from creating new ontology and breaking world canon.

---

## I. VẤN ĐỀ CẦN KHÓA

AI không phân biệt được:

* "khái niệm tồn tại"
* và "cách kể chuyện"

👉 Nếu không khóa, AI sẽ:

* tạo văn hoá mới
* tạo hệ quyền lực mới
* phá canon mà không biết là mình phá

---

## II. QUYẾT ĐỊNH KIẾN TRÚC

**AI không được quyền chạm vào tầng ontology.**

### Cụ thể:

**AI ĐƯỢC:**

* instantiate từ primitive
* kết hợp primitive
* suy luận bên trong primitive set

**AI BỊ CẤM:**

* tạo primitive mới
* đổi nghĩa primitive
* suy diễn khái niệm không map được

---

## III. NGUYÊN TẮC CỨNG (HARD RULES)

* **All abstract concepts MUST map to Primitive IDs**
* **Unknown concept = ERROR, not creativity**
* **Ontology gaps require human governance**

👉 Đây là **"Hiến pháp AI"** của world.

---

## IV. FAILURE NẾU KHÔNG CÓ ADR NÀY

* **World drift** (lệch nền sau vài tháng)
* **AI tạo "soft retcon"**
* **Bạn không còn khả năng debug story**

---

## V. PRIMITIVEPROTECT FLOW

### 1️⃣ PrimitiveGuard (Input Gate)

**Nhiệm vụ**: Bảo vệ AI khỏi input sai nền.

```php
class PrimitiveGuard
{
    public function validate(array $primitiveRefs): void
    {
        foreach ($primitiveRefs as $domain => $code) {
            if (!Primitive::exists($domain, $code)) {
                throw new OntologyViolation(
                    "Unknown primitive: {$domain}.{$code}"
                );
            }
        }
    }
}
```

**Dùng ở:**
* Seed creation
* AI prompt build
* World bootstrap

---

### 2️⃣ AIResponseValidator (Output Gate)

**Nhiệm vụ**: Chặn hallucination.

```php
class AIResponseValidator
{
    public function validate(array $aiOutput): void
    {
        if ($this->containsNewOntology($aiOutput)) {
            throw new OntologyViolation(
                'AI attempted to introduce new ontology'
            );
        }
    }
}
```

**Rule:**

AI chỉ được trả về:
* instance
* event
* relation

**Không** abstract concept mới

---

### 3️⃣ Flow Chuẩn

```
Seed
 → PrimitiveGuard
 → Prompt Builder
 → AI
 → AIResponseValidator
 → World State
```

👉 Nếu thiếu 1 gate → hệ không an toàn.

---

## VI. ADMIN UI – READ-ONLY + PROPOSAL

### Nguyên Tắc UI

**❌ Admin không được:**

* edit primitive trực tiếp
* xoá primitive
* sửa nghĩa

**✅ Admin chỉ được:**

* xem
* propose
* justify

---

### Primitive Proposal Flow

```
Admin đề xuất primitive
 → mô tả triết học
 → phân tích tác động
 → gắn ADR
 → review
 → merge vào WFR vX.Y.0
```

**Primitive mới chỉ tồn tại khi có ADR.**

---

### UI Components

#### Primitive Detail (Read-only)

* Code
* Domain
* Description
* Introduced in version
* Linked ADRs

#### Proposal Form

* Proposed Code
* Domain
* Why existing primitives insufficient?
* Power shift analysis

---

## ONTOLOGY LAW (KHẮC CỐT)

> **AI may tell a story,
> but never define the world.**
