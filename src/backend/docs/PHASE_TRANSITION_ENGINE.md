# Phase Transition Engine — Evolution Engine Upgrade

## Tổng quan

Hệ thống đã được nâng cấp từ **static metric engine** sang **evolution engine** với 3 cơ chế:

1. **Pressure Accumulation Field** — contradiction tích lũy thành áp suất
2. **Criticality Detector** — tính toán khi nào đạt điểm chuyển pha
3. **Collapse Function + Non-linear Innovation Burst** — tự sụp, tự chữa lành, tự tiến hóa

---

## 1. Pressure Accumulation Field

**File:** `app/Domains/Cosmology/Mathematics/PressureAccumulationField.php`

- **contradiction_index**: derived từ inequality×(1-legitimacy), trauma, entropy
- **pressure()**: tích lũy theo thời gian khi không có release
- **releaseRate()**: innovation có thể dissipate pressure (reorganization)

Trong ngôn ngữ "đại đạo":
- `inequality × (1 - legitimacy)` = đạo tự mâu thuẫn
- `trauma` = vết thương tích tụ
- `entropy` = đạo bị nhiễu

---

## 2. Criticality Detector

**File:** `app/Domains/Cosmology/Mathematics/CriticalityDetector.php`

**Phases:**
- `STABLE` — bình thường
- `REORGANIZATION_POSSIBLE` — entropy cao, innovation có thể spike → cấu trúc mới
- `CRITICAL` — điều kiện collapse đủ nhưng chưa fracture
- `COLLAPSE_IMMINENT` — trigger structural fracture

**Điều kiện collapse (đạo rạn nứt):**
```
contradiction_index > 0.70
AND innovation < 0.15
AND resource_flow (resource_stock) < 0.05
```

---

## 3. Collapse Function

**Trong:** `EvolutionKernel::applyFeedbackLoops()`

Khi `should_collapse`:
- Order × 0.3, Legitimacy × 0.2, Cohesion × 0.4
- Trauma + 0.2, Entropy + 0.1
- Resource_stock × 0.5

**LifecycleService::checkDeath()** — thêm cause `STRUCTURAL_FRACTURE` khi criticality detector báo collapse.

---

## 4. Reorganization Law

** entropy → innovation spike → cấu trúc mới**

Khi `can_reorganize` (entropy > 0.65, innovation > 0.05, resource > 0.02):
- Innovation nhận boost từ `InnovationBurst::reorganizationBoost()`
- Cho phép hệ tự chữa lành thay vì sụp

---

## 5. Non-linear Innovation Burst

**File:** `app/Domains/Cosmology/Mathematics/InnovationBurst.php`

- **deltaInnovation()**: base delta + burst tiềm năng khi entropy > 0.65
- Burst xác suất ~15% mỗi tick khi điều kiện đủ
- **reorganizationBoost()**: tăng innovation khi can_reorganize

---

## Luồng tích hợp

```
Universe.tick()
  → EvolutionKernel.evolve(state)
      → calculateDifferentials() — dùng InnovationBurst cho innovation delta
      → applyFeedbackLoops()
          → CriticalityDetector.assess()
          → Nếu should_collapse: structural fracture
          → Nếu can_reorganize: innovation boost
          → Catabolic collapse, Resource scarcity, Revolution (như cũ)
  → LifecycleService.checkDeath()
      → CriticalityDetector.assess() → STRUCTURAL_FRACTURE
      → HEAT_DEATH, TIME_CRUNCH, STAGNATION (như cũ)
```

---

## Cấu hình (có thể tune)

| Component | Parameter | Default |
|-----------|-----------|---------|
| CriticalityDetector | contradictionThreshold | 0.70 |
| CriticalityDetector | innovationMinThreshold | 0.15 |
| CriticalityDetector | resourceFlowThreshold | 0.05 |
| InnovationBurst | entropyTrigger | 0.65 |
| InnovationBurst | burstAmplitude | 0.25 |
| InnovationBurst | burstProbability | 0.15 |
