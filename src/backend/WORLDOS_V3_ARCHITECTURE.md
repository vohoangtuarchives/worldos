# WorldOS V3 Architecture — Migration Status

> **This is the target architecture + current adoption level.**  
> Not all components are fully migrated. See "Migration Status" below.

## Core Principle

**Universe-centric**: All operations center on `universe_id` + `parent_universe_id`.
- World = aggregate root (owns laws, physics profile)
- Universe = runtime instance of World (owns state_vector, age, snapshots)

## Canonical Runtime Pipeline

```
Entrypoint (API/Job/Command)
  → UniverseRuntimeService::advance(universeId, ticks)
    → UniverseRuntimeService::tick()
      → WorldEvolutionEngineAdapter::applyTick()
        → WorldEvolutionKernel::tickUniverse(world, universe, shock?)
          → BasePhysicsEngine::evolve(state, preset, regime)
          → StructuralMutationEngine::mutate() [on collapse]
      → UniverseSnapshotRepository::save()
      → UniverseTicked event dispatched
```

All entrypoints MUST converge to this pipeline:
- ✅ API controllers → `SagaService` → `UniverseRuntimeService`
- ✅ `TickUniverseJob` → `UniverseRuntimeService::advance()`
- ✅ `RunSagaSimulationJob` → `SagaService::runBatchWithEvaluation()`
- ✅ `EpochControlService` → `UniverseRuntimeService::tick()` / `SagaService::runBatch()`

## Evaluator System

```
UniverseEvaluatorInterface
  ├── StubUniverseEvaluator (heuristic: entropy/novelty scoring)
  └── LLMUniverseEvaluator (LLM-powered + stub fallback)
```
Driver: `config('worldos.evaluator_driver')` — `stub` (default) | `llm`

## Snapshot Model

| Table | Status | Purpose |
|---|---|---|
| `universe_snapshots` | ✅ Active (V3) | State vector per tick, rollback, fork |
| `cosmic_snapshots` | ⚠️ Legacy | World-level snapshots, used by WriterConsole |

API controllers read from `universe_snapshots`. WriterConsole controllers still use `cosmic_snapshots` (pending migration).

## Migration Status

### ✅ Fully Migrated
- `WriterSagaController` → `SagaService`
- `WriterCosmologyController` → `SagaService`
- `WriterWorldHubController::emergency()` → `UniverseModel` V3
- `EpochControlService` → `UniverseRuntimeService` + `SagaService`
- `RunSagaSimulationJob` → `SagaService`
- `TickUniverseJob` → `UniverseRuntimeService::advance()`
- `WriterWorldSnapshotController` → `UniverseSnapshot` (V3)
- `EmergencyInterventionService` → V3 methods on `UniverseModel`

### ⚠️ Legacy (WriterConsole — not API-facing)
- `WorldHubController` — uses `cosmicSnapshots()`
- `GodConsoleController` — uses `cosmicSnapshots()`
- `SagaExplorerController` — cleaned up, dispatches V3 job
- `WorldCreationController` — dispatches V3 job
- `CosmosSimulateCommand` — uses `CosmicSnapshotEloquentRepository`

### 🗑️ Deprecated
- `SagaRunner` — V2 orchestrator, not used by API controllers
- `WorldEvolutionPipeline` — replaced by `WorldEvolutionKernel`
- `Cosmology::tick()` — legacy, used only as fallback for universes without `world_id`
