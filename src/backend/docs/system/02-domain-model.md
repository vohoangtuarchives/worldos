# 02 — Domain model (World, Universe, Snapshot, Saga)

## 2.1 World

- **Vai trò**: Bản thiết kế bất biến (rule set, archetype, gene_vector, config). Không mang runtime (không tick, không entropy thời gian thực).
- **Model**: `App\Models\World`
- **Bảng**: `worlds`
- **Quan hệ**: Một World có nhiều Universe (runtime instance). World có thể bị "đóng băng" (HALTED) qua policy → Universe không được tick.

World cung cấp: `gene_vector`, `config` (archetype, preset_key, current_stage), `law_profile` (cho kernel validateMutation). Các service như WorldPowerProfileService bootstrap profile từ preset.

## 2.2 Universe (runtime instance)

- **Vai trò**: Đơn vị kinh tế duy nhất cho tick, state_vector, entropy, stability. Mỗi Universe thuộc một World (`world_id`).
- **Model**: `App\Models\UniverseModel` (bảng `universes`)
- **Entity**: `App\Domains\Cosmology\Entities\Universe`

**Bảng universes (v3)**: id (uuid), world_id (bigint FK worlds, bắt buộc), name, age (int, tick hiện tại), state_vector (json), entropy (float), stability_index (float), status (string: running|collapsed|stable|archived), parent_universe_id (uuid FK universes), parameters (json), is_archived, death_cause, saga, coords, cosmic_faction_id.

**Quan hệ**: world(), parentUniverse(), snapshots() (universe_snapshots).

## 2.3 UniverseSnapshot (snapshot-first)

- **Vai trò**: Lưu trạng thái Universe tại một tick; dùng cho rollback, fork, clone, AI metrics.
- **Model**: `App\Models\UniverseSnapshot`
- **Bảng**: `universe_snapshots`: id, universe_id (FK universes cascade), tick (int), state_vector (json), entropy, stability_index, metrics (json), timestamps. Index (universe_id, tick).
- **Repository**: `App\Domains\Cosmology\Repositories\UniverseSnapshotRepository` — save(), getAtTick(), getLatest().

## 2.4 Saga (orchestrator)

- **Vai trò**: Điều phối (spawn, advance, evaluate, fork). Không có clock/entropy/physics riêng.
- **Model**: `App\Domains\Saga\Saga` (bảng `sagas`)

**Bảng sagas (v2)**: id, name, world_count, archetype_focus, carry_legacy, status (pending|running|completed|failed), current_world_index, metadata, strategy, evaluation_policy, current_universe_id (FK universes), started_at, completed_at, genre.

**Bảng saga_worlds**: id, saga_id, world_id, universe_id (v3: mỗi saga world trỏ một Universe), sequence, status (pending|running|completed|collapsed), archetype_legacy, myth_legacy, collapse_context.

Flow v3: advance/evaluate theo saga_worlds.universe_id.

## 2.5 Luồng dữ liệu

- World → tạo Universe (SagaService::spawnUniverse).
- Universe mỗi tick → ghi UniverseSnapshot (UniverseRuntimeService → kernel → UniverseSnapshotRepository::save).
- Saga sở hữu SagaWorld (world_id + universe_id); advance/evaluate theo từng universe_id.
- Fork: clone từ snapshot → Universe mới với parent_universe_id trỏ Universe gốc.
