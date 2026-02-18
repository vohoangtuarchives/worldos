# Domain Architecture: World Root, Universe Runtime

## Chốt kiến trúc

- **World = Aggregate Root** (Core Domain): ecosystem hoàn chỉnh — materials, governance, timeline, seeds, scars, attractors, god-console, health, evolution engine. Luật và định nghĩa nằm ở World.
- **Universe = Runtime Instance** (RuntimeContext): state thực thi của một World — current_tick, fork lineage, chronicle. Không chứa logic gốc; chỉ thực thi logic của World. Tương tự "running match" của "game definition".
- **Saga gắn với World**; Universe là một branch của saga, không phải root.

Quan hệ: **Universe nằm trong World** (World là container; Universe là instance).

## Event topology

- **World-side:** `WorldDefined`, `WorldLawUpdated`, `MaterialInjected` → RuntimeContext (UniverseRuntime) react. Classes: `App\Domains\World\Events\*`.
- **Runtime-side:** `UniverseTicked`, `UniverseForked`, `UniverseCollapsed` → SagaContext react. Classes: `App\Domains\Runtime\Events\*`.
- **Không có** `UniverseLawUpdated` — luật chỉ ở World.
- `UniverseTicked` is dispatched by `UniverseRuntimeService` after each tick (with universe id, world id if any, age, state summary).

## Aggregate ownership

- World không được duplicate logic vào Universe. Universe chỉ giữ runtime state và delegate tick/evolution sang World (EvolutionKernel / evolution engine).
- World freeze → Universe không được tick (policy enforcement).

## Code layout (gợi ý)

- **World:** `app/Domains/World/` — WorldAggregate, Evolution (kernel nếu gắn World), Materials, Governance.
- **Runtime:** `app/Domains/Runtime/` hoặc `app/Domains/Cosmology/` — Universe entity (runtime), UniverseRuntimeService (tick delegate to World), tick engine, chronicle log.
- **Saga:** SagaContext — narrative, canonize, publish; đọc từ Runtime events.

## DB

- `universes.world_id` (FK to worlds): Universe thuộc World. Nullable cho dữ liệu cũ; khi có world_id thì tick phải đi qua World evolution.
