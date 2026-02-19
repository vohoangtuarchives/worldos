# 02 — Domain model (World, Universe, Snapshot, Saga)

## 2.1 World

- **Vai trò**: Bản thiết kế bất biến (rule set, archetype, gene_vector, config). Là **Container** vật lý/meta-physics. Không gắn liền với một Preset cụ thể khi khởi tạo (Preset được dùng khi spawn Universe).
- **Model**: `App\Models\World`
- **Bảng**: `worlds`
- **Quan hệ**: Một World có nhiều Universe (runtime instance).

World cung cấp: `gene_vector`, `config` (archetype, origin_type, physics_profile).

## 2.2 Universe (runtime instance)

- **Vai trò**: Đơn vị thực thi (Simulation Instance). Được spawn từ một World và khởi tạo (seed) dựa trên một **Genesis Preset**.
- **Model**: `App\Models\UniverseModel` (bảng `universes`)
- **Entity**: `App\Domains\Cosmology\Entities\Universe`

**Bảng universes (v3)**: id, world_id (FK worlds), name, age (tick), status, preset_key (ghi nhận preset gốc), parent_universe_id (nếu là fork), state_vector, entropy, stability_index.

**Quan hệ**: world(), parentUniverse(), snapshots() (universe_snapshots).

## 2.3 UniverseSnapshot (snapshot-first)

- **Vai trò**: Lưu trạng thái Universe tại một tick; dùng cho rollback, fork, clone, AI metrics.
- **Model**: `App\Models\UniverseSnapshot`
- **Bảng**: `universe_snapshots`: id, universe_id (FK universes cascade), tick (int), state_vector (json), entropy, stability_index, metrics (json), timestamps. Index (universe_id, tick).
- **Repository**: `App\Domains\Cosmology\Repositories\UniverseSnapshotRepository` — save(), getAtTick(), getLatest().

## 2.4 Saga (orchestrator)

- **Vai trò**: Điều phối (Advance, Evaluate, Fork) một chuỗi các Universe. Được khởi tạo **từ** một Universe đang hoạt động (Active Universe).
- **Model**: `App\Domains\Saga\Saga` (bảng `sagas`)

**Bảng sagas (v2)**: id, name, status, current_universe_id.

**Bảng saga_worlds**: Liên kết Saga với Universe.

Flow v3:
1. Tạo World (Container).
2. Spawn Universe (Instance + Preset).
3. Tạo Saga từ Universe đó (Orchestrator).
4. Saga advance/evaluate Universe.

## 2.5 Luồng dữ liệu

- World (Container) → Spawn Universe (dùng Preset).
- Universe run → Snapshot.
- Saga wrap Universe → Advance/Fork.
