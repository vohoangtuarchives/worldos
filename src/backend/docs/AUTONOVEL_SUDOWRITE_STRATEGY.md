# Chiến lược: Auto sản xuất truyện dài chất lượng cao

Mục tiêu: **auto sản xuất truyện dài chất lượng cao** — kết hợp **tốc độ + memory** (AutoNovel) và **chất lượng prose + cấu trúc** (Sudowrite). Nếu build hệ thống riêng (WorldOS), học kiến trúc từ cả hai.

---

## 1. Hybrid sản phẩm: AutoNovel core + Sudowrite polish

| Vai trò | Công cụ | Lý do |
|--------|---------|-------|
| **Core pipeline** (tốc độ + memory) | **AutoNovel** | Batch gen, multi-layer memory (500+ elements), parallel chapters, ~45 phút/tiểu thuyết, coherence cao. Sinh draft nhanh, giữ continuity. |
| **Polish từng arc** (prose + tone) | **Sudowrite** | Story Bible = source of truth; Muse (fiction-specific model); Scene/Draft được guide bởi Characters, Worldbuilding, Outline. Dùng để chỉnh từng arc sau khi AutoNovel đã có skeleton. |

**Luồng đề xuất:** AutoNovel sinh full draft (batch) → export theo arc → Sudowrite Story Bible nhập Synopsis/Characters/Worldbuilding/Outline từ draft → polish từng arc bằng Sudowrite (rewrite scene, match style).

---

## 2. Kiến trúc học từ AutoNovel

### 2.1 Batch generation + tốc độ

- **Parallel chapter generation**: nhiều chương xử lý đồng thời, vẫn giữ coherence.
- **Single premise → full book**: từ một premise sinh ra sách có cấu trúc (chapters, format) trong thời gian ngắn.
- **Metric**: ~50k words/hour, ~45 phút/novel; 96.4% plot coherence.

**Áp dụng WorldOS:**

- Đẩy **sinh chương** sang queue (job): mỗi series có thể có job batch “sinh N chương cho arc hiện tại” thay vì chỉ 1 chương/request.
- **BeatSpec + MemorySnapshot** đã tách planner và generator; có thể mở rộng: planner sinh nhiều BeatSpec cho cả arc → nhiều job generator chạy song song (cẩn thận shared state: dùng narrative_state + lock).
- Giữ **deterministic BeatPlanner** (seed, arc progress) để parallel chapters vẫn nằm trên cùng tension curve.

### 2.2 Multi-layer content memory

- Track **500+ elements** đồng thời: characters, chapters, citations, arguments, references.
- **128k token context** + multi-layer compression; memory extraction và context persistence qua từng chương.
- World-building và character memory tích hợp, consistency qua hàng trăm trang.

**Áp dụng WorldOS:**

- **NarrativeState** + **narrative_driven_state** + **world_snapshot** đã là một lớp “world memory”; thiếu lớp **character memory** và **element registry** rõ ràng.
- Hướng đi: **Story Bible–style registry** (xem mục 3): Characters, Worldbuilding, Outline/Arc — mỗi entity có bản ghi, được inject có chọn lọc vào prompt (chỉ “active” characters/world elements cho chương hiện tại).
- **Memory compression**: NarrativeMemoryService đã có digest + lastParagraphs; có thể thêm “layer 2”: mỗi N chương tạo **arc digest** (tóm tắt nhân vật đã xuất hiện, xung đột chưa giải quyết) để giảm token mà vẫn đủ continuity.

---

## 3. Kiến trúc học từ Sudowrite Story Bible

### 3.1 Story Bible = source of truth

Story Bible là **một nơi tập trung** toàn bộ yếu tố cốt lõi; AI và người viết đều tham chiếu.

**Các trường (theo thứ tự ảnh hưởng):**

1. **Braindump** — ý tưởng ban đầu (manual).
2. **Genre** — thể loại (manual); ảnh hưởng Synopsis, Outline, Scenes, Draft.
3. **Style** — phong cách (manual / Match My Style); ảnh hưởng Beat + Prose.
4. **Synopsis** — tóm tắt truyện; từ Braindump/Genre; ảnh hưởng Characters, Worldbuilding, Outline, Scenes.
5. **Characters** — từ Synopsis; ảnh hưởng Outline, Scenes, Draft.
6. **Worldbuilding** — từ Synopsis; ảnh hưởng Outline, Scenes, Draft.
7. **Outline** — từ Genre, Synopsis, Characters, Worldbuilding; ảnh hưởng Scenes.
8. **Scenes** — từ Genre, Style, Synopsis, Outline, Characters, Worldbuilding; ảnh hưởng Draft.
9. **Draft** — prose chương; từ Style, Genre, Characters, Worldbuilding, Scenes.

**Nguyên tắc:** Trường sau chỉ nhìn các trường đã có trước; generation “step-by-step” có kiểm soát.

### 3.2 Áp dụng vào WorldOS (build hệ thống riêng)

| Sudowrite field | WorldOS tương đương / đề xuất |
|-----------------|-------------------------------|
| Braindump | `narrative_series.config['premise']` hoặc bảng `story_bible` (braindump text). |
| Genre | Đã có: `narrative_series.genre_key` + SerialGenrePreset. |
| Style | Có thể thêm: `narrative_series.config['style_notes']` hoặc StyleVector (tone, density) từ BeatSpec. |
| Synopsis | Có thể: arc one-liners (SerialArcPlanner) + mô tả series; hoặc bảng `story_bible.synopsis`. |
| Characters | **Thiếu.** Đề xuất: bảng `story_bible_characters` (name, role, traits, first_seen_chapter); inject “active characters” vào prompt theo BeatSpec. |
| Worldbuilding | Một phần đã có: NarrativeBridge (world state → genre, traits, situations); narrative_driven_state (shadow_presence, magic_stability). Có thể mở rộng: bảng `story_bible_worldbuilding` (locations, rules, symbols). |
| Outline | Arc + chapter blueprints (SerialArcPlanner + BeatPlanner) tương đương outline cấp cao; có thể lưu `story_bible.outline` (text hoặc structured). |
| Scenes | Có thể: mỗi SerialChapter = 1 scene; hoặc thêm “scene brief” (beat + conflict) trước khi sinh prose. |
| Draft | Đã có: SerialChapter.content (prose). |

**Thiết kế “Story Bible” trong WorldOS (gợi ý):**

- **Bảng `story_bibles`**: narrative_series_id (1–1), braindump, synopsis, genre_key, style_notes, outline (text hoặc json).
- **Bảng `story_bible_characters`**: story_bible_id, name, role, traits (json), first_seen_chapter, is_active.
- **Bảng `story_bible_worldbuilding`**: story_bible_id, kind (location|rule|symbol), name, description.
- Khi build prompt: lấy Synopsis + **Characters được mention trong BeatSpec hoặc gần đây** + **Worldbuilding có liên quan** + current_world_state_narrative → đưa vào system/user prompt để “keep AI on track”.

---

## 4. Pipeline đề xuất cho WorldOS (tự build)

Kết hợp bài học từ cả hai:

```
[Story Bible: Braindump, Genre, Synopsis, Characters, Worldbuilding, Outline]
        ↓
  BeatPlanner (arc + chapter beats, tension curve)
        ↓
  Batch: N BeatSpecs cho arc
        ↓
  Generator (LLM) từng chương — context = MemorySnapshot + Story Bible (active chars + world) + narrative_driven_state
        ↓
  Causality Bridge: StoryEventExtractor → narrative_driven_state (+ optional World entropy)
        ↓
  (Tuỳ chọn) Arc polish: gom toàn bộ chương arc → job “polish arc” (model tốt hơn / Sudowrite API nếu có) với full Bible context
```

**Ưu tiên triển khai:**

1. **Story Bible tối thiểu**: braindump/synopsis + characters (bảng + inject vào prompt).
2. **Batch chapter jobs**: queue “generate next N chapters” cho một arc, mỗi chương 1 job, đọc/ghi NarrativeState + narrative_driven_state nhất quán.
3. **Memory layer 2**: arc digest mỗi 5 chương (structured summary) để prompt không phình.

---

## 5. Tóm tắt

- **Dùng ngoài**: AutoNovel làm core pipeline (tốc độ + memory), Sudowrite làm polish từng arc (Bible + prose).
- **Build riêng (WorldOS)**: học **AutoNovel** — batch gen, multi-layer memory, parallel chapters, compression; học **Sudowrite** — Story Bible (source of truth), thứ bậc Braindump → Synopsis → Characters → Worldbuilding → Outline → Scenes → Draft, và “chỉ inject elements có liên quan” vào từng bước.
- Codebase hiện tại đã có: BeatSpec/MemorySnapshot, Causality Bridge (narrative_driven_state), SerialStoryService. Thiếu: **Story Bible** (characters, worldbuilding, synopsis), **batch job** cho nhiều chương, **arc-level polish** hook.

Tài liệu này có thể dùng làm spec cho phase tiếp theo: Story Bible schema + API và tích hợp vào prompt.
