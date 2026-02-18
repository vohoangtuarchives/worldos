# 04 — Saga orchestrator (SagaService, Genesis v3)

## 4.1 SagaService

- Vị trí: `App\Domains\Saga\Services\SagaService`
- Vai trò: Saga chỉ điều phối Universe (spawn, advance, evaluate, fork). Không tick World; không gọi SagaRunner.simulateWorld.
- Phụ thuộc: CosmologyRepository, UniverseSnapshotRepository, UniverseRuntimeService, MetricsExtractor, UniverseEvaluatorInterface, DecisionEngine.

## 4.2 Phương thức chính

**spawnUniverse(World, ?parentUniverseId): Universe** — Tạo Universe từ World, ghi snapshot tick 0.

**runBatch(Saga, ticksPerUniverse): void** — Với mỗi saga_world có universe_id: advance(universe_id, ticksPerUniverse). Chỉ dùng UniverseRuntimeService.

**evaluate(Universe): string** — Stub trả về 'continue'. Đầy đủ dùng MetricsExtractor + Evaluator + DecisionEngine (xem 05).

**fork(Universe, fromTick): Universe** — Clone từ snapshot tại fromTick; Universe mới có parent_universe_id; ghi snapshot đầu.

**genesisV3(Saga, ticksPerUniverse): void** — Tạo một World (preset từ GenesisPresetService), spawnUniverse, tạo SagaWorld (universe_id, sequence=1), cập nhật Saga current_universe_id và status RUNNING, runBatch. Không dispatch RunSagaSimulationJob.

**runBatchWithEvaluation(Saga, ticksPerUniverse, evaluateEveryTicks): void** — runBatch rồi với mỗi universe: fromLatestSnapshot → evaluate → decisionEngine->execute (fork/archive/continue hoặc apply mutation).

## 4.3 Genesis từ API

Writer Genesis: GET presets, POST tạo Saga rồi gọi genesisV3 (hoặc endpoint start). Presets: GenesisPresetService::allByCategory(), find(preset_key).

## 4.4 Saga không có

Saga không có clock, entropy, physics. Mọi số liệu từ Universe và universe_snapshots. Không dùng SagaRunner.simulateWorld cho flow mới.
