# Context Map (DDD)

World = Aggregate Root. Universe = Runtime Instance. Quan hệ: **Universe nằm trong World**.

## Bounded contexts

| Context | Vai trò | Thành phần chính |
|--------|---------|-------------------|
| **WorldContext** (Core) | Evolution, materials, governance, collapse law, health | World aggregate, EvolutionKernel, Materials, Governance |
| **RuntimeContext** | Instance, fork lineage, tick, chronicle | Universe (runtime), tick engine, chronicle log |
| **SagaContext** | Narrative, branch scoring, canonize, publish | Saga, narrative extraction, canonize |

## Relationship

- **WorldContext** là upstream.
- **RuntimeContext** phụ thuộc World: Universe thực thi theo logic của World (Universe không chứa luật gốc).
- **SagaContext** đọc từ RuntimeContext (UniverseTicked, UniverseForked, UniverseCollapsed).

## Diagram (ASCII)

```
WorldContext (Core Domain)
    │
    │  WorldDefined, WorldLawUpdated, MaterialInjected
    ▼
RuntimeContext
    │  Universe = runtime instance of World
    │  UniverseTicked, UniverseForked, UniverseCollapsed
    ▼
SagaContext
```

## Conformist / anti-corruption

- RuntimeContext nhận events từ WorldContext; không đảo ngược (Universe không emit WorldLawUpdated).
- SagaContext subscribe RuntimeContext events; có thể dùng read model / projection.
