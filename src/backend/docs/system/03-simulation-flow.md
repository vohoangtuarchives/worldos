# 03 — Luồng simulation (tick và snapshot)

## 3.1 Entry point duy nhất cho “chạy thời gian”

- **UniverseRuntimeService::advance(string $universeId, int $ticks, ?int $sagaId = null, ?int $startYear = 0)**

  - Nếu `ticks <= 0`: trả về Universe hiện tại, không tick.
  - Nếu `ticks > 0`: gọi `tick()` liên tiếp `$ticks` lần; mỗi lần có thể inject shock (khi sagaId/startYear được set và ShockInjector cho phép).

- **UniverseRuntimeService::tick(string $universeId, ?int $sagaId, ?int $currentYear)**

  - Load Universe (CosmologyRepository::findOrSeed).
  - (Tùy chọn) ShockInjector: nếu `shouldInject(sagaId, currentYear)` → tạo ShockParams cho kernel.
  - **Nếu Universe có world_id**:
    - Kiểm tra policy: World không được HALTED.
    - **evolutionEngine->applyTick($universe, $shockParams)** → thực chất gọi **WorldEvolutionKernel::tickUniverse(World, Universe, ShockParams)**.
    - CosmologyRepository::save(universe, world_id).
    - **UniverseSnapshotRepository::save(universe, [])** — ghi snapshot sau mỗi tick.
    - Dispatch UniverseTicked.
  - **Nếu Universe không có world_id (legacy)**:
    - Cosmology::tick() (không shock).
    - Save universe + snapshot.

## 3.2 Kernel: tick Universe

- **WorldEvolutionEngineAdapter** implement **EvolutionEngineInterface::applyTick($runtimeInstance, $shockParams)**.
  - Nếu runtimeInstance là CosmologyUniverse và có world_id → gọi **WorldEvolutionKernel::tickUniverse(World, Universe, ShockParams)**.

- **WorldEvolutionKernel::tickUniverse(World, CosmologyUniverse, ?ShockParams)**:
  - Hiện tại yêu cầu **BasePhysicsEngine**: evolve(universe->getState(), preset, regime); áp shock nếu có; cập nhật state lên Universe; kernel không ghi DB — caller (UniverseRuntimeService) ghi save + snapshot.

## 3.3 Snapshot-first

- Sau **mỗi** tick (trong UniverseRuntimeService::tick), gọi **UniverseSnapshotRepository::save($universe, $metrics)**.
- Snapshot dùng `universe_id` + `tick` (= age) làm khóa (updateOrCreate); lưu state_vector, entropy, stability_index, metrics.
- Rollback / fork / clone / AI metrics đều đọc từ universe_snapshots (getAtTick, getLatest).

## 3.4 Không dùng cho flow v3

- **SagaRunner.simulateWorld** và **RunSagaSimulationJob** (cosmic_snapshots, tick World) — deprecated cho flow mới.
- Tick **World** trực tiếp (WorldEvolutionKernel::evolve(World, years)) — dùng cho legacy hoặc công cụ nội bộ, không phải luồng Saga v3.
- Flow v3: **chỉ** advance **Universe** qua UniverseRuntimeService → Kernel::tickUniverse → snapshot.

## 3.5 Sơ đồ luồng (v3)

```
SagaService::runBatch(saga, N)
  → với mỗi saga_world có universe_id:
      UniverseRuntimeService::advance(universe_id, N)
        → for i = 0..N-1:
            tick(universe_id, sagaId, startYear + i)
              → evolutionEngine->applyTick(universe, shock?)
              → cosmologyRepository->save(universe)
              → universeSnapshotRepository->save(universe)
              → dispatch UniverseTicked
```
