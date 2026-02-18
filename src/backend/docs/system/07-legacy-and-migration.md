# 07 — Legacy và migration

## 7.1 Deprecated (không dùng cho flow v3)

- **SagaRunner.simulateWorld**
  - Tick World / cosmic_snapshots, không dùng Universe + universe_snapshots. Thay bằng SagaService::runBatch + UniverseRuntimeService::advance.

- **RunSagaSimulationJob**
  - Chạy simulation qua SagaRunner; ghi cosmic_snapshots. Flow mới: Genesis v3 + runBatch / runBatchWithEvaluation; dữ liệu lịch sử ở universe_snapshots.

- **CosmicSnapshotRepositoryInterface / cosmic_snapshots**
  - Đánh dấu deprecated trong code (WorldOS v3: dùng UniverseSnapshotRepository + universe_snapshots). Không dùng cho advance/evaluate/fork mới.

- **Tick World trực tiếp**
  - WorldEvolutionKernel::evolve(World, years) tick trên World (current_time, entropy trên World). Dùng cho legacy hoặc công cụ nội bộ; luồng Saga v3 chỉ tick Universe qua UniverseRuntimeService → Kernel::tickUniverse.

## 7.2 Nguồn sự thật (v3)

- **Runtime state**: Universe (universes) + universe_snapshots. Không dựa vào cosmic_snapshots cho flow advance/evaluate/fork.
- **Orchestration**: SagaService (spawnUniverse, runBatch, runBatchWithEvaluation, fork, genesisV3). Không dựa vào SagaRunner cho simulation mới.
- **Entry point tick**: UniverseRuntimeService::advance → tick → EvolutionEngineInterface::applyTick → WorldEvolutionKernel::tickUniverse (khi có world_id).

## 7.3 Migration path

- Tài liệu cũ ngoài `docs/system/` có thể sai lệch; tham chiếu duy nhất cho kiến trúc v3 là bộ tài liệu trong `docs/system/`.
- Code mới: tạo World → spawnUniverse → ghi snapshot; advance qua UniverseRuntimeService; evaluate qua MetricsExtractor + Evaluator + DecisionEngine; fork từ UniverseSnapshotRepository. Không tạo job/runner dựa trên cosmic_snapshots hoặc SagaRunner.simulateWorld.
