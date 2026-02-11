# Archetype Mutation – When History Breaks

> **Mutation chỉ nên xảy ra khi lịch sử "gãy xương".**

Nếu drift là con người mệt mỏi vì lịch sử, thì mutation là lịch sử không còn đủ từ ngữ để diễn giải thực tại.

---

## I. MUTATION KHÁC DRIFT Ở ĐÂU?

**Drift:**
* Con người mệt mỏi vì lịch sử
* Thay đổi trọng số

**Mutation:**
* Lịch sử không còn đủ từ ngữ để diễn giải thực tại
* Thay đổi cấu trúc nhận thức

👉 **Mutation = thay đổi cấu trúc nhận thức, không phải trọng số.**

---

## II. KHI NÀO MUTATION ĐƯỢC PHÉP XẢY RA?

**Chỉ 3 trigger. Không thêm.**

### 1️⃣ Civilization Collapse cực sâu

**Conditions:**
* legitimacy = 0
* economy vỡ
* myth mất hiệu lực hoàn toàn

**→ archetype backing không còn ai tin.**

---

### 2️⃣ Myth Paradox (rất hiếm)

**Conditions:**
* 2 myth dựa trên cùng archetype
* Dẫn đến 2 hệ đạo đức đối nghịch
* Conflict không giải được bằng power

**→ xã hội không còn cách hiểu thống nhất.**

---

### 3️⃣ Repeated Failure Across Sagas

**Conditions:**
* Cùng archetype
* Cùng pattern collapse
* Lặp lại qua nhiều saga

**→ đây không còn là lỗi world**
**→ là lỗi nhận thức loài người**

---

## III. MUTATION DIỄN RA NHƯ THẾ NÀO (CỰC KỲ QUAN TRỌNG)

❌ **Không tạo archetype mới tùy tiện**
❌ **Không xóa archetype cũ**

✅ **Mutation = fork archetype**

```
Before:
  "sacrifice"

After:
  "sacrifice_redemptive"
  "sacrifice_extractive"
```

**Characteristics:**
* Cùng gốc
* Khác hướng đạo đức
* Mang theo lịch sử chia tách

---

## IV. RULE SẮT CHO MUTATION

1. **Mutation không đảo ngược**
2. **Mutation ghi dấu vết vĩnh viễn**
3. **Mutation lan chậm, không bùng nổ**

**Nếu bạn vi phạm 1 rule → hệ mất ký ức tiến hóa.**

---

## V. HISTORIAN ĐỌC MUTATION THẾ NÀO?

**Không bao giờ nói:**
* Archetype mới xuất hiện

**Mà nói:**
> Sacrifice split into two irreconcilable meanings after the Third Silence Collapse.

👉 **mutation = sẹo lịch sử, không phải feature.**

---

## VI. IMPLEMENTATION EXAMPLE

### Mutation Trigger Detection

```php
class ArchetypeMutationDetector
{
    public function checkMutationTrigger(
        Archetype $archetype,
        WorldState $world,
        SagaHistory $history
    ): ?MutationTrigger {
        // Trigger 1: Extreme collapse
        if ($this->isExtremCollapse($world, $archetype)) {
            return new MutationTrigger(
                type: 'EXTREME_COLLAPSE',
                archetype: $archetype,
                severity: $world->collapseSeverity
            );
        }
        
        // Trigger 2: Myth paradox
        if ($this->hasMythParadox($world, $archetype)) {
            return new MutationTrigger(
                type: 'MYTH_PARADOX',
                archetype: $archetype,
                conflictingMyths: $this->getParadoxMyths($world, $archetype)
            );
        }
        
        // Trigger 3: Repeated failure across sagas
        if ($this->hasRepeatedFailure($history, $archetype)) {
            return new MutationTrigger(
                type: 'REPEATED_FAILURE',
                archetype: $archetype,
                failureCount: $history->failureCount($archetype)
            );
        }
        
        return null;
    }
    
    private function isExtremCollapse(
        WorldState $world,
        Archetype $archetype
    ): bool {
        return $world->legitimacy <= 0
            && $world->economy->isCollapsed()
            && $world->myths->usingArchetype($archetype)->allIneffective();
    }
    
    private function hasMythParadox(
        WorldState $world,
        Archetype $archetype
    ): bool {
        $myths = $world->myths->usingArchetype($archetype);
        
        // Check if myths lead to contradictory morals
        $morals = $myths->map(fn($m) => $m->moralFramework);
        
        return $morals->hasIrreconcilableConflict();
    }
    
    private function hasRepeatedFailure(
        SagaHistory $history,
        Archetype $archetype
    ): bool {
        $failures = $history->collapses()
            ->where('primary_archetype', $archetype->key)
            ->count();
        
        return $failures >= 3; // Threshold
    }
}
```

---

### Mutation Execution (Fork)

```php
class ArchetypeMutationExecutor
{
    public function fork(
        Archetype $original,
        MutationTrigger $trigger
    ): array {
        // Never delete original
        $original->markAsMutated();
        
        // Create two divergent variants
        $variants = $this->createVariants($original, $trigger);
        
        // Record mutation history
        $this->recordMutation($original, $variants, $trigger);
        
        return $variants;
    }
    
    private function createVariants(
        Archetype $original,
        MutationTrigger $trigger
    ): array {
        // Determine polarity split based on trigger
        $split = $this->determineSplit($original, $trigger);
        
        return [
            new Archetype(
                key: "{$original->key}_{$split['positive']}",
                parent: $original->key,
                polarity: [$split['positive']],
                weight: $original->weight * 0.6,
                mutation_mark: $trigger->toArray()
            ),
            new Archetype(
                key: "{$original->key}_{$split['negative']}",
                parent: $original->key,
                polarity: [$split['negative']],
                weight: $original->weight * 0.4,
                mutation_mark: $trigger->toArray()
            )
        ];
    }
}
```

---

### Database Schema

```sql
CREATE TABLE archetype_mutations (
    id UUID PRIMARY KEY,
    parent_archetype VARCHAR,
    variant_1 VARCHAR,
    variant_2 VARCHAR,
    trigger_type VARCHAR, -- EXTREME_COLLAPSE | MYTH_PARADOX | REPEATED_FAILURE
    trigger_context JSON,
    created_at TIMESTAMP,
    irreversible BOOLEAN DEFAULT true
);
```

---

## VII. MUTATION PROPAGATION (SLOW SPREAD)

```php
class MutationPropagation
{
    public function propagate(
        ArchetypeMutation $mutation,
        SagaRunner $saga
    ): void {
        // Mutation spreads slowly across worlds
        foreach ($saga->remainingWorlds() as $world) {
            $adoptionChance = $this->calculateAdoption(
                $world,
                $mutation
            );
            
            if (random() < $adoptionChance) {
                $world->archetype_pool->add($mutation->variants);
                $world->archetype_pool->deprecate($mutation->parent);
            }
        }
    }
    
    private function calculateAdoption(
        World $world,
        ArchetypeMutation $mutation
    ): float {
        // Slow adoption, not instant
        $baseRate = 0.1; // 10% per world
        
        // Higher if world has similar trauma
        if ($world->hasSimilarTrauma($mutation->trigger_context)) {
            $baseRate += 0.2;
        }
        
        return min($baseRate, 0.5); // Max 50%
    }
}
```

---

## SỰ THẬT CUỐI

> **Mutation is not evolution.
> It is history admitting it cannot continue with the words it has.**

**Corollaries:**
1. Mutation is rare, not frequent
2. Mutation is irreversible
3. Mutation creates permanent scars
4. Mutation spreads slowly
5. Each mutation carries the memory of why it happened
