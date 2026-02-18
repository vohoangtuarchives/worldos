# Serial Narrative & Evolution Kernel — Roadmap

## Hiện trạng (đã làm)

### SerialStoryService — production-safe refactor
- **Transaction + row lock**: `generateNextChapter()` chạy trong `DB::transaction` với `NarrativeSeries::lockForUpdate()` để tránh race condition khi nhiều worker cùng sinh chương.
- **Không recursion**: Khi hết chương trong một arc, tăng `current_book_index` và tiếp tục vòng lặp trong cùng transaction; không gọi lại `generateNextChapter()`.
- **Story memory giới hạn**: `buildStorySoFar()` chỉ lấy **5 chương gần nhất** (config: `STORY_MEMORY_CHAPTER_LIMIT`) để tránh O(n) load và vượt context LLM.
- **Đếm chương bằng count()**: Dùng `SerialChapter::where(...)->count()` thay vì load toàn bộ chapter trong arc.

### Schema
- `serial_chapters`: unique `(narrative_series_id, book_index, chapter_index)` + FK → tránh duplicate; index ngầm từ unique đủ cho count theo arc.

---

## Hướng dài hạn (Evolution Kernel + Narrative as projection)

Tài liệu này tham chiếu thiết kế: **Narrative không phải engine kể chuyện độc lập**, mà là **lớp projection của Evolution Kernel** — truyện là cách thế giới tự kể lại sự biến đổi.

### Nguyên tắc
1. **Simulation quyết định macro**: StateVector, Arc (phase), Mutation đến từ Evolution Core.
2. **Narrative chỉ diễn giải**: Nhận WorldEvent / StateSnapshot + mutation → build context → LLM render text. Không được sửa world state.
3. **Memory cho AI**: Short-term (vài chương gần nhất), mid-term (arc summary), long-term (world/character state). Không feed full chapter history.
4. **Dual-Core**: Evolution Core (state, pressure, mutation) + Narrative Core (quality, continuity, bias đề xuất). Liên kết qua **Dramatic Contract** (Narrative có thể đề xuất bias; Simulation có quyền từ chối).

### Các bước có thể làm tiếp (khi tích hợp Evolution Kernel)
- **StateVector formal**: dimensions chuẩn hóa (order, entropy, cohesion, legitimacy, innovation, …); PresetDescriptor chỉ là data (growthRates, influenceMatrix, collapseThresholds).
- **Arc emergent**: Arc không plan trước; detect từ tension trajectory (Markov + state-conditioned), rồi map ArcPhase → Preset.
- **NarrativeModule**: `project(World, Mutations)` → FocusSelector chọn mutation “dramatic” nhất → ContextBuilder → Renderer → store projection; có thể emit event (e.g. ChapterGenerated) cho queue/Kafka.
- **Character Arc Engine**: CharacterState (loyalty, fear, ambition, …) cập nhật theo WorldEvent; narrative ưu tiên focus nhân vật có arc mạnh.
- **SimulationRunner**: CLI/Job chạy N ticks deterministic (seed), snapshot/event log, Monte Carlo tuning; chỉ khi simulation ổn mới gắn AI render.

Chi tiết đầy đủ nằm trong bản thiết kế trao đổi (StateVector, Meta-Preset, RegimeModifier, InfluenceGraph, CollapseCascade, Resource/Population layer, AI Collapse Predictor, v.v.). Doc này chỉ ghi lại **refactor production-safe hiện tại** và **định hướng** để code sau này không đi lệch.
