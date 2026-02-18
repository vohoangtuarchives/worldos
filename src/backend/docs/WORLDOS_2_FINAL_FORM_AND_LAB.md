# WorldOS 2.0 — Final Form & Evolution Lab

Tài liệu ghi nhận kiến trúc **final form** và hướng **long-running evolutionary lab** (continuous engine, AI toggle, Docker + VPS). Bổ sung cho [WORLDOS_2_CLEAN_ARCHITECTURE.md](WORLDOS_2_CLEAN_ARCHITECTURE.md) và [BACKEND_OVERVIEW.md](BACKEND_OVERVIEW.md).

---

## I. Ba boundary tuyệt đối (không được vi phạm)

| # | Boundary | Mô tả |
|---|----------|--------|
| 1 | **UniverseRuntimeService** | Không bao giờ gọi BasePhysicsEngine trực tiếp; chỉ gọi WorldEvolutionKernel (hoặc adapter). |
| 2 | **Saga** | Không bao giờ tick World; chỉ tick Universe qua RuntimeService. |
| 3 | **Narrative** | Không bao giờ ghi state_vector / Universe trực tiếp; chỉ qua MutationService hoặc PressureSignal. |

Vi phạm một trong ba → Clean Architecture sụp.

---

## II. Clean boundary summary

| Thành phần | Được làm | Không được làm |
|------------|----------|----------------|
| World | Định nghĩa luật (preset, influences, config) | Giữ runtime state |
| Universe | Giữ state_vector, age, snapshot, chronicle | Định nghĩa luật |
| WorldEvolutionKernel | Evolve (physics + influence + phase) | Quyết định saga/selection |
| Saga | Chọn blueprint, spawn Universe, observe collapse, mutate blueprint | Evolve trực tiếp, tick World |
| Narrative | Đọc Universe, cập nhật narrative_state | Mutate Universe trực tiếp |
| UniverseMutationService | Cửa commit duy nhất vào Universe | Bị bypass bởi Narrative/Arc |

---

## III. StoryEngine — vị trí chính danh

StoryEngine (`app/StoryEngine/`) hiện là **engine song song** không nằm trong Context Map (Saga / Universe runtime / Narrative serial).

**Hai lựa chọn:**

- **A) AI sandbox (tách hoàn toàn)**  
  Chuyển thành `App/Experimental/StoryEngine` hoặc tương đương. Không thuộc Domain. Dùng cho test, AI experiment; không tham gia luồng Saga/Universe.

- **B) Hợp nhất**  
  Coi như narrative pre-simulation hoặc civilization sandbox phục vụ huấn luyện Saga meta-evaluator. Khi đó phải định nghĩa rõ contract (input/output) và chỉ gọi từ Saga/Narrative qua interface.

Nếu không chọn rõ → ranh giới DDD bị phá.

---

## IV. Snapshot taxonomy (chuẩn hóa)

| Loại snapshot | Chỉ nên thuộc | Ghi chú |
|---------------|----------------|---------|
| **Runtime state** | Universe | state_vector, age, runtime snapshot theo universe_id |
| **Blueprint state** | World | Lịch sử config/preset theo world (nếu cần) |
| **Meta state** | Saga | Pareto front, civilization candidates, saga ledger |

Tránh trùng vai trò: world_snapshots_v2, cosmic_snapshots, chronicles, civilization_snapshots cần phân vai (Universe vs World vs Saga) rõ ràng; xem [WORLDOS_2_WORLD_RUNTIME_AUDIT.md](WORLDOS_2_WORLD_RUNTIME_AUDIT.md).

---

## V. Saga Meta Layer — Pareto & AI Historian

### 5.1 Vai trò

- **SagaMetaEvaluator**: Đánh giá collapse → CivilizationFitnessVector (đa chiều, không gộp 1 số). Deterministic metrics + optional AI enrichment.
- **ParetoFrontManager**: Chọn lọc theo Pareto dominance; không collapse vector thành weighted sum.
- **BlueprintMutationPlanner**: Sinh thế hệ tiếp theo từ front + collapse signature; không để AI sinh mutation trực tiếp.

### 5.2 AI Historian (mode A — chỉ chấm điểm + phân loại)

- AI **chỉ**: tính chỉ số khó lượng hóa, phân loại archetype, viết collapseNarrative ngắn (visualization).
- AI **không**: đề xuất mutation, thay đổi fitness core, ghi memory, ảnh hưởng selection trực tiếp.
- **Input chuẩn hóa**: ChronicleSummaryDTO, CollapseSignatureDTO (không đưa raw state).
- **Output**: JSON schema cố định (mythDepth, civilizationalIdentityStrength, innovationSustainability, archetypeClassification, collapseNarrative); **không** có mutationBiasSuggestion.
- **Trọng số**: final metric = (1 − w) × deterministic + w × ai (ví dụ w = 0.2). Deterministic luôn dominant.
- **Failure**: JSON lỗi / archetype ngoài taxonomy / value ngoài range → fallback 100% deterministic; không crash Saga.

### 5.3 Long-running lab & AI toggle

- Evolution chạy **liên tục** (background engine); AI có thể **bật/tắt** khi cần.
- Evolution **không phụ thuộc AI**: deterministic evaluation luôn chạy; frontier tồn tại kể cả khi AI tắt.
- **EvolutionConfig** (DB): ai_enabled, ai_weight, ai_sampling_rate, ai_model_version. Worker check config trước khi gọi LLM.
- **Frontier 2 lớp**: Provisional (deterministic only) và Enriched (khi AI report đã merge). Recompute incremental khi AI về.

---

## VI. Triển khai: Docker + một VPS (multi-node ready)

### 6.1 Vai trò container

| Role | Chạy | Ghi chú |
|------|------|--------|
| **app-control** | API, EvolutionOrchestrator, FrontierManager, Config | Không chạy simulation nặng |
| **simulation-worker** | `queue:work --queue=simulation` | Scale ngang (replicas) |
| **ai-worker** | `queue:work --queue=ai` | Concurrency thấp (rate limit LLM) |
| **Redis** | Queue | Đủ cho giai đoạn đầu |
| **DB** | PostgreSQL | Shared state; index civilizations(generation_id), fitness_state, archetype |

### 6.2 Tách queue bắt buộc

- **simulation**: Spawn → RunUntilCollapse → Deterministic Evaluate → Persist.
- **ai**: Read Snapshot → Summarize → LLM → Persist AI Report (nếu ai_enabled).
- **frontier**: Recompute Pareto (có thể chạy trên control hoặc queue riêng).

Không dùng một queue cho tất cả; không để AI call block simulation queue.

### 6.3 Điều phối

- **evolution:tick** (artisan): Cron mỗi 5–10s; kiểm tra queue depth và running jobs; dispatch simulation nếu cần.
- **Pareto update**: Serialize (Redis lock hoặc DB transaction); chỉ control app hoặc job dedicated được phép cập nhật frontier.
- **Resource**: Giới hạn CPU/RAM cho simulation-worker và ai-worker để tránh starvation lẫn nhau.

### 6.4 Mở rộng sau

- Scale simulation replicas; tách Redis/DB sang managed service; cùng Docker image.
- Thiết kế sẵn event-driven, stateless workers → có thể chuyển sang multi-node / K8s sau.

---

## VII. Đã refactor (theo hướng tài liệu này)

- **config/evolution.php**: ai_enabled, ai_weight, ai_sampling_rate, ai_model_version, ai_queue, simulation_queue.
- **config/saga.php**: **strict_runtime** (mặc định false). Khi true: Saga không tick World; nếu không có Universe thì throw; nếu Universe tick fail thì throw thay vì fallback World.
- **SagaRunner**: Khi strict_runtime bật, không vào nhánh worldEvolutionKernel/evolutionPipeline (tick World); bắt buộc có Universe.
- **RunSagaSimulationJob**: Gán queue qua `config('evolution.simulation_queue', 'simulation')`.
- **config/queue.php**: Ghi chú dùng queue simulation / ai cho evolution lab.
- **app/StoryEngine/README.md**: Vị trí legacy/experimental; hai hướng A (sandbox) / B (hợp nhất); link WORLDOS_2_FINAL_FORM_AND_LAB.

---

## VIII. Tài liệu liên quan

- [WORLDOS_2_CLEAN_ARCHITECTURE.md](WORLDOS_2_CLEAN_ARCHITECTURE.md) — North star, 4 tầng, InfluencePipeline, Narrative pressure
- [WORLDOS_2_WORLD_RUNTIME_AUDIT.md](WORLDOS_2_WORLD_RUNTIME_AUDIT.md) — Runtime trên World, snapshot/chronicle universe_id
- [BACKEND_SAGA_ARCHITECTURE.md](BACKEND_SAGA_ARCHITECTURE.md) — Saga meta, Pareto, BlueprintMutationPlanner, ShockInjector
- [BACKEND_OVERVIEW.md](BACKEND_OVERVIEW.md) — Tổng hợp domains, bảng DB, API, Jobs

*Cập nhật lần cuối: refactor strict_runtime, evolution config, StoryEngine README, queue simulation/ai.*
