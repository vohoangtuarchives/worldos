# Saga: Kiến trúc đúng và Refactor (Runtime-first + AI Meta-Evaluator)

Tài liệu mô tả định nghĩa Saga, luồng Runtime-first, kiến trúc final form, và thiết kế AI Meta-Evaluator (multi-objective Pareto, stability constraint, convergence, long-lived equilibrium, dual stability). Refactor có thể làm theo từng bước.

---

## I. Định nghĩa Saga

- **Saga không phải** World, Universe, hay Narrative.
- **Saga là**: Bộ điều phối tiến hóa nhiều World/Universe theo một ý đồ meta — meta-process điều khiển chuỗi các Universe instances sinh ra từ các World blueprint khác nhau, theo mục tiêu lịch sử/triết học/narrative cao hơn.
- **Nói ngắn**: World = luật; Universe = timeline; **Saga = chuỗi timeline có ý nghĩa**.
- **Bản chất**: Universe tiến hóa trong World; World tiến hóa qua Saga. Saga = evolutionary selection layer (Darwin engine của các World).

Nếu bỏ Saga: vẫn có World → Universe → tick, nhưng không có meta progression, entropy ledger xuyên world, myth carry-over, legacy extraction. Saga tồn tại để: tạo World A → spawn Universe A1 → tick → collapse/legacy → extract legacy → tạo World B từ legacy → spawn Universe B1 → lặp lại.

---

## II. Saga đúng: năm việc (Runtime-first)

Saga **không bao giờ tick World**. Saga điều phối **Universe** và subscribe RuntimeContext.

1. **Genesis orchestration**: `$world = genesisService->create(...);` `$universe = universeFactory->spawnFromWorld($world);`
2. **Evolution orchestration**: `$runtimeService->tick($universe, years);`
3. **Observation**: Subscribe UniverseTicked, UniverseCollapsed, UniverseForked.
4. **Legacy extraction**: Khi collapse: `$legacy = mythExtractor->extract($universe);`
5. **World mutation for next iteration**: `$newWorld = worldForkService->createFromLegacy($legacy);` hoặc BlueprintMutationPlanner từ SagaEvaluationReport.

---

## III. Kiến trúc Saga (final form)

```
Saga
 ├── SagaWorld (world blueprint sequence: saga_id, world_id, sequence)
 ├── SagaUniverse (runtime instances: saga_id, universe_id, sequence)
 ├── SagaEntropyLedger
 ├── SagaObserver (subscribe runtime events)
 └── SagaSelectionStrategy (Pareto + Convergence + Stability)
```

**Flow**: World (blueprint) → spawn Universe → tick → Events → SagaObserver → Legacy extraction → SelectionStrategy → New World blueprint.

**DB gợi ý**: Bảng `saga_universes` (saga_id, universe_id, sequence) để Saga quản lý đúng runtime instances; SagaWorld vẫn nối saga ↔ world (blueprint).

---

## IV. AI Meta-Evaluator — vai trò và domain design

**Vai trò**: (1) Evaluate Universe outcome; (2) Score civilization trajectory; (3) Recommend next World mutation. **Không** can thiệp physics; chỉ can thiệp **blueprint evolution**.

**Luồng**: UniverseCollapsed → SagaObserver → **SagaMetaEvaluator (AI)** → SagaEvaluationReport → **BlueprintMutationPlanner** → Next World.

**Cấu trúc domain (gợi ý)**:

- `app/Domains/Saga/Services/`: SagaMetaEvaluator, CivilizationScorer, BlueprintMutationPlanner, ParetoFrontManager, ConvergenceController, StabilityConstraint.
- `app/Domains/Saga/DTO/`: SagaEvaluationReport, CivilizationScore, SagaEvaluationInput.
- `app/Domains/Saga/ValueObjects/`: MythSignature, CollapseProfile, CivilizationObjectiveVector.

**Input cho AI — không gửi raw vector**. Gửi structured abstraction:

- **SagaEvaluationInput**: CollapseProfile, CivilizationScore (trajectory), MythSignature, phaseHistory, entropyIntegral, yearsSurvived.
- **CollapseProfile**: severity, dominant_contradiction, collapse_type (entropy overload, inequality revolt, legitimacy erosion, …).
- **CivilizationScore**: stability_score, innovation_density, resilience_index, cultural_cohesion_peak (và sau này: oscillation_amplitude, adaptation_rate, internal_stability, external_resilience).
- **MythSignature**: hero_presence_score, spiritual_depth, legacy_strength.

**Hai tầng evaluator**:

- **Layer 1 — Deterministic heuristic**: Nhanh, rẻ, dự đoán được. Ví dụ: nếu collapse_type === 'inequality_revolt' thì mutation redistribution_bias += 0.2.
- **Layer 2 — AI pattern reasoning**: LLM nhận diện archetype, đề xuất mutation direction (governance_bias, inequality_dampener, innovation_regulator). Prompt **structured** (JSON in/out), không narrative.

**BlueprintMutationPlanner**: Áp dụng report nhưng **clamp** (ví dụ ±0.2 mỗi dimension), anti-oscillation (giới hạn flip), exploration factor (5–10% noise). Không tin AI 100%.

**AI không quyết định collapse**: Physics layer vẫn deterministic. AI chỉ quyết định blueprint mutation.

---

## V. Multi-Objective Pareto + Stability constraint

**Lựa chọn**: Multi-objective Pareto (E) + Stability là hard constraint ưu tiên cao (D).

**CivilizationObjectiveVector** (normalize [0,1]): survivalYears, innovationPeak, mythDepth, resilience, inequalityBalance, entropyControl (và sau: oscillationAmplitude, adaptationRate, internalStability, externalResilience).

**StabilityConstraint**: Ví dụ `resilience < 0.45 || entropyControl < 0.4` → violated. Nếu violated: không vào Pareto front; mutation phải sửa stability trước.

**ParetoFrontManager**: Lưu generation history (saga_generations: saga_id, world_id, objective_vector json, archetype, stability_flag). Dominance: A dominates B nếu A không tệ hơn mọi objective và tốt hơn ít nhất một. Duy trì current_pareto_front[].

**AI trong multi-objective**: AI không tính Pareto. AI nhận diện archetype, phân tích trade-off, đề xuất mutation direction để cải thiện vùng bị dominate.

---

## VI. Convergence về civilization optimum (A)

**Mục tiêu**: Thu hẹp Pareto frontier về vùng ổn định cao nhất theo stability-priority; selection ưu tiên stable-dominant cluster.

**ConvergenceController**:

- Tính **centroid** của vùng tốt nhất (ví dụ resilience > 0.6, entropyControl > 0.6).
- Khoảng cách world mới tới centroid (Euclidean hoặc cosine).
- Mutation hướng **gradient về centroid** (clamp delta, ví dụ ±0.15).

**Exploration decay schedule**: `exploration_rate = max(0.02, 0.1 * exp(-generation / 20))` để tránh local optimum; không bao giờ exploration = 0.

**Stability**: Trước khi xét converge, nếu StabilityConstraint violated → force stability repair, skip convergence logic.

---

## VII. Long-lived equilibrium >1000 năm (C)

**Mục tiêu**: Controlled dynamic equilibrium, không phải stability cao nhất. Civilization tồn tại >1000 năm với dao động nhỏ có kiểm soát.

**Bổ sung chỉ số**: oscillation_amplitude, structural_adaptation_rate, innovationStability.

**Stability constraint (phiên bản equilibrium)**: resilience ∈ [0.55, 0.8], entropyControl ∈ [0.5, 0.75], oscillationAmplitude < 0.25. (Quá cao → cứng, không thích nghi.)

**Equilibrium basin**: Không ép về một điểm; ConvergenceController đo distance tới **vùng** (target region). Ưu tiên survival > 1000 năm vào elite pool.

**Selection rules**: Survival < 500 → ưu tiên survival repair; penalize hyper-innovation (innovationPeak > 0.9); reward entropy_variance thấp + không collapse.

**Mutation**: Ít aggressive, max ±0.1; nếu oscillationAmplitude > 0.25 thì feedback_damping += 0.07; nếu adaptationRate thấp thì governance_elasticity += 0.06.

---

## VIII. Dual stability: Internal + External (C)

**Hai loại ổn định**:

- **Internal**: inequality↔legitimacy, entropy↔order, cohesion↔trauma, innovation↔institutional adaptation. Chỉ số: internal_stability_index (resilience, entropy_variance, inequality_volatility).
- **External**: military buffer, resource margin, cohesion under shock, regime elasticity. Chỉ số: external_resilience_index (shock_recovery_time, military_buffer, resource_margin).

**Constraint**: internalStability > 0.6, externalResilience > 0.6. **Target region**: internal ∈ [0.65, 0.85], external ∈ [0.65, 0.85], oscillationAmplitude < 0.25, entropyControl ∈ [0.55, 0.75].

**ShockSimulationLayer (ShockInjector)**: Chỉ hoạt động khi chạy dưới Saga mode. Mỗi 50–100 năm (hoặc theo config): random military shock, resource collapse, ideology invasion, external tech pressure. Đo shock_recovery_time (years until cohesion + legitimacy > 0.7); nếu recovery > 30 years → penalize externalResilience; nếu collapse triggered → hard fail.

**Mutation planner (dual-stability aware)**: Internal cao / external thấp → + military buffer, + resource redundancy, + governance crisis mode. External cao / internal thấp → + inequality dampener, + innovation regulator, + trauma healing. Không tăng cả hai quá mạnh cùng lúc (entropy surge).

**AI**: Phát hiện delayed shock fragility, hidden feedback loops, overfitting vào internal harmony nhưng thiếu defensive posture. Prompt: counterfactual / stress scenarios.

---

## IX. Refactor từng bước (không downtime)

1. **Runtime-first**: Saga sau createWorld gọi universeFactory->spawnFromWorld($world); tick Universe thay vì tick World; SagaObserver subscribe UniverseTicked, UniverseCollapsed, UniverseForked.
2. **SagaUniverse**: Thêm bảng saga_universes (saga_id, universe_id, sequence) và model; Saga quản lý chuỗi runtime instances.
3. **Legacy extraction**: Khi UniverseCollapsed, mythExtractor->extract($universe) → legacy; truyền vào bước tạo World tiếp theo (WorldForkService hoặc planner).
4. **CivilizationScorer (rule-based)**: Deterministic: stabilityIndex, innovationDensity, resilienceIndex, cohesionPeak từ Universe/state; output CivilizationScore. Không cần AI ngay.
5. **StabilityConstraint + ParetoFrontManager**: Lưu saga_generations; dominance; StabilityConstraint->violated() gating.
6. **SagaMetaEvaluator + BlueprintMutationPlanner**: Input structured (CollapseProfile, CivilizationScore, MythSignature); Layer 1 heuristic + Layer 2 AI (async job); planner clamp ±0.2 (sau giảm ±0.1 cho long-lived).
7. **ConvergenceController**: Centroid vùng tốt nhất; mutation hướng centroid; exploration decay.
8. **ShockInjector**: Bật khi Saga mode; inject shock theo chu kỳ; đo external_resilience.
9. **Frontend (sau)**: Pareto scatter, stability heatmap, mutation delta timeline, archetype evolution tree.

---

## X. Tóm tắt nguyên tắc

- **World** = luật (blueprint).
- **Universe** = thực thi luật (timeline).
- **Saga** = tiến hóa luật (meta-orchestrator, runtime-first, selection strategy, AI meta-evaluator).
- **Narrative** = quan sát.
- **Mutation** = commit thay đổi vào instance (qua MutationService).

Saga phải có: Memory (ledger, generations), Evaluation (scorer, meta-evaluator), Selection (Pareto, convergence, stability), Mutation of blueprint (BlueprintMutationPlanner). Nếu không, Saga chỉ là batch job hoặc grouping — mất ý nghĩa.

---

*Tham chiếu: BACKEND_OVERVIEW.md Section 11 (Evolution refactor), Section 12 (Saga refactor).*
