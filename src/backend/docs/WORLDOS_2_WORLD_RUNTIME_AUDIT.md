# World runtime state — Audit (WorldOS 2.0 Clean)

Theo [WORLDOS_2_CLEAN_ARCHITECTURE.md](WORLDOS_2_CLEAN_ARCHITECTURE.md): **World không giữ runtime state**. Tài liệu này liệt kê cột/bảng hiện đang lưu runtime trên World và hướng chuyển sang Universe.

---

## I. World model — cột nghi ngờ runtime

| Cột (World) | Mục đích hiện tại | Phân loại | Hướng Clean |
|-------------|-------------------|-----------|--------------|
| `current_time` | Năm hiện tại khi tick World/Saga | **Runtime** | Đọc/ghi từ Universe.age (hoặc snapshot universe); World không cập nhật. Saga đã sync từ Universe sau advance(). |
| `entropy` | Entropy hiện tại | **Runtime** | Nguồn chân lý: Universe.state_vector (entropy dimension). World có thể cache read-only từ Universe nếu cần UI, nhưng không là source. |
| `initial_entropy` | Entropy lúc tạo World | Có thể law/config | Giữ trên World như part of preset/config hoặc chuyển sang "world_template". |
| `cosmic_entropy` | Entropy cosmic (?) | **Runtime** | Chuyển sang Universe-scoped hoặc bỏ nếu trùng entropy. |
| `tick` | Số tick đã chạy | **Runtime** | Suy từ Universe.age hoặc snapshot; không lưu trên World. |

**Khuyến nghị ngắn hạn:** Không xóa cột ngay; thêm policy: khi tick đi qua Universe (Saga/Runtime), **không ghi** current_time/entropy lên World, hoặc chỉ ghi read-through từ Universe. Refactor dần: mọi đọc current_time/entropy cho "runtime" chuyển sang lấy từ Universe (theo world_id).

---

## II. Bảng / relation gắn World có thể chứa runtime

| Bảng / relation | Nội dung | Phân loại | Hướng Clean |
|----------------|----------|-----------|-------------|
| `world_snapshots_v2` | Snapshot state theo thời gian | **Runtime** | Chuyển sang universe_id (UniverseSnapshot); World không giữ snapshot. |
| `cosmic_snapshots` | Cosmic state theo world | **Runtime** | Gắn universe_id; đọc từ Universe khi tick. |
| `governance_logs` | Log theo world | Có thể runtime | Nếu log theo "năm" runtime → gắn universe_id. |
| `chronicles` | Biên niên sử | **Runtime** | Gắn universe_id (UniverseChronicle); World không giữ chronicle. |
| `civilization_snapshots`, `civilization_diffs`, `civilization_cycles` | Đã gắn universe_id trong migration | OK | Giữ gắn Universe. |

**Khuyến nghị:** Migration sau có thể thêm universe_id vào world_snapshots_v2 / cosmic_snapshots / chronicles và dần chuyển đọc/ghi sang Universe; khi đủ dữ liệu, bỏ hoặc deprecated bản ghi gắn pure world_id.

---

## III. Hành động đã làm / cần làm

- [x] **Policy code**: Khi tick đi qua Universe (Saga path), chỉ SagaRunner sync current_time/entropy từ Universe → World (read-through cache). Không nguồn nào khác ghi runtime lên World trong cùng luồng Saga.
- [x] **Đọc runtime (một phần)**: `CosmologyRepository::getRuntimeStateForWorld(world_id)` trả về age/entropy từ Universe. Đã dùng tại: **RealmContactService::calculateRealmInfluence**, **StateLoader::loadVector/saveVector** (currentYearForWorld), **WriterCosmologyController::getSagaTree** (current_era), **WorldHubController::loadHeroesData** (currentEra), **SagaExplorerController::tree** (current_era). Các chỗ khác (SagaRunner, WorldEvolutionKernel, TimeManager, …) vẫn đọc/ghi World.current_time theo policy.
- [x] **Snapshot/Chronicle (một phần)**: Migration thêm nullable `universe_id` vào **chronicles** và **cosmic_snapshots**. SagaRunner khi ghi chronicle truyền `universe_id` khi tick đi qua Universe; bản ghi cũ hoặc path không-Universe vẫn chỉ world_id. Tạo mới cosmic_snapshots chưa gắn universe_id (có thể bổ sung khi có call site từ Universe tick).

---

## IV. Call sites ghi World.current_time / World.entropy

| Vị trí | Khi nào | Policy Clean |
|--------|---------|--------------|
| **SagaRunner** (204–205) | Sau `advance($universeId, …)` | **Đúng**: sync từ Universe (Universe là source of truth); World là cache cho chronicle/UI. |
| **SagaRunner** (243–244, 250) | Fallback evolutionPipeline hoặc time-only | Chỉ chạy khi không có universeId hoặc kernel; giữ tạm. |
| **WorldEvolutionKernel** (evolve, evolveWithBasePhysics) | Khi tick đi qua World (không qua Universe) | Kernel ghi lên World vì lúc đó World là runtime context; khi có Universe thì tickUniverse() không ghi World (caller persist Universe). |
| **TimeManager** (77) | advance(World, deltaTime) | Legacy path; ưu tiên advance Universe rồi sync. |
| **WorldTickService** | Legacy world tick | Dần deprecated khi mọi tick đi qua Universe. |

**Policy:** Trong luồng Saga runtime-first, **chỉ** SagaRunner được ghi `world->current_time` và `world->entropy`, và chỉ bằng giá trị lấy từ Universe sau advance().

*Tài liệu audit; cập nhật khi có thay đổi schema hoặc policy.*
