# WorldOS — Bộ tài liệu phát triển hệ thống

Bộ tài liệu này mô tả **WorldOS v3** — kiến trúc simulation-first, IP Foundry. Đây là tài liệu chính thức cho phát triển; các tài liệu cũ nằm ngoài `docs/system/` có thể sai lệch và sẽ bị thay thế dần.

## Cấu trúc tài liệu

| Tài liệu | Nội dung |
|----------|----------|
| [01-overview-and-principles.md](01-overview-and-principles.md) | Mục tiêu, ba luật sắt, IP Foundry vs story tool |
| [02-domain-model.md](02-domain-model.md) | World, Universe, UniverseSnapshot, Saga — schema và quan hệ |
| [03-simulation-flow.md](03-simulation-flow.md) | Entry point, Kernel, luồng tick, ghi snapshot |
| [04-saga-orchestrator.md](04-saga-orchestrator.md) | SagaService, Genesis v3, runBatch, fork, evaluate |
| [05-ai-evaluation-layer.md](05-ai-evaluation-layer.md) | Metrics, Evaluator, DecisionEngine, Kernel validate/applyPressure |
| [06-api-and-integration.md](06-api-and-integration.md) | API, routes, Genesis, Writer |
| [07-legacy-and-migration.md](07-legacy-and-migration.md) | Legacy deprecated, cosmic_snapshots, migration path |

## Nguyên tắc đọc

- **Authority**: Chỉ **Universe** mang runtime (tick, state). World chỉ là rule container. Saga chỉ orchestrate.
- **Snapshot-first**: Mọi tiến hóa ghi `universe_snapshots`; rollback/fork/clone từ snapshot.
- **AI**: Đánh giá và đề xuất mutation qua kernel; không sửa `state_vector` trực tiếp.

## Vị trí code chính (backend)

- Runtime: `App\Domains\Runtime\UniverseRuntimeService`
- Snapshot: `App\Domains\Cosmology\Repositories\UniverseSnapshotRepository`
- Saga orchestrator: `App\Domains\Saga\Services\SagaService`
- Kernel: `App\Domains\Evolution\Kernel\WorldEvolutionKernel`
- AI: `App\Domains\Runtime\Evaluation\*` (MetricsExtractor, UniverseEvaluatorInterface, DecisionEngine)
