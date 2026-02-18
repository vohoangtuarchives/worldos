# Causality Bridge (Narrative → World)

Hai engine **Narrative** (LLM sinh chương) và **World Simulation** (tick, shock event) trước đây chạy song song, không liên kết nhân quả. Bridge này nối **sự kiện trong chương** với **trạng thái thế giới** và đưa state đó vào prompt chương sau.

## Luồng

1. **Sau khi LLM trả chapter**  
   `SerialStoryService` gọi `projectNarrativeEventsToWorld(seriesId, content)`.

2. **Story Event Extractor**  
   Trích sự kiện có cấu trúc từ nội dung chương (rule-based: từ khóa → `type` + `severity`).  
   Ví dụ: "dark magic", "shadow mark", "corruption" → `magic_corruption`; "invasion", "killed" → `invasion` / `violence`.

3. **World Mutation Policy**  
   Ánh xạ loại sự kiện → delta trên **narrative_driven_state** (shadow_presence, magic_stability, threat_level).  
   State được clamp 0–1 và lưu vào `narrative_state.narrative_driven_state`.

4. **Prompt chương sau**  
   Khi build `chronicleContext`, thêm `current_world_state_narrative` = chuỗi dạng:  
   `Current world state (narrative): shadow_presence=0.33, magic_stability=0.67, threat_level=0.2`  
   → LLM thấy thế giới đã thay đổi theo chương trước.

## File chính

| Thành phần | File |
|------------|------|
| DTO sự kiện | `app/Domains/Narrative/Bridge/DTO/StoryEvent.php` |
| Contract extractor | `app/Domains/Narrative/Bridge/Contracts/StoryEventExtractorInterface.php` |
| Rule-based extractor | `app/Domains/Narrative/Bridge/Extractor/RuleBasedStoryEventExtractor.php` |
| Chính sách mutation | `app/Domains/Narrative/Bridge/WorldMutationPolicy.php` |
| Serialize cho prompt | `app/Domains/Narrative/Bridge/StateSerializerForPrompt.php` |
| Gọi trong flow | `SerialStoryService::projectNarrativeEventsToWorld()`, và khi build `chronicleContext` |

## Mở rộng

- **Thêm pattern**: Sửa `RuleBasedStoryEventExtractor::patterns()` (hoặc đưa vào config).  
- **Thay bằng LLM nhỏ**: Implement `StoryEventExtractorInterface` gọi model nhỏ để extract event có cấu trúc (type, location, symbol, severity).  
- **Đẩy entropy sang World (worlds table)**: Trong `projectNarrativeEventsToWorld` (hoặc listener), nếu series có `world_id` (qua universe), có thể tăng `World::entropy` theo tổng severity của events để World tick và shock event phản ứng theo narrative.  
- **Kafka**: Publish `StoryEventDetected` sau extract; World engine subscribe và mutate (phù hợp Evolution Kernel async).

## Lưu ý

- **Một series ↔ một narrative state**: `narrative_state` theo `narrative_series_id`; narrative_driven_state là state “thế giới truyện” cho series đó.  
- **World tick (world_id 2,3,4,5,6)**: Nếu mỗi series gắn một `world_id` (hoặc universe_id → world_id), nên chỉ tick world tương ứng series đang viết để tránh compute thừa và để có thể đồng bộ entropy/shock với narrative sau này.

## Narrative → Universe: Pressure (WorldOS 2.0 Clean)

Khi bật ảnh hưởng narrative lên runtime, có hai hướng:

1. **Hiện tại (narrative_affects_universe = true)**: NarrativeToUniverseAdapter map events → StoryOutcomeDTO và gọi UniverseMutationService.commit() → thay đổi vector (có magnitude limit). Một cửa, nhưng narrative đang mutate vector.
2. **Mục tiêu Clean (narrative_affects_via_pressure = true, chưa implement)**: Narrative không mutate vector. Chapter → EventExtractor → PressureSignal → Runtime/PhaseEngine.injectPressure() → tăng contradiction/pressure → PhaseEngine đánh giá → nếu vượt ngưỡng thì collapse/reorg. Narrative chỉ tạo điều kiện cho phase transition.

Config `mutation.narrative_affects_via_pressure` dự kiến cho path (2). Xem WORLDOS_2_CLEAN_ARCHITECTURE.md mục VI.
