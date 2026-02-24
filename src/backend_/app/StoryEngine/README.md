# StoryEngine — Legacy / Experimental Layer

**Vị trí:** `app/StoryEngine/` (không nằm trong `app/Domains/`).

## Vai trò

StoryEngine là **engine mô phỏng cốt truyện ở mức world** (chapter-based pipeline: Physics → SeedSelection → UnifiedRule → FactionAction → Economic → Balancing → Metrics). State: WorldState, CharacterState, FactionState, Seed/InformationSeed.

## Ranh giới kiến trúc (WorldOS 2.0)

Theo [WORLDOS_2_FINAL_FORM_AND_LAB.md](../../docs/WORLDOS_2_FINAL_FORM_AND_LAB.md):

- **Saga** (Domains/Saga) điều phối **Universe** (tick qua UniverseRuntimeService) — **không** nằm trong StoryEngine.
- **Narrative/Serial** (Domains/Narrative) sinh chương qua LLM và SerialStoryService — **không** dùng pipeline StoryEngine.
- StoryEngine **không** thuộc Context Map chính (WorldContext → RuntimeContext → SagaContext). Nó là lớp song song, dùng cho:
  - Test (verify_operator_mode, verify_replay, verify_governance, verify_world_laws)
  - AI services (AIIntegrationService, DynamicWorldEventGenerator, IntelligentNPC, AIStoryGenerator, PredictiveAnalytics)
  - AIManagementController khi cần state/simulation ở tầng world

## Hai hướng chính danh

1. **A) AI sandbox (tách hoàn toàn)**  
   Chuyển thành `App/Experimental/StoryEngine`. Không thuộc Domain. Dùng cho thí nghiệm AI/test; không tham gia luồng Saga/Universe.

2. **B) Hợp nhất**  
   Coi như narrative pre-simulation hoặc civilization sandbox phục vụ Saga meta-evaluator. Khi đó cần contract rõ (input/output) và chỉ gọi từ Saga/Narrative qua interface.

Chưa chọn rõ A hay B thì StoryEngine vẫn là **legacy/experimental**; khi thêm tính năng mới ưu tiên luồng Saga + Universe + Narrative.

## Persistence

- **EventStore / OptimizedEventStore:** bảng `world_events` (timeline_id, chapter, type, payload).
- **OptimizedEventStore:** snapshot theo timeline_id/chapter (bảng `world_snapshots` nếu có).
- **ReplayEngine:** replay từ event store.

Không nhầm với snapshot runtime của Universe (civilization_snapshots, chronicles với universe_id).
