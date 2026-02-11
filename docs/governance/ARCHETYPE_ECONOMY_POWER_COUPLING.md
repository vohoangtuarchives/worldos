# Archetype-Economy-Power Coupling

> **Đây là chỗ mà hệ của bạn chuyển từ "mô phỏng logic" sang "mô phỏng xã hội".**

Nếu làm đúng Archetype ↔ Economy ↔ Power coupling, world của bạn sẽ tự sinh chính trị, bất công và sụp đổ mà bạn không cần viết.

---

## I. VẤN ĐỀ HIỆN TẠI (RẤT THƯỜNG GẶP)

**Hiện giờ hệ có:**
* Economy logic ✔
* Power / faction / conflict ✔
* Archetype Pool ✔

**Nhưng:**
* Economy đang là số
* Power đang là cơ chế
* Archetype đang là ý niệm

👉 **Nếu không coupling → world vận hành nhưng không có lý do tồn tại.**

---

## II. NGUYÊN LÝ COUPLING CỐT LÕI

**Archetype không tạo ra hành động.**
**Nó tạo ra cách xã hội chấp nhận hoặc biện minh cho hành động.**

* **Economy & Power làm chuyện xấu.**
* **Archetype giải thích vì sao chuyện đó "đúng".**

---

## III. KIẾN TRÚC COUPLING (ĐÚNG CHỖ, KHÔNG PHÁ CORE)

```
Archetype
   ↓ biases
Perception Filter
   ↓ legitimizes
Economy & Power Actions
   ↓ creates
Inequality / Scarcity / Violence
   ↓ feeds back
Archetype Weight Drift
```

👉 **Archetype không điều khiển economy**
👉 **Nó bẻ cong cách economy được hiểu**

---

## IV. COUPLING VỚI ECONOMY (QUAN TRỌNG NHẤT)

### 1. Archetype ảnh hưởng cách phân phối

**Ví dụ archetype: Hierarchy**

* Thuế lũy tiến → bị xem là bất công
* Tích lũy → được xem là trật tự tự nhiên

```php
if (archetype('hierarchy')->weight > 0.7) {
    economy->allowWealthConcentration();
}
```

👉 **Không phải rule cứng**
👉 **Là xác suất chấp nhận**

---

### 2. Archetype biến scarcity thành đức hạnh

**Archetype Sacrifice:**
* Thiếu thốn ≠ thất bại
* Thiếu thốn = cao quý

**Hệ quả:**
* Economy tệ vẫn ổn định
* Dân chịu đựng lâu → collapse sâu hơn

---

## V. COUPLING VỚI POWER

### 1. Power cần hợp thức hóa

**Không quyền lực nào tồn tại lâu nếu:**
* Không có myth
* Không có archetype chống lưng

**Archetype Unity:**
* Đàn áp = bảo vệ
* Khác biệt = phản bội

👉 **Conflict không đến từ power,**
👉 **mà từ archetype bị kéo quá ngưỡng.**

---

### 2. Khi power mất archetype

**Đây là trigger collapse rất đẹp:**

* Power vẫn mạnh
* Economy vẫn chạy
* Nhưng archetype backing bị decay

**→ legitimacy = 0**
**→ collapse gần như tức thì**

---

## VI. CÔNG THỨC COUPLING (ĐỦ ĐỂ IMPLEMENT)

```
legitimacy =
  f(archetype_weight, myth_intensity)
  - economic_inequality
  - trauma_memory
```

**Không cần phức tạp hơn.**

---

## VII. VAI TRÒ CỦA HISTORIAN Ở ĐÂY

**Historian không nói:**
* Quyền lực xấu

**Historian chỉ nói:**
> Every society that normalized sacrifice tolerated extreme inequality.

👉 **Pattern, không phán xét.**

---

## VIII. DẤU HIỆU BẠN COUPLING ĐÚNG

**Bạn sẽ thấy:**
* Economy tệ nhưng xã hội chưa sụp
* Power yếu nhưng tồn tại lâu
* Collapse xảy ra bất ngờ, không theo logic thuần

**Nếu bạn ngạc nhiên → hệ đúng.**

---

## IX. IMPLEMENTATION EXAMPLE

```php
class PerceptionFilter
{
    public function evaluateLegitimacy(
        WorldState $world,
        ArchetypePool $archetypes
    ): float {
        $legitimacy = 0;
        
        // Archetype backing
        foreach ($archetypes->active() as $archetype) {
            if ($archetype->supportsCurrentPower()) {
                $legitimacy += $archetype->weight * $archetype->mythIntensity;
            }
        }
        
        // Economic inequality penalty
        $legitimacy -= $world->economy->giniCoefficient * 0.5;
        
        // Trauma memory penalty
        $legitimacy -= $world->traumaScore * 0.3;
        
        return max(0, min(1, $legitimacy));
    }
}
```

---

## X. ARCHETYPE BIASED ACTIONS

```php
class EconomicPolicy
{
    public function allowWealthConcentration(ArchetypePool $pool): bool
    {
        $hierarchy = $pool->get('hierarchy');
        $unity = $pool->get('unity');
        
        // Higher hierarchy weight → tolerate inequality
        $tolerance = $hierarchy->weight * 0.7 + $unity->weight * 0.3;
        
        return $tolerance > 0.6;
    }
    
    public function tolerateScarcity(ArchetypePool $pool): bool
    {
        $sacrifice = $pool->get('sacrifice');
        
        // Sacrifice archetype makes scarcity virtuous
        return $sacrifice->weight > 0.5;
    }
}
```

---

## XI. POWER LEGITIMACY DECAY

```php
class PowerSystem
{
    public function checkLegitimacy(
        Faction $ruling,
        ArchetypePool $pool
    ): float {
        $supportingArchetypes = $pool->filter(
            fn($a) => $a->supportsFactonType($ruling->type)
        );
        
        $legitimacy = $supportingArchetypes->sumWeights();
        
        // If legitimacy drops below threshold → collapse imminent
        if ($legitimacy < 0.3) {
            $this->triggerLegitimacyCrisis();
        }
        
        return $legitimacy;
    }
}
```

---

## XII. FEEDBACK LOOP

```php
class ArchetypeWeightDrift
{
    public function applyEconomicFeedback(
        Archetype $archetype,
        EconomyState $economy
    ): float {
        $drift = 0;
        
        // If archetype justifies inequality
        if ($archetype->key === 'hierarchy') {
            // High inequality strengthens hierarchy archetype
            $drift += $economy->inequality * 0.1;
        }
        
        // If archetype normalizes sacrifice
        if ($archetype->key === 'sacrifice') {
            // Scarcity strengthens sacrifice archetype
            $drift += $economy->scarcity * 0.15;
        }
        
        return $drift;
    }
}
```

---

## SỰ THẬT CỐT LÕI

> **Archetype không tạo ra bất công.
> Nó làm cho bất công có vẻ hợp lý.**

**Corollaries:**
1. Economy creates conditions
2. Archetype creates acceptance
3. Power exploits both
4. Collapse happens when archetype can't justify anymore
5. Pattern repeats with different archetypes
