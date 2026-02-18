# 01 — Tổng quan và nguyên tắc WorldOS v3

## 1.1 WorldOS là gì

WorldOS là **IP Foundry** (phòng lab simulation, branching timeline, IP mutation), không phải công cụ viết truyện thuần túy. Hệ thống mô phỏng thế giới (World) thành các instance chạy thời gian thực (Universe), đánh giá và nhánh hóa (fork) dựa trên AI và chính sách Saga.

- **Simulation lab**: Chạy nhiều Universe song song hoặc tuần tự, so sánh kết quả.
- **Branching timeline**: Fork Universe từ snapshot, áp dụng shock/mutation, so sánh nhánh.
- **IP mutation**: Thay đổi luật/tham số (qua kernel) để tạo biến thể IP.

## 1.2 Ba luật sắt (không vi phạm)

1. **Universe là đơn vị kinh tế duy nhất**
   - Mọi tick, entropy, state_vector, stability thuộc **Universe**, không thuộc World hay Saga.
   - World chỉ là bản thiết kế (rules, archetype). Saga chỉ điều phối (spawn, advance, evaluate, fork).

2. **Authority rõ ràng**
   - **Tick** chỉ thực hiện qua **WorldEvolutionKernel** (hoặc adapter gọi kernel). Không có “Saga tick” hay “World tick” độc lập.
   - **Snapshot** chỉ ghi từ flow advance (UniverseRuntimeService → kernel → snapshot repository). Một nguồn sự thật cho lịch sử Universe.

3. **Snapshot-first**
   - Mỗi bước tiến hóa (sau tick) ghi **universe_snapshots**. Rollback, fork, clone, đánh giá đều dựa trên snapshot.
   - Không dựa vào cosmic_snapshots hay bảng “world state” cũ cho flow v3.

## 1.3 Kiến trúc đích (tóm tắt)

```
World (immutable rule set)
    ↓
Universe (runtime instance: id, world_id, age, state_vector, entropy, stability_index, status, parent_universe_id)
    ↓ snapshot sau mỗi tick
UniverseSnapshot (universe_id, tick, state_vector, entropy, stability_index, metrics)

Saga (orchestrator)
    → spawnUniverse(World)
    → advance(universeId, N)  [qua UniverseRuntimeService]
    → evaluate(universeId)    [Metrics → Evaluator → DecisionEngine]
    → fork(universeId, ...)   [clone từ snapshot + mutation]
```

- **Entry point** cho “chạy thời gian”: `UniverseRuntimeService::advance($universeId, $ticks)`.
- **AI**: MetricsExtractor → UniverseMetrics; UniverseEvaluator → EvaluationResult; DecisionEngine quyết định fork/archive/continue. Kernel: `validateMutation`, `applyPressure` — AI không ghi state trực tiếp.

## 1.4 Genesis v3

Tạo World + Universe đầu tiên + SagaWorld, sau đó có thể `runBatch` (advance + evaluate) theo chính sách. Không tạo “Saga clock” hay “World entropy”; mọi số liệu đều từ Universe và universe_snapshots.

## 1.5 Tài liệu liên quan trong bộ docs/system

- Chi tiết domain: [02-domain-model.md](02-domain-model.md)
- Luồng tick và snapshot: [03-simulation-flow.md](03-simulation-flow.md)
- Saga và Genesis: [04-saga-orchestrator.md](04-saga-orchestrator.md)
- Lớp AI: [05-ai-evaluation-layer.md](05-ai-evaluation-layer.md)
- API: [06-api-and-integration.md](06-api-and-integration.md)
- Legacy và migration: [07-legacy-and-migration.md](07-legacy-and-migration.md)
