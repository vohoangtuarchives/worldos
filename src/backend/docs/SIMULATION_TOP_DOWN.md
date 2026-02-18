# Mối quan hệ top-down của hệ thống simulation

Tài liệu mô tả luồng điều khiển và quan hệ từ tầng cao xuống tầng thấp trong hệ thống mô phỏng WorldOS.

---

## 1. Tầng cao nhất: Bounded Context (theo CONTEXT_MAP)

```
WorldContext (Core)  →  RuntimeContext  →  SagaContext
```

- **WorldContext**: Nguồn chân lý cho evolution, materials, governance, health. **World** = aggregate root.
- **RuntimeContext**: Thể hiện runtime (Universe = instance của World), tick, chronicle. Phụ thuộc World.
- **SagaContext**: Narrative, branch scoring, canonize. Đọc từ Runtime (events: UniverseTicked, UniverseForked, UniverseCollapsed).

Quan hệ: **World** là upstream; **Universe** thuộc World; **Saga** điều phối chuỗi World (và có thể tick Universe khi kiến trúc dùng Runtime path).

---

## 2. Hệ thống simulation — hai luồng chính

Trong code hiện tại tồn tại **hai đường đi** cho simulation, không hoàn toàn thống nhất:

### Luồng A: Saga → World (WorldEvolutionPipeline, Cosmic Snapshot)

| Tầng | Thành phần | Vai trò |
|------|------------|--------|
| 1 | **Genesis (API)** | Tạo Saga (name, preset, world_count). Dispatch `RunSagaSimulationJob`. |
| 2 | **Saga** | Entity: world_count, current_world_index, status. Chuỗi nhiều World (sequence). |
| 3 | **RunSagaSimulationJob** | Job queue: gọi `SagaRunner::runSync($saga)`. |
| 4 | **SagaRunner** | Điều phối: tạo World (createNextWorld) → simulateWorld(sagaWorld) cho từng World trong saga. **Không tick Universe**; làm việc trực tiếp với **World** + **WorldEvolutionPipeline**. |
| 5 | **World** | Model Eloquent: name, preset, current_time, entropy, config. 1 World có nhiều CosmicSnapshot (theo year). |
| 6 | **simulateWorld()** | Vòng lặp: loadOrInitializeState(World) → WorldSnapshot; mỗi bước gọi `evolutionPipeline->step()` → saveSnapshot(world_id, nextSnapshot); cập nhật World.current_time, World.entropy; Faction/Conflict/Material/Chronicle; kiểm tra collapse → onWorldComplete hoặc tiếp tục. |
| 7 | **WorldEvolutionPipeline** (Cosmic) | Nhận WorldSnapshot hiện tại, trả về WorldSnapshot tiếp theo (năm sau). Physics + civilization evolution. |
| 8 | **CosmicSnapshotRepository** | Persist snapshot theo (world_id, year). Lưu CosmicSnapshot (entropy, stability, energy, env_*, civ_*, …). |

**Đặc điểm**: Saga tạo và “chạy” từng **World**; state được lưu trong **cosmic_snapshots** (world_id, year). **Universe** (UniverseModel) không bắt buộc trong luồng này.

---

### Luồng B: Universe (Runtime) → WorldEvolutionKernel

| Tầng | Thành phần | Vai trò |
|------|------------|--------|
| 1 | **API / Job** | Gọi advance universe (ví dụ `POST /api/cosmology/universe/{id}/advance` với ticks). |
| 2 | **UniverseRuntimeService** | advance(universeId, ticks): với mỗi tick gọi tick(universeId). Nếu Universe có world_id: delegate sang **EvolutionEngineInterface** (WorldEvolutionEngineAdapter). Nếu không: legacy Cosmology::tick(). |
| 3 | **UniverseModel** | Bảng universes: id, world_id, state_vector, age, parameters. **Runtime instance** của một World. |
| 4 | **WorldEvolutionEngineAdapter** | implement EvolutionEngineInterface. applyTick(universe) → **WorldEvolutionKernel::tickUniverse(World, Universe)**. |
| 5 | **World** | Cung cấp preset, law; kernel đọc config từ World. |
| 6 | **WorldEvolutionKernel** | tickUniverse(World, Universe): load state từ Universe, BasePhysicsEngine/VectorDynamicsEngine evolve, có thể StructuralMutationEngine (collapse/reorganize), lưu state lại vào Universe (qua CosmologyRepository). |
| 7 | **CosmologyRepository** | Persist Universe (state_vector, age) và có thể gắn world_id. |

**Đặc điểm**: **Universe** là đối tượng được tick; **World** là chủ sở hữu luật/preset. State sống ở **Universe** (state_vector, age); CosmicSnapshot (world_id) có thể được ghi từ tầng kernel/snapshot tùy tích hợp.

---

## 3. Sơ đồ quan hệ top-down (tóm tắt)

```
                    [Genesis API]
                         │
                         ▼
                   ┌──────────┐
                   │   Saga   │  world_count, current_world_index
                   └────┬─────┘
                        │
         RunSagaSimulationJob
                        │
                        ▼
                 ┌──────────────┐
                 │  SagaRunner  │  createNextWorld → simulateWorld
                 └──────┬───────┘
                        │
        ┌───────────────┼───────────────┐
        ▼               ▼               ▼
   ┌─────────┐   ┌──────────────────┐   ┌─────────────────────────┐
   │  World  │   │ WorldEvolution   │   │ CosmicSnapshotRepository│
   │ (model) │   │   Pipeline       │   │ (world_id, year)        │
   └────┬────┘   └────────┬─────────┘   └─────────────────────────┘
        │                 │
        │     loadOrInitializeState / step() / saveSnapshot
        │                 │
        ▼                 ▼
   current_time, entropy, cosmic_snapshots (DB)


   --- Luồng B (Runtime / Universe) ---

   [API advance universe]
            │
            ▼
   ┌─────────────────────┐     world_id      ┌─────────┐
   │ UniverseRuntime     │ ─────────────────► │  World  │
   │ Service             │                    └────┬────┘
   └──────────┬──────────┘                         │
              │ applyTick                          │ preset, law
              ▼                                   ▼
   ┌─────────────────────┐              ┌───────────────────────┐
   │ WorldEvolution      │ tickUniverse  │ WorldEvolutionKernel   │
   │ EngineAdapter       │──────────────►│ (BasePhysics / Vector  │
   └─────────────────────┘              │  DynamicsEngine)      │
                                        └───────────┬───────────┘
                                                    │
                                                    ▼
   ┌─────────────────────┐              state_vector, age
   │ UniverseModel        │ ◄─────────────────────────
   │ (runtime instance)   │    CosmologyRepository.save
   └─────────────────────┘
```

---

## 4. World vs Universe (tóm tắt)

| Khái niệm | Vai trò | Nơi state / tick |
|-----------|--------|-------------------|
| **World** | Aggregate root: preset, law_profile, config, evolution influences. 1 World → N Universe. | Trong **Luồng A**: World.current_time, World.entropy; state theo thời gian trong **cosmic_snapshots** (world_id, year). |
| **Universe** | Runtime instance: state_vector, age, parameters. Thuộc 1 World (world_id). | Trong **Luồng B**: Universe là đối tượng được tick; state trong **universes** (state_vector, age); CosmologyRepository.save. |

Theo tài liệu WorldOS 2.0: tick và fork nên thao tác trên **Universe**; World chỉ định nghĩa luật và constraint. Trong code, **Luồng A** (SagaRunner) vẫn tick **World** qua WorldEvolutionPipeline + CosmicSnapshot; **Luồng B** đã tick **Universe** qua WorldEvolutionKernel.

---

## 5. Các thành phần can thiệp thủ công (Writer / Cluster)

- **Writer World Hub** (freeze, resume, step, rollback): Dùng **EpochControlService** (in-memory snapshot history). Step/rollback tác động lên World; không nhất thiết đi qua UniverseRuntimeService hay SagaRunner.
- **EpochControlService**: freeze/resume (cập nhật World.autonomous), stepEpoch (log “requested”), rollback (khôi phục từ snapshotHistory in-memory). Snapshot history nằm trong process, không persist DB.
- **God Console** (metrics, intervene): Đọc/ghi **World.state** (WorldState.state_vector, current_phase); không trực tiếp gọi Saga hay Universe tick.

Tức là: **Cluster/Writer** điều khiển World (freeze, step, rollback, inject) và xem metrics; luồng simulation “tự chạy” có thể là **SagaRunner** (Luồng A) hoặc **UniverseRuntimeService** (Luồng B) tùy cách gọi.

---

## 6. Kết luận

- **Top-down theo domain**: WorldContext → RuntimeContext → SagaContext. World là upstream; Universe thuộc World; Saga đọc runtime events.
- **Top-down theo execution**:
  - **Luồng A (Saga/Genesis)**: Genesis → Saga → SagaRunner → World + WorldEvolutionPipeline + CosmicSnapshotRepository. Một Saga có thể có nhiều World (world_count); mỗi World được simulateWorld đến khi complete/collapse rồi chuyển sang World tiếp theo.
  - **Luồng B (Universe runtime)**: Advance Universe → UniverseRuntimeService → WorldEvolutionKernel.tickUniverse(World, Universe) → state lưu trên Universe.
- **Hai luồng chưa thống nhất**: SagaRunner không dùng Universe; nó dùng World + CosmicSnapshot. Khi cần “một kiến trúc duy nhất”, có thể chọn: hoặc SagaRunner chuyển sang tạo Universe cho mỗi World và tick qua UniverseRuntimeService, hoặc giữ Luồng A là chính và coi Luồng B cho API/Universe riêng.

Tài liệu này mô tả đúng theo code và docs hiện tại; khi refactor (ví dụ Saga tick Universe, bỏ bớt path World-only) nên cập nhật lại doc cho khớp.

## 7. WorldOS v3 (IP Foundry)

- Universe là authority duy nhất; state và tick trên Universe + universe_snapshots.
- Snapshot-first: ghi universe_snapshots mỗi tick; rollback/fork từ snapshot.
- Saga chỉ orchestrator: SagaService.spawnUniverse, runBatch, evaluate, fork. Genesis gọi SagaService.genesisV3 (không dispatch RunSagaSimulationJob).
- Luồng cũ (SagaRunner + cosmic_snapshots) deprecated. Chi tiết: WORLDOS_V3_ARCHITECTURE.md.
