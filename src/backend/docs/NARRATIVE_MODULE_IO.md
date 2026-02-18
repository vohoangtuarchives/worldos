# Narrative Module — Input / Output Contract

Tài liệu mô tả đầu vào, đầu ra và side effects của module sinh truyện dài kỳ (serial). Dùng để tái cấu trúc và tái sử dụng khi thêm Story Bible, batch job, hoặc adapter narrative→World.

---

## Entry point

- **API / entry**: `SerialStoryService::generateNextChapter(string $seriesId)` — tham số duy nhất là `seriesId`.

---

## Đầu vào (Input)

**Đọc từ DB / dịch vụ:**

| Nguồn | Dùng để |
|-------|--------|
| **NarrativeSeries** | config, genre_key, universe_id, title |
| **SerialChapter** (của series) | memory: digest, lastParagraphs (NarrativeMemoryService) |
| **NarrativeState** | last_emotional_beat, narrative_driven_state, world_snapshot |
| **CosmologyRepository** (khi có universe_id) | Universe → getState(), getParameters() — **chỉ đọc, không ghi** |
| **Story Bible** (khi có) | synopsis, braindump, style_notes, characters — inject vào chronicleContext và prompt |

---

## Đầu ra (Output)

**Trả về:**

- `SerialChapter|null` (content, summary, structured_summary, book_index, chapter_index) hoặc `null` nếu đã hết kế hoạch (tất cả arc đã đủ chương).

**Side effects (ghi xuống DB):**

| Bảng / Model | Hành động |
|--------------|-----------|
| **SerialChapter** | Bản ghi mới (content, summary, structured_summary, book_index, chapter_index) |
| **NarrativeState** | updateOrCreate: arc_progress, last_emotional_beat, last_tension, world_snapshot, **narrative_driven_state** |
| **ChapterTelemetry** | Bản ghi mới |
| **NarrativeSeries** | increment total_chapters_generated; có thể update current_book_index |

**Không ghi vào:**

- **World** (bảng `worlds`, tick, entropy, shock_events)
- **Universe** (Cosmology persistence)
- **shock_events**

Diễn biến truyện (narrative_driven_state) chỉ ảnh hưởng prompt chương sau; muốn truyện ảnh hưởng World cần thêm adapter riêng.

---

## Sub-modules / dependencies

| Sub-module | Input | Output |
|------------|-------|--------|
| **BeatPlanner** | arc, chapterIndex, chaptersPerArc, worldState | BeatSpec |
| **NarrativeMemoryService** | series, nextChapterIndex, chaptersPerArc, lastBeat | MemorySnapshot |
| **ChapterProducer** | BeatSpec (hoặc blueprint), MemorySnapshot (hoặc storySoFar), chronicleContext, styleInput | content + usage |
| **StoryEventExtractor** + **WorldMutationPolicy** | chapter content | cập nhật narrative_driven_state (qua NarrativeState) |

---

## Luồng tóm tắt

1. Lock series, load config + arcs (+ universe nếu có).
2. Xác định (book_index, chapter_index) tiếp theo; nếu đã hết → return null.
3. Build **chronicleContext** (series_title, arc, config, world_state nếu có, current_world_state_narrative, **synopsis/characters từ Story Bible nếu có**).
4. Lấy BeatSpec (BeatPlanner) và MemorySnapshot (NarrativeMemoryService).
5. **ChapterProducer** sinh nội dung từ BeatSpec + Memory + chronicleContext + styleInput.
6. Lưu SerialChapter; cập nhật NarrativeState (fingerprint + narrative_driven_state qua StoryEventExtractor/WorldMutationPolicy); ghi ChapterTelemetry.
7. Return SerialChapter.
