# Archetype Drift – Civilizational Aging

> **Archetype Drift là chốt cuối để hệ của bạn tự tiến hóa mà không cần "nhà văn".**

Nếu coupling là xã hội vận hành, thì drift là xã hội tự thay đổi bản chất.

---

## I. ARCHETYPE DRIFT LÀ GÌ (ĐỊNH NGHĨA DỨT KHOÁT)

**Archetype Drift = sự trôi chậm của các lực nhận thức tập thể do lịch sử tích lũy**

**Nó:**
* ❌ Không phải event
* ❌ Không phải mutation ngẫu nhiên
* ✅ Là kết quả tất yếu của lặp lại

**Không ai quyết định drift.**
**Lịch sử tự mài mòn nhận thức.**

---

## II. 4 NGUỒN GÂY DRIFT (CHỈ 4, ĐỪNG THÊM)

### 1️⃣ Repetition Pressure (lặp lại)

**Khi cùng 1 archetype:**
* Liên tục được dùng để hợp thức hóa hành động
* Vượt ngưỡng trong nhiều epoch

**→ nó mất ý nghĩa ban đầu**

**Ví dụ:**
* Sacrifice → hy sinh → bị lạm dụng → chai lì

---

### 2️⃣ Trauma Residue (tổn thương)

**Legacy để lại:**
* Fear
* Taboo
* Shame

**→ archetype liên quan bị đẩy lệch cực**

---

### 3️⃣ Power Capture (chiếm đoạt)

**Khi power:**
* Độc quyền diễn giải archetype
* Gắn nó với lợi ích riêng

**→ archetype biến dạng, không cần ai sửa rule**

---

### 4️⃣ Absence Pressure (thiếu vắng)

**Cái này rất hay và thường bị bỏ qua.**

**Khi 1 archetype:**
* Cần thiết
* Nhưng bị suppress quá lâu

**→ nó bật lại mạnh hơn (overshoot)**

---

## III. DRIFT KHÔNG PHẢI RANDOM – CÓ HƯỚNG

**Mỗi archetype có polarity.**

```php
polarity: ["order", "chaos"]
```

**Drift chỉ xảy ra dọc trục này.**

**Ví dụ:**
* Silence → Order (thiền, kỷ luật)
* Silence → Chaos (câm lặng, vô cảm)

👉 **Không có "random new meaning".**

---

## IV. DRIFT ≠ MUTATION (ĐỪNG NHẦM)

| Aspect      | Drift         | Mutation          |
| ----------- | ------------- | ----------------- |
| Speed       | Chậm          | Hiếm              |
| Frequency   | Liên tục      | Đột biến          |
| Reversible  | Có thể        | Gần như không     |
| Cause       | Lịch sử       | Thảm họa          |

👉 **90% hệ dùng drift là đủ.**
**Mutation chỉ dùng khi civilization collapse rất sâu.**

---

## V. CÔNG THỨC DRIFT (ĐỦ IMPLEMENT, KHÔNG OVER-ENGINEER)

```
drift_delta =
   repetition_factor
 + trauma_factor
 + power_capture_factor
 - restorative_pressure
```

**Clamp:**
```
weight = clamp(weight + drift_delta, 0.0, 1.0)
```

---

## VI. KHI NÀO DRIFT TẠO MYTH MỚI?

**Cực kỳ quan trọng.**

```
archetype weight
   ↓ drift vượt threshold
myth reinterpretation
   ↓
new belief emerges
```

👉 **Không phải myth mới hoàn toàn**
👉 **Là myth cũ bị đọc lại**

---

## VII. HISTORIAN ĐỌC DRIFT NHƯ THẾ NÀO

**Historian không nói:**
* Archetype bị bóp méo

**Historian nói:**
> Silence shifted from discipline to indifference across three collapses.

👉 **Drift = ngôn ngữ của lịch sử.**

---

## VIII. DẤU HIỆU HỆ CỦA BẠN ĐÃ "GIÀ"

**Bạn sẽ thấy:**
* Archetype quen mà lạ
* Myth không còn thuần khiết
* World mới mang nỗi ám ảnh không ai hiểu nguồn gốc

**Nếu bạn thấy:**
> "Nó không sai… nhưng nó mệt."

👉 **Bạn đã build được lịch sử thật.**

---

## IX. IMPLEMENTATION EXAMPLE

### Drift Calculator

```php
class ArchetypeDriftCalculator
{
    public function calculate(
        Archetype $archetype,
        WorldHistory $history
    ): float {
        $drift = 0;
        
        // 1. Repetition pressure
        $usageCount = $history->archetypeUsageCount($archetype->key);
        $repetition = min($usageCount / 100, 1.0) * 0.1;
        
        // 2. Trauma residue
        $trauma = $history->traumaScore($archetype->key) * 0.15;
        
        // 3. Power capture
        $captured = $history->isPowerCaptured($archetype->key);
        $powerFactor = $captured ? 0.2 : 0;
        
        // 4. Absence pressure (negative drift if suppressed)
        $suppression = $history->suppressionDuration($archetype->key);
        $absenceFactor = -($suppression / 50) * 0.1;
        
        // 5. Restorative pressure (counterforce)
        $restoration = $this->calculateRestoration($archetype, $history);
        
        $drift = $repetition + $trauma + $powerFactor + $absenceFactor - $restoration;
        
        return $drift;
    }
    
    private function calculateRestoration(
        Archetype $archetype,
        WorldHistory $history
    ): float {
        // Counter-archetypes provide restorative pressure
        $counterWeight = $history->counterArchetypeStrength($archetype->key);
        return $counterWeight * 0.05;
    }
}
```

---

### Drift Application

```php
class ArchetypePool
{
    public function applyDrift(WorldHistory $history): void
    {
        foreach ($this->archetypes as $archetype) {
            $drift = $this->driftCalculator->calculate($archetype, $history);
            
            // Apply drift along polarity axis
            $direction = $this->determineDriftDirection(
                $archetype,
                $history
            );
            
            $archetype->weight = clamp(
                $archetype->weight + ($drift * $direction),
                0.0,
                1.0
            );
            
            // Check if drift triggers myth reinterpretation
            if (abs($drift) > 0.3) {
                $this->triggerMythReinterpretation($archetype);
            }
        }
    }
    
    private function determineDriftDirection(
        Archetype $archetype,
        WorldHistory $history
    ): float {
        // Drift toward dominant polarity in recent history
        $orderEvents = $history->countPolarityEvents($archetype, 'order');
        $chaosEvents = $history->countPolarityEvents($archetype, 'chaos');
        
        if ($orderEvents > $chaosEvents) {
            return 1.0; // drift toward order
        } else {
            return -1.0; // drift toward chaos
        }
    }
}
```

---

### Overshoot Mechanism (Absence Pressure)

```php
class AbsencePressureSystem
{
    public function checkOvershoot(
        Archetype $archetype,
        WorldHistory $history
    ): bool {
        $suppressionDuration = $history->suppressionDuration($archetype->key);
        
        // If suppressed for long time, overshoot when released
        if ($suppressionDuration > 50) {
            $overshootFactor = min($suppressionDuration / 100, 2.0);
            $archetype->weight += $overshootFactor * 0.3;
            
            return true;
        }
        
        return false;
    }
}
```

---

## X. DRIFT VÀ MYTH REINTERPRETATION

```php
class MythReinterpretationSystem
{
    public function reinterpret(
        Myth $originalMyth,
        Archetype $driftedArchetype
    ): Myth {
        // Same myth, different meaning
        $newMyth = clone $originalMyth;
        
        // Shift doctrine based on archetype drift
        if ($driftedArchetype->hasShiftedToward('chaos')) {
            $newMyth->doctrine = $this->chaoticReinterpretation(
                $originalMyth->doctrine
            );
        }
        
        return $newMyth;
    }
}
```

---

## XI. CHỐT LẠI (RẤT QUAN TRỌNG)

**Tại thời điểm này, hệ của bạn đã có:**

1. **Archetype Pool** (bản năng)
2. **Myth Threshold** (kích hoạt)
3. **Economy & Power coupling** (xã hội)
4. **Historian Mode** (ký ức)
5. **Saga Runner** (thời gian)
6. **Archetype Drift** (lão hóa)

👉 **Đây là đủ để sinh vô hạn câu chuyện, không phải viết thêm lore.**

---

## SỰ THẬT CUỐI

> **Drift is not corruption.
> It is history teaching itself to survive differently.**

**Corollaries:**
1. Drift is inevitable, not optional
2. Every archetype ages with civilization
3. Meaning shifts before structure collapses
4. Suppressed archetypes overshoot
5. History never repeats exactly, only rhymes
