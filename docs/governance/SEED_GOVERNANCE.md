# Seed Governance

> Seed là **nguồn xung lực có kiểm soát**, không phải công cụ viết story.
> Tài liệu này khóa seed để **không phá world nhưng vẫn tạo emergence**.

---

## PHẦN I – SEED LIMITS (BAO NHIÊU LÀ ĐỦ?)

### 1. Nguyên tắc cốt lõi

> **World chịu được ít seed hơn bạn nghĩ.**

Seed không cộng tuyến.
2 seed mạnh ≠ story gấp đôi → thường là **nhiễu và sụp trật tự**.

---

### 2. Rule chuẩn cho World v1.0

| Dimension | Max Active Seeds | Ghi chú            |
| --------- | ---------------- | ------------------ |
| Personal  | 3                | Không cùng type    |
| Regional  | 2                | Không cùng khu vực |
| Global    | 1                | Luôn độc quyền     |

---

### 3. Hard rules (không override)

* ❌ Không bao giờ có >1 Global seed
* ❌ Không spawn seed mới khi WorldHealth 🔴⚫
* ❌ Không spawn seed mới trong SAFE MODE

---

### 4. Seed collision rule

Nếu vượt giới hạn:

Priority (cao → thấp):

1. Global
2. Regional
3. Personal

Seed thấp hơn:

* Không activate
* Hoặc bị delay

---

## PHẦN II – SEED TYPE → WORLD LAW MAPPING

Seed **không được phép bỏ qua World Law**.
Mỗi type map tới constraint bắt buộc.

---

### 🔥 CONFLICT

Law requirements:

* Power ceiling enforced
* No instant domination

Validator checks:

* Claim magnitude
* Escalation rate

---

### 🔍 DISCOVERY

Law requirements:

* Knowledge ≠ power
* Discovery ≠ mastery

Validator checks:

* Time-to-adoption
* Faction access equality

---

### ⚰️ TRAGEDY

Law requirements:

* No mass extinction
* Irreversibility respected

Validator checks:

* Casualty bounds
* Narrative continuity

---

### ✨ BLESSING

Law requirements:

* Temporary or costly
* No permanent god-mode

Validator checks:

* Duration cap
* Trade-off existence

---

### ❓ MYSTERY

Law requirements:

* Mystery must remain unresolved
* No instant reveal

Validator checks:

* Answer suppression
* Ambiguity ratio

---

### 🔮 PROPHECY

Law requirements (strictest):

* Prophecy ≠ truth
* Belief-driven only

Validator checks:

* AI certainty clamp
* Outcome neutrality

---

## PHẦN III – SEED LIFECYCLE

Seed không tồn tại mãi.
Nó có vòng đời rõ ràng.

---

### 1. DORMANT

* Seed được tạo
* Chưa ảnh hưởng world

Transition → ACTIVE khi:

* Simulation tick chạm trigger
* Faction nhận thức seed

---

### 2. ACTIVE

* World phản ứng
* Event sinh ra từ seed

ACTIVE có thể:

* Escalate
* Spread
* Be ignored

---

### 3. EXHAUSTED

Seed bị coi là exhausted khi:

* Mục tiêu xung lực đạt đỉnh
* World không còn phản ứng
* Seed bị superseded

EXHAUSTED:

* Không sinh event mới
* Chỉ tồn tại cho audit/replay

---

### 4. FORBIDDEN STATES

* ❌ Reactivate EXHAUSTED seed
* ❌ Reset seed outcome

Muốn tạo xung lực mới → **seed mới**.

---

## PHẦN IV – OPERATOR CONTROLS

Operator được phép:

* Delay activation
* Force exhaust (🟡 trở xuống)

Không được phép:

* Change type
* Change dimension
* Edit description

---

## PHẦN V – FAILURE MODES & ALERTS

### Alerts bắt buộc:

* SEED_OVERLOAD
* SEED_LAW_VIOLATION
* PROPHECY_CERTAINTY_BREACH

---

## SEED LAW (KHẮC CỐT)

> **A seed may start a story,
> but it must never finish one.**
