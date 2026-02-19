# 04 — Saga orchestrator (SagaService, Genesis v3)

## 4.1 SagaService

- Vị trí: `App\Domains\Saga\Services\SagaService`
- Vai trò: Saga chỉ điều phối Universe (spawn, advance, evaluate, fork). Không tick World; không gọi SagaRunner.simulateWorld.
- Phụ thuộc: CosmologyRepository, UniverseSnapshotRepository, UniverseRuntimeService, MetricsExtractor, UniverseEvaluatorInterface, DecisionEngine.

## 4.2 Phương thức chính

**spawnUniverseFromPreset(World, presetKey): Universe** — Tạo Universe instance từ World, áp dụng cấu hình từ Preset, ghi snapshot tick 0.

**createSagaFromActive(Universe): Saga** — Khởi tạo Saga quản lý Universe đã có.

**runBatch(Saga, ticksPerUniverse): void** — Advance universe hiện tại của Saga.

**evaluate(Universe): result** — Đánh giá tiềm năng Universe (AI/Rule-based).

**fork(Universe, fromTick): Universe** — Clone từ snapshot.

## 4.3 Genesis từ API (Split Flow)

Quy trình 3 bước tách biệt:

1. **POST /api/writer/genesis/world**: Tạo World Container (Physic Laws, Genre). Không dùng Preset.
2. **POST /api/writer/genesis/universe**: Spawn Universe Instance vào World, sử dụng **Preset** (Scenario, History Seed).
3. **POST /api/writer/sagas/create-from-active**: Tạo Saga (Story Orchestrator) để quản lý/advance Universe đó.

## 4.4 Saga không có

Saga không có clock, entropy, physics. Mọi số liệu từ Universe và universe_snapshots. Không dùng SagaRunner.simulateWorld cho flow mới.
