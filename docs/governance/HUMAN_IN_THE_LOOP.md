# Human-in-the-Loop – Curator, Not Author

> **Ở thời điểm này, hệ của bạn đã đạt mức: không cần viết lore, không cần viết cốt truyện, không cần AI "thông minh hơn" → chỉ cần thời gian + ký ức.**

Tài liệu này định nghĩa cách nhà văn chạm vào mà không làm bẩn hệ.

---

## I. CON NGƯỜI KHÔNG ĐƯỢC LÀM GÌ

❌ **Sửa myth trực tiếp**
❌ **Sửa archetype weight**
❌ **Quyết định collapse outcome**
❌ **"Tạo câu chuyện hay hơn"**

**Nếu cho phép → AI + human = narrator toàn tri → hệ chết.**

---

## II. CON NGƯỜI ĐƯỢC PHÉP LÀM GÌ?

**Chỉ 3 loại can thiệp, gọi là soft bias.**

### 1️⃣ Seeding Bias (trước khi world sinh)

**Nhà văn chọn:**
* Archetype focus (2–3 cái)
* Myth threshold cao/thấp
* **Không chọn kết quả**

👉 **Giống chọn địa hình, không vẽ đường.**

---

### 2️⃣ Pressure Injection (trong saga)

**Không ra lệnh:**
* Hãy sụp đổ

**Mà tạo áp lực mù:**
* Resource shock
* Information distortion
* Demographic imbalance

👉 **Hệ tự phản ứng.**

---

### 3️⃣ Selection, không phải Correction (sau saga)

**Nhà văn:**
* Đọc historian output
* Chọn saga đáng kể
* Bỏ saga tẻ nhạt

👉 **Đây là cách lịch sử thật được ghi nhớ.**

---

## III. VAI TRÒ THẬT CỦA NHÀ VĂN

**Không phải:**
* Người kể chuyện

**Mà là:**
* Người chọn cái gì được nhớ

**Giống sử gia, không phải thần.**

---

## IV. DẤU HIỆU BẠN LÀM HUMAN-IN-THE-LOOP ĐÚNG

**Bạn sẽ thấy:**
* Bạn không đoán được kết cục
* Bạn chỉ hiểu sau khi nó xảy ra
* Bạn thấy world "có lý" dù không như ý

**Nếu bạn từng nghĩ:**
> "Mình không hề viết đoạn này…"

👉 **Bạn đã thành công.**

---

## V. IMPLEMENTATION: SEEDING BIAS

```php
class WorldSeedingInterface
{
    public function configureSeed(User $writer): WorldSeed
    {
        return new WorldSeed([
            // Allowed: Choose archetype focus
            'archetype_focus' => $writer->selectArchetypes(max: 3),
            
            // Allowed: Set myth threshold
            'myth_threshold' => $writer->selectThreshold(0.5, 0.9),
            
            // Allowed: Choose primitive bias
            'primitive_bias' => $writer->selectPrimitives(max: 5),
            
            // FORBIDDEN: Choose outcome
            // 'outcome' => ..., // This doesn't exist
            
            // FORBIDDEN: Set specific myths
            // 'myths' => ..., // This doesn't exist
        ]);
    }
}
```

---

## VI. IMPLEMENTATION: PRESSURE INJECTION

```php
class PressureInjectionSystem
{
    public function injectPressure(
        World $world,
        User $writer
    ): void {
        // Writer can only inject blind pressure
        $pressure = $writer->selectPressureType([
            'RESOURCE_SHOCK' => 'Sudden scarcity',
            'INFO_DISTORTION' => 'Communication breakdown',
            'DEMOGRAPHIC_SHIFT' => 'Population change'
        ]);
        
        // System translates to mechanics
        match($pressure) {
            'RESOURCE_SHOCK' => $this->applyResourceShock($world),
            'INFO_DISTORTION' => $this->applyInfoDistortion($world),
            'DEMOGRAPHIC_SHIFT' => $this->applyDemographicShift($world),
        };
        
        // Writer does NOT choose:
        // - Which faction wins
        // - Which myth survives
        // - Whether collapse happens
    }
    
    private function applyResourceShock(World $world): void
    {
        // Blind pressure - outcome unknown
        $world->economy->scarcity += 0.3;
        
        // System decides what happens next
    }
}
```

---

## VII. IMPLEMENTATION: SELECTION (POST-SAGA)

```php
class SagaSelectionInterface
{
    public function reviewSagas(User $writer): void
    {
        $sagas = SagaRun::completed()->get();
        
        foreach ($sagas as $saga) {
            $summary = $this->historian->summarize($saga);
            
            // Writer can only:
            // 1. Mark as "interesting"
            // 2. Mark as "canonical" (to be remembered)
            // 3. Mark as "discard"
            
            $writer->categorize($saga, $summary);
        }
        
        // Writer CANNOT:
        // - Edit saga content
        // - Change outcomes
        // - Rewrite history
    }
    
    public function publishCanonical(User $writer): void
    {
        $canonical = SagaRun::where('marked_as', 'canonical')->get();
        
        // Only canonical sagas become "official history"
        // But even canonical sagas are not editable
    }
}
```

---

## VIII. FORBIDDEN ACTIONS (ENGINE-ENFORCED)

```php
class HumanActionValidator
{
    private array $forbiddenActions = [
        'edit_myth_doctrine',
        'set_archetype_weight',
        'force_collapse',
        'choose_winner',
        'rewrite_event',
        'delete_scar',
        'modify_history'
    ];
    
    public function validate(HumanAction $action): bool
    {
        if (in_array($action->type, $this->forbiddenActions)) {
            throw new ForbiddenHumanActionException(
                "Action '{$action->type}' violates human-in-the-loop contract"
            );
        }
        
        return true;
    }
}
```

---

## IX. ALLOWED VS FORBIDDEN

| Action                     | Allowed | Reason                          |
| -------------------------- | ------- | ------------------------------- |
| Choose archetype focus     | ✅       | Setting conditions              |
| Set myth threshold         | ✅       | Setting difficulty              |
| Inject resource shock      | ✅       | Blind pressure                  |
| Select saga to publish     | ✅       | Curation                        |
| Edit myth                  | ❌       | Breaks emergent property        |
| Force specific outcome     | ❌       | Kills surprise                  |
| Change archetype weight    | ❌       | Violates drift mechanics        |
| Rewrite history            | ❌       | Destroys memory integrity       |

---

## X. CURATOR WORKFLOW

```
1. Pre-World: Choose conditions
   ↓
2. During Saga: Inject blind pressures (optional)
   ↓
3. Post-Saga: Read historian output
   ↓
4. Selection: Mark interesting/canonical/discard
   ↓
5. Publish: Share canonical sagas (read-only)
```

---

## SỰ THẬT CUỐI

> **The writer's job is not to write history.
> It is to choose which history deserves to be remembered.**

**Corollaries:**
1. Human sets conditions, not outcomes
2. Pressure is blind, not directed
3. Selection happens after, not before
4. Curation is not creation
5. Surprise is the goal, not control
