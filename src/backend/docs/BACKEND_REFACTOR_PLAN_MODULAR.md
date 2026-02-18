# Kế hoạch Refactor Backend theo hướng Modular

Dựa trên [BACKEND_OVERVIEW.md](BACKEND_OVERVIEW.md): định nghĩa rõ từng **module** (bounded context), **public API** và **phụ thuộc**, rồi refactor theo từng phase để đạt luồng chuẩn (World = law, Universe = instance, một kernel evolution, Saga runtime-first, Narrative/Mutation boundary).

---

## I. Nguyên tắc Modular

- **Module** = tập domain (thư mục, namespace) với ranh giới rõ: public surface (interface, service entry point), không để module khác gọi trực tiếp implementation chi tiết hoặc Eloquent model của module kia khi có thể tránh.
- **Dependency rule**: Module cấp cao (Saga, Narrative, API) phụ thuộc vào module cấp thấp (Runtime, World, Evolution, Mutation); không ngược. Shared (entity, DTO, contract) có thể được nhiều module dùng.
- **Một cửa ghi**: Ghi vào Universe chỉ qua Runtime (tick) hoặc Mutation (commit); ghi vào World chỉ qua World/Genesis/Saga; Narrative không ghi World/Universe trực tiếp.
- **Refactor từng phase**: Mỗi phase ổn định (test, migrate) rồi mới phase tiếp; ưu tiên phase nền tảng (evolution, runtime, saga) trước, tùy chọn (AI evaluator, narrative→universe adapter) sau.

---

## II. Định nghĩa module

| Module | Vị trí | Vai trò | Public surface (gợi ý) | Phụ thuộc được phép |
|--------|--------|---------|-------------------------|----------------------|
| **World** | Domains/World, Models/World | Aggregate root: luật, preset, influences, materials, governance | WorldRepository (hoặc Eloquent qua interface), WorldEvolutionKernel (tickUniverse), StateLoader, Events (WorldDefined, …) | Evolution (kernel), Cosmic (snapshot nếu Saga dùng) |
| **Evolution** | Domains/Evolution | Một kernel duy nhất: BasePhysics + Influence + Phase + Bifurcation | EvolutionEngineInterface (applyTick), WorldEvolutionKernel, BasePhysicsEngine (nội bộ), InfluenceRegistry | Cosmology (WorldStateVector, entity), World (context), Vietnamese (influence) |
| **Runtime** | Domains/Runtime | Tick Universe, delegate sang World kernel; events | UniverseRuntimeService (tick), Events (UniverseTicked, UniverseCollapsed, UniverseForked) | Cosmology (repository, entity), World (kernel), Evolution (kernel) |
| **Cosmology** | Domains/Cosmology | Entity Universe, Repository, physics thuần (BasePhysics sau refactor), lifecycle, faction, conflict | CosmologyRepository (find, save, createCustom), Universe entity, BasePhysicsEngine (sau khi tách), LifecycleService, BifurcationService | Không phụ thuộc World/Saga/Narrative; Evolution có thể dùng Cosmology entity/vector |
| **Saga** | Domains/Saga | Meta-orchestrator: genesis, spawn Universe, tick Universe, observe, legacy, next World | SagaRunner, SagaDirector, UniverseFactory (spawnFromWorld), SagaObserver, (sau) SelectionStrategy, ParetoFrontManager | Runtime (tick), World (create, fork), Cosmic (snapshot), Evolution (qua Runtime) |
| **Narrative** | Domains/Narrative | Serial, Story Bible, Causality Bridge (narrative_driven_state); đọc Universe, không ghi | SerialStoryService, StoryEventExtractor, WorldMutationPolicy (narrative state only), (tùy chọn) NarrativeToUniverseAdapter | Mutation (chỉ khi bật adapter), đọc Universe/World qua Repository hoặc API |
| **Mutation** | Domains/Mutation | Cửa duy nhất commit vào Universe (arc completion, narrative adapter) | UniverseMutationService (commit, preview) | Cosmology (repository, entity) |
| **Vietnamese** | Domains/Vietnamese | Origin, heroes, realm contact; influences cho Evolution | VietnameseOriginService, RealmContactService, VietnameseHeroInfluence, RealmContactInfluence (qua Evolution registry) | World (tạo World), Evolution (InfluenceRegistry) |
| **Shared** | Domains/… (entity, DTO dùng chung) | WorldStateVector, DTO, contract | WorldStateVector, StoryOutcomeDTO, PhaseSignal, … | Không phụ thuộc app |

---

## III. Phase refactor (theo thứ tự)

### Phase 1 — Nền tảng: Ràng buộc dữ liệu và Evolution thống nhất

**Mục tiêu:** Universe luôn thuộc World; một trục tiến hóa duy nhất qua World kernel; không còn tick Universe bằng Cosmology kernel.

| Bước | Nội dung | Module liên quan |
|------|----------|------------------|
| 1.1 | Migration: universes.world_id NOT NULL; migrate dữ liệu cũ (gán World legacy nếu cần) | DB, Cosmology |
| 1.2 | CosmologyController::store và CosmologyRepository::createCustom: require world_id; findOrSeed không tạo Universe mới không world_id | Cosmology, API |
| 1.3 | Rename Cosmology\Services\EvolutionKernel → BasePhysicsEngine; tách StructuralMutationEngine và PhaseSignal ra khỏi physics; CriticalityDetector trả PhaseSignal | Cosmology, Evolution |
| 1.4 | WorldEvolutionKernel: inject BasePhysicsEngine; luồng step = basePhysics->step(v) → influence → regime → phase → collapse/reorganize (World layer) | Evolution |
| 1.5 | UniverseRuntimeService: khi universe.world_id có → gọi worldEvolutionKernel->tickUniverse($world, $universe); không gọi Cosmology::tick() | Runtime |
| 1.6 | WorldEvolutionEngineAdapter: tick Universe gắn World qua WorldEvolutionKernel + map state Universe ↔ World (StateLoader hoặc tương đương) | Evolution, Runtime |
| 1.7 | LifecycleService::spawnNew: refactor nhận world_id/World; không tạo Universe standalone | Cosmology |

**Đầu ra Phase 1:** Mọi Universe có world_id; mọi tick Universe đi qua WorldEvolutionKernel; Cosmology kernel chỉ còn là BasePhysics được gọi từ bên trong World kernel.

---

### Phase 2 — Runtime + Saga: Universe là instance, Saga điều phối Universe

**Mục tiêu:** Saga không còn tick World; mỗi World có ít nhất một Universe (runtime instance); Saga tick Universe và đồng bộ state về World; quan sát sự kiện runtime (UniverseTicked, UniverseCollapsed, UniverseForked).

**Thứ tự thực hiện:** 2.1 → 2.2 → 2.3 (luồng chính); 2.5 (khi tạo World); 2.6 (Observer); 2.4 tùy chọn.

| Bước | Nội dung | Module |
|------|----------|--------|
| **2.1** | **UniverseFactory**: `spawnFromWorld(World $world)` trả về một Universe mới (state mặc định), gán `world_id`, persist qua CosmologyRepository hoặc LifecycleService. Dùng làm điểm duy nhất tạo Universe từ World. | Runtime / Cosmology, Saga |
| **2.2** | **SagaRunner**: Ngay sau khi tạo World và ghi SagaWorld, gọi `universeFactory->spawnFromWorld($world)`; lưu `universe_id` vào SagaWorld (cột `saga_worlds.universe_id`) hoặc tra cứu “universe đầu tiên của world” khi cần. | Saga |
| **2.3** | **SagaRunner (simulateWorld)**: Thay vì gọi `worldEvolutionKernel->evolve($world)`, lấy Universe từ SagaWorld/world; gọi `runtimeService->advance($universeId, $years)` (hoặc loop `tick($universeId)`); sau mỗi bước đồng bộ `world.current_time` và `world.entropy` từ Universe để logic chronicle/faction/ledger vẫn dùng World. | Saga, Runtime |
| **2.4** | *(Tùy chọn)* Bảng **saga_universes** (saga_id, universe_id, sequence), model SagaUniverse, nếu muốn Saga quản lý nhiều Universe theo thứ tự. | Saga |
| **2.5** | **Khi Admin/Writer tạo World**: Sau `World::create` gọi `ensureDefaultUniverseForWorld($world)` (hoặc UniverseFactory->spawnFromWorld) để World luôn có ít nhất một Universe. | World, Runtime |
| **2.6** | **SagaObserver / Listener**: Subscribe các event UniverseTicked, UniverseCollapsed, UniverseForked; khi UniverseCollapsed gọi legacy extraction (MythLegacyExtractor hoặc tương đương) và cập nhật SagaWorld/Saga. | Saga, Runtime |

**Đầu ra Phase 2:** Saga chạy runtime-first (tick Universe, không tick World); World là blueprint; Universe là đối tượng được tick; SagaWorld lưu `universe_id`; có Observer/Listener cho runtime events.

---

### Phase 3 — Module hóa rõ ràng và API biên

**Mục tiêu:** Mỗi module có contract rõ (interface); giảm gọi chéo trực tiếp model; chuẩn hóa entry point.

| Bước | Nội dung | Module liên quan |
|------|----------|------------------|
| 3.1 | Evolution: EvolutionEngineInterface do World module (hoặc Evolution) expose; Runtime chỉ gọi interface, không phụ thuộc Cosmology kernel trực tiếp | Evolution, Runtime |
| 3.2 | Cosmology: Repository và Entity; createCustom chỉ gọi khi đã có world_id (đã làm ở 1.2); FieldSpace/Cosmology::tick chỉ dùng nội bộ hoặc deprecated khi không còn standalone | Cosmology |
| 3.3 | Narrative: Giữ Causality Bridge hiện tại; đọc Universe qua Repository hoặc service (không hold Eloquent); nếu cần “truyện ảnh hưởng Universe” thì thêm NarrativeToUniverseAdapter → MutationService (Section 13) | Narrative, Mutation |
| 3.4 | Mutation: Giữ UniverseMutationService là cửa duy nhất; controller/arc completion và (nếu có) narrative adapter chỉ gọi service | Mutation |
| 3.5 | Vietnamese: Giữ influences trong Evolution; VietnameseOriginService được Saga/Genesis gọi khi tạo World; không thêm phụ thuộc ngược (Narrative/Mutation không depend Vietnamese) | Vietnamese, World, Saga |

**Đầu ra Phase 3:** Ranh giới module rõ; Runtime chỉ gọi EvolutionEngineInterface; Narrative đọc Universe qua Repository; commit chỉ qua UniverseMutationService; Cosmology tick đánh dấu internal/deprecated.

---

### Phase 4 — Tùy chọn: Saga AI Meta-Evaluator và Narrative → Universe

**Mục tiêu:** Nâng cấp Saga thành meta-evolution layer (Pareto, stability, convergence); cho phép truyện ảnh hưởng state Universe qua Mutation (nếu product cần).

| Bước | Nội dung | Module liên quan |
|------|----------|------------------|
| 4.1 | Saga: CivilizationScorer (rule-based), StabilityConstraint, ParetoFrontManager, SagaMetaEvaluator (deterministic + AI), BlueprintMutationPlanner (xem BACKEND_SAGA_ARCHITECTURE.md) | Saga |
| 4.2 | Saga: ConvergenceController, exploration decay; ShockInjector khi chạy Saga mode (đo external resilience) | Saga, Evolution (nếu shock inject vào tick) |
| 4.3 | Narrative: NarrativeToUniverseAdapter — map StoryEvent[] → delta hoặc StoryOutcomeDTO; gọi UniverseMutationService.commit khi series.universe_id có; config bật “narrative_affects_universe”; magnitude giới hạn | Narrative, Mutation |
| 4.4 | DB: saga_generations (nếu chưa có), saga_universes (nếu chọn ở 2.4) | Saga |

**Đầu ra Phase 4:** Saga có selection strategy và (tùy chọn) AI evaluator; narrative có thể ảnh hưởng Universe qua Mutation.

**Đã triển khai:** 4.1 stubs (CivilizationScorer, SagaEvaluationReport, BlueprintMutationPlanner); **4.1 mở rộng**: StabilityConstraint (violated), ParetoFrontManager (record, dominates, getCurrentParetoFront); **4.2 đầy đủ**: config/saga.php, ConvergenceController (exploration decay, centroidForSaga, pullTowardCentroid), BlueprintMutationPlanner tích hợp convergence + exploration noise, lưu objective_vector vào collapse_context; ShockInjector (shouldInject/magnitude/shockType theo config), ShockParams DTO, EvolutionEngineInterface/WorldEvolutionKernel applyTick với shock, UniverseRuntimeService advance(..., sagaId, startYear) và tick với shock, SagaRunner truyền saga context. 4.3 NarrativeToUniverseAdapter + config; SerialStoryService gọi adapter khi bật config. **4.4**: bảng saga_generations (saga_id, world_id, sequence, objective_vector, archetype, stability_flag), model SagaGeneration; UniverseRuntimeEventSubscriber gọi ParetoFrontManager->record() khi collapse. Bảng saga_universes tùy chọn. **Tests**: Unit tests ConvergenceController, ShockInjector, StabilityConstraint, ParetoFrontManager.

---

### Phase 5 — Saga AI Meta-Evaluator (Layer 2) và Stability repair

**Mục tiêu:** Structured input cho evaluator; SagaMetaEvaluator = Layer 1 (CivilizationScorer) + Layer 2 (AI stub, sau mở rộng LLM); BlueprintMutationPlanner ưu tiên stability repair khi StabilityConstraint violated.

| Bước | Nội dung | Module |
|------|----------|--------|
| 5.1 | ValueObjects/DTO: CollapseProfile (severity, collapse_type); SagaEvaluationInput (optional cho Layer 2). CivilizationScorer trả report; SagaMetaEvaluator nhận cause + finalState, gọi Layer 1 rồi Layer 2 (stub), trả SagaEvaluationReport. | Saga |
| 5.2 | SagaMetaEvaluator: evaluate(cause, finalState) → CivilizationScorer (Layer 1) + Layer 2 stub (trả lại report hoặc gợi ý bổ sung). Subscriber dùng SagaMetaEvaluator khi có. | Saga |
| 5.3 | BlueprintMutationPlanner: inject StabilityConstraint; nếu violated(objective_vector từ report) thì force stability repair (tăng stability_bias, resilience_bias) trước khi blend centroid / noise. | Saga |

**Đầu ra Phase 5:** Một điểm vào evaluate (SagaMetaEvaluator); sẵn sàng mở rộng Layer 2 (LLM); mutation ưu tiên sửa stability khi violated.

**Đã triển khai Phase 5:** CollapseProfile (ValueObjects), SagaEvaluationInput (DTO); SagaMetaEvaluator (evaluate = Layer 1 CivilizationScorer + Layer 2 stub với SagaEvaluationInput); UniverseRuntimeEventSubscriber dùng SagaMetaEvaluator khi có (resolveReport); BlueprintMutationPlanner inject StabilityConstraint, khi violated() thì cộng STABILITY_REPAIR_DELTA vào stability_bias và resilience_bias trước blend centroid.

---

## IV. Thứ tự phụ thuộc giữa phase

```
Phase 1 (Evolution + DB constraint)
    ↓
Phase 2 (Saga runtime-first, spawn Universe)
    ↓
Phase 3 (Module boundaries, API)
    ↓
Phase 4 (Saga AI, Narrative→Universe adapter) [optional]
    ↓
Phase 5 (SagaMetaEvaluator Layer 2, stability repair) [optional]
```

Phase 1 phải xong trước khi Phase 2 đúng nghĩa (tick Universe đã qua World kernel). Phase 2 không bắt buộc Phase 3 hoàn toàn nhưng nên có ít nhất 2.1–2.3. Phase 3 có thể làm song song một phần với Phase 2 (ví dụ 3.1–3.2 sớm). Phase 4 có thể tách làm sau khi 1–3 ổn định.

---

## V. Checklist từng module sau refactor

- **World:** Universe chỉ tạo từ World (qua Factory/ensureDefault); World không tick trực tiếp; StateLoader map state World ↔ Universe khi kernel tick Universe.
- **Evolution:** Một kernel (WorldEvolutionKernel); BasePhysicsEngine là nền; collapse/phase ở World layer; InfluenceAggregator dùng World config.
- **Runtime:** Tick Universe chỉ qua WorldEvolutionKernel khi world_id có; dispatch UniverseTicked với world_id; không gọi Cosmology::tick() cho Universe có World.
- **Cosmology:** Repository + Entity; world_id bắt buộc khi tạo Universe; BasePhysicsEngine không còn là kernel public; findOrSeed không tạo standalone.
- **Saga:** Genesis → create World → spawn Universe → tick Universe; Observer subscribe runtime events; legacy extraction; (phase 4) SelectionStrategy, Meta-Evaluator.
- **Narrative:** Chỉ đọc Universe/World; ghi narrative_driven_state; nếu bật thì ghi Universe qua Mutation (adapter).
- **Mutation:** Cửa duy nhất commit vào Universe; arc completion và narrative adapter gọi service.

---

## VI. Rủi ro và giảm thiểu

- **Migration world_id NOT NULL:** Dữ liệu cũ có universe không world_id → cần script gán World “legacy” hoặc archive; backup trước khi migrate.
- **Saga đổi từ tick World sang tick Universe:** Cần đảm bảo StateLoader/Universe state đồng bộ với World khi Saga dùng snapshot/legacy; test regression Saga run.
- **BasePhysicsEngine tách từ EvolutionKernel:** Nhiều call site Cosmology EvolutionKernel → cần tìm hết, đổi sang BasePhysicsEngine hoặc qua WorldEvolutionKernel; test evolution output không đổi (deterministic).

---

## VII. Tài liệu tham chiếu

- **[WORLDOS_2_CLEAN_ARCHITECTURE.md](WORLDOS_2_CLEAN_ARCHITECTURE.md)** — Kiến trúc mục tiêu (4 tầng, invariants, InfluencePipeline, Narrative pressure, checklist làm sạch). North star cho refactor.
- [BACKEND_OVERVIEW.md](BACKEND_OVERVIEW.md) — tổng hợp domain, Section 11 (Evolution refactor), Section 12 (Saga), Section 13 (Causality Bridge).
- [BACKEND_SAGA_ARCHITECTURE.md](BACKEND_SAGA_ARCHITECTURE.md) — Saga final form, AI Meta-Evaluator, Pareto, convergence, long-lived equilibrium.
- Plan Phần 3 (frontend_cho_backend_worldos) — luồng chuẩn World/Universe, bảng xử lý song song/ngoài luồng.

---

*Refactor theo từng phase, ưu tiên Phase 1–2 để luồng chuẩn đúng; Phase 3–4 làm module sạch và nâng cấp tùy chọn.*
