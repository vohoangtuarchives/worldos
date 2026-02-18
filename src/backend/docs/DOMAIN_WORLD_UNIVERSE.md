# World vs Universe — Ranh giới domain

## Tóm tắt

- **World** = Aggregate Root. Định danh thế giới, genesis snapshot, constraint (Anchor + ConstraintProfile). Một World có thể có nhiều Universe (nhiều timeline).
- **Universe** = Runtime Instance. Một phiên bản chạy của World: `state_vector`, `age`, `parameters`, thuộc một World qua `world_id`. Tick và fork đều gắn với một World.

## World (Aggregate Root)

- **Vai trò:** Định danh “thế giới” trong Cosmology; lưu thông tin genesis (snapshot ban đầu, ConstraintProfile bất biến, Structural Anchor).
- **Quan hệ:** 1 World → nhiều Universe (nhiều timeline / nhánh).
- **Lưu ý code:** Trong codebase tồn tại cả `App\Models\World` (Saga/Writer — model Eloquent cho dự án truyện) và khái niệm “World” trong Cosmology (AR của domain). Khi đọc code cần phân biệt: Cosmology World = AR gắn với genesis + constraint; `UniverseModel.world_id` trỏ tới bảng `worlds` (Saga/Writer). Nếu sau này tách hẳn Cosmology World ra bảng/entity riêng thì sẽ refactor tương ứng.

## Universe (Runtime Instance)

- **Vai trò:** Thể hiện trạng thái mô phỏng tại một thời điểm: `WorldStateVector` (state_vector), age (tick), parameters (metadata, seed, v.v.).
- **Quan hệ:** N thuộc 1 World (`UniverseModel.world_id`).
- **Tick / Fork:** Mọi tick và fork đều thao tác trên Universe; kết quả fork là Universe mới cùng `world_id` (hoặc World mới nếu fork “từ genesis” với constraint khác).

## Parameters (Universe)

Trong `parameters` (JSON) nên có:

- `random_seed`: int/string — seed cho deterministic tick và fork.
- `constraint_profile`: snapshot ConstraintProfile tại genesis (immutable).
- `anchor_type`: string — Structural Anchor (academic_system, faction_system, commercial_system).
- `ancestors`, `event`, `branch_type`: dùng khi fork (đã có trong BifurcationService).

## Genesis (stage) vs Khai Thiên (flow)

- **Genesis (stage):** Giai đoạn đầu đời trong Stage Machine của kernel (`current_stage = GENESIS`). Không phải một màn hình UI; World/Universe khi vừa tạo đang ở stage Genesis cho đến khi kernel chuyển phase.
- **Khai Thiên (flow):** Hành động tạo World (và optional Universe đầu tiên) từ preset hoặc Anchor + Constraint. UI/API “Khai Thiên” = “Tạo World”.
