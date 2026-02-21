# Kế hoạch refactor toàn bộ logic Domain sang DDD (Tuzy)

> Mục tiêu: Di chuyển toàn bộ logic nghiệp vụ từ `app/Domains/*` vào `Tuzy\Domain\*` (và Application/Infrastructure). Sau khi xong, `app/Domains` có thể deprecate/xóa.

## Nguyên tắc

- **Một nguồn sự thật:** Logic domain chỉ nằm trong Tuzy (Domain, Application, Infrastructure).
- **Di chuyển từng bước:** Theo từng bounded context hoặc từng nhóm class (VO → Event → Service → Aggregate).
- **Giữ tương thích trong quá trình chuyển:** Có thể dùng alias/facade trong `App\Domains` trỏ tới Tuzy tạm thời (extends hoặc delegate), sau đó cập nhật callers và xóa alias.

## Bản đồ context

| App\Domains (hiện tại) | Tuzy (đã có / sẽ có) | Ghi chú |
|------------------------|----------------------|---------|
| World | World | Đã có Entity, Repository, CRUD + List. Cần thêm: VOs (EntropyScore, WorldLawProfile đã có), Events (ShockEvent), Aggregate/Service. |
| Runtime | Runtime | Đã có Universe CRUD. App\Domains\Runtime có Evaluation, UniverseRuntimeService. |
| Saga | Saga | Đã có Saga CRUD. App có SagaRunner, SagaWorld, Actions, Services. |
| Cosmology | Cosmology | Đã có UniverseStyle. App có Universe entity, WorldStateVector, Mathematics, Services. |
| Evolution | Evolution | Đã có EvolutionProfile. App có Engine, InfluencePipeline, Kernel. |
| Narrative | Narrative | Đã có NarrativeSeries. App có Planning, Services, LLM, Projection. |
| Vietnamese | Heroes | Đã có WorldHero. App có Models, Services (HeroBifurcation, Scoring, …). |
| Material | Material (mới) | Chưa có trong Tuzy. Nhiều file: Engine, State, Repositories, Analytics. |
| Character | Character (mới) | Aggregates, Services, ValueObjects. |
| Cosmic | Cosmic (mới) hoặc gộp Cosmology | ValueObjects, Services (PhaseEngine, EventEngine, …). |
| History, Genre, Faction, Time, … | Theo thứ tự ưu tiên | Mỗi context có thể tạo thư mục Tuzy\Domain\<Context>. |

## Phase 1 — Mở rộng context World (Tuzy đã có)

**Mục tiêu:** Đưa Value Objects, Domain Events, và dần các Service/Aggregate từ `App\Domains\World` sang `Tuzy\Domain\World`.

### 1.1 Value Objects

| VO (App\Domains\World) | Hành động | Trạng thái |
|------------------------|-----------|------------|
| EntropyScore | Tạo `Tuzy\Domain\World\ValueObject\EntropyScore`, App alias extend Tuzy | ✅ Done |
| WorldLawProfile | Đã có trong Tuzy | Done |
| Claim | Tuzy\Domain\World\ValueObject\Claim; App extends Tuzy | ✅ Done |
| PhysicsProfile | Tuzy\Domain\World\ValueObject\PhysicsProfile; App extends + Arrayable. ✅ Done |
| GeneVector | Tuzy\Domain\World\ValueObject\GeneVector; App extends. ✅ Done |
| (còn lại) | … | Pending |

### 1.2 Domain Events

| Event (App) | Hành động | Trạng thái |
|-------------|-----------|------------|
| ShockEvent | Tuzy\Domain\World\Event\ShockEvent; App = class_alias | ✅ Done |
| WorldLawUpdated, MaterialInjected, WorldDefined | Tuzy\Domain\World\Event\*; App extends + Laravel traits | ✅ Done |

### 1.3 Domain Services / Aggregate

- WorldAggregate (App) → Logic có thể chuyển dần vào Tuzy (entity mở rộng hoặc Tuzy\Domain\World\Service).
- Các service: ShockEventGenerator, WorldInitializer, EntropyCalculator (History), … → Port interface trong Tuzy, implementation có thể tạm delegate App.

### 1.4 Callers cần cập nhật (sau khi đủ VO/Event trong Tuzy)

- TickWorldAction, WorldTickCommand
- WorldAggregate, EloquentWorldRepository (App) → dùng Tuzy repository hoặc map
- CharacterSurvivalAggregate, SurvivalCheckEngine (sẽ dùng Tuzy\Domain\World\ValueObject\EntropyScore)

## Phase 2 — Mở rộng Runtime, Saga, Cosmology, Evolution, Narrative, Heroes

- **Runtime events:** UniverseTicked, UniverseForked, UniverseCollapsed → Tuzy\Domain\Runtime\Event; App extends + Laravel traits. ✅ Done.
- **Saga VOs:** ShockParams, CollapseProfile → Tuzy\Domain\Saga\ValueObject; App extends. ✅ Done.
- **Evolution VO:** BranchEvent → Tuzy\Domain\Evolution\ValueObject; App extends. ✅ Done.
- **Narrative event:** ChapterGenerated → Tuzy\Domain\Narrative\Event; App extends + Laravel. ✅ Done.
- **Narrative VO:** StoryEvent (Bridge/DTO) → Tuzy\Domain\Narrative\ValueObject; App extends, clamp severity in App ctor. ✅ Done.
- **Cosmology VOs:** PhaseSignal, ConstraintProfile → Tuzy\Domain\Cosmology\ValueObject; App extends. ✅ Done.
- **Saga VO:** SagaEvaluationReport → Tuzy\Domain\Saga\ValueObject; App = class_alias. ✅ Done.
- **Character VOs:** SurvivalProbability, NarrativeWeight, RiskFactors → Tuzy\Domain\Character\ValueObject; App extends. ✅ Done.
- **Conflict VO:** ConflictSeed → Tuzy\Domain\Conflict\ValueObject; App extends. ✅ Done.
- **Intelligence VO:** IntelligenceSource → Tuzy\Domain\Intelligence\ValueObject; App extends. ✅ Done.
- **Intelligence:** **IntelligenceType** (enum) → Tuzy\Domain\Intelligence\ValueObject; App = class_alias. ✅ Done.
- **Intelligence VO:** **IntelligenceReport** → Tuzy\Domain\Intelligence\ValueObject; App extends. Repository arrayToReport dùng Tuzy IntelligenceSource (type, id, reliability). ✅ Done.
- **Narrative VOs:** MemorySnapshot, PressureSignal, **BeatSpec**, **DefaultOutcome**, **StoryOutcomeDTO** → Tuzy\Domain\Narrative\ValueObject; App extends. ✅ Done.
- **Saga VO:** SagaEvaluationInput → Tuzy\Domain\Saga\ValueObject; App extends. ✅ Done.
- **Character VO:** **SurvivalTrend** → Tuzy\Domain\Character\ValueObject; App dùng Tuzy (SurvivalCheckEngine import Tuzy, xóa class trùng). ✅ Done.
- **Character VO:** **SurvivalResult** → Tuzy\Domain\Character\ValueObject (DTO: characterId, probability, survived, reason); App SurvivalResult::toTuzy() trả về Tuzy DTO. ✅ Done.
- **Material:** **MaterialState** (enum) → Tuzy\Domain\Material\ValueObject; App = class_alias (file App trước đó không tồn tại, MaterialInstance dùng enum). ✅ Done.
- **Cosmic VO:** **Attractor** → Tuzy\Domain\Cosmic\ValueObject; App\Domains\Cosmic\ValueObjects\Attractor = class_alias. (Lưu ý: App\Domains\Cosmology\ValueObjects\Attractor là class khác — centroid/WorldStateVector.) ✅ Done.
- **CoreTruth VOs:** **Axiom**, **CoreTruth** → Tuzy\Domain\CoreTruth\ValueObject; App extends. ✅ Done.
- **World:** **WorldHealthStatus** (enum) → Tuzy\Domain\World\ValueObject; App = class_alias. ✅ Done.
- **WorldManagement VO:** **HealthResult** → Tuzy\Domain\WorldManagement\ValueObject; App extends. ✅ Done.
- **Faction VOs:** **FactionMemory**, **IdeologyVector**, **PersonalityVector** → Tuzy\Domain\Faction\ValueObject; App extends. ✅ Done.
- **Genre VO:** **GenrePromptCapsule** → Tuzy\Domain\Genre\ValueObject; App extends. ✅ Done.
- **Epistemology VO:** **EpistemicIndex** → Tuzy\Domain\Epistemology\ValueObject; App extends. ✅ Done.
- **Cosmology VOs:** **EpistemicVector**, **OntologyVector**, **CivilizationVector**, **EnergyVector** → Tuzy\Domain\Cosmology\ValueObject; App extends. ✅ Done.
- **Narrative VOs:** **StorySlice**, **Intent** (Dialogue), **EmotionState** (Character), **StateSnapshot** (Timeline) → Tuzy\Domain\Narrative\ValueObject; App extends. ✅ Done.
- **Cosmology VO:** **WorldSeed** → Tuzy\Domain\Cosmology\ValueObject; App extends, fromArray override để giữ uniqid('seed_') khi seed_hash null. ✅ Done.
- Heroes: thêm khi có event/VO đơn giản.

## Phase 3 — Context mới (Material, Character, Cosmic, …)

- Tuzy\Domain\Material: MaterialState enum. ✅ Done.
- Tuzy\Domain\Cosmic: Attractor VO. ✅ Done.
- MaterialInstance (App) vẫn dùng MaterialState qua alias; migrate full VO sau nếu cần.

## Phase 5 — Tất cả app/Domains có mặt trong Tuzy (2026-02-21)

**Mục tiêu:** Mỗi thư mục con của `app/Domains` có ít nhất một type tương ứng trong `Tuzy\Domain\*` (VO, Event, Entity, hoặc Interface). Không đụng API/Controller.

**Đã thêm Tuzy Domain cho các context trước đó chưa có:**

| app/Domains | Tuzy\Domain\* (mới) |
|-------------|----------------------|
| Genesis | Genesis\ValueObject\GenesisSeedSpec |
| Governance | Governance\Event\StyleProposalCreated |
| Mutation | Mutation\Contracts\WorldMutationContract |
| Power | Power\ValueObject\PowerStage (enum); App enum = class_alias Tuzy |
| Reader | Reader\ValueObject\InteractionKind (enum) |
| Replay | Replay\ValueObject\ReplayCursor |
| Social | Social\ValueObject\AddressingScope (enum); App enum = class_alias Tuzy |
| Historian | Historian\ValueObject\HistorianScope |
| CognitiveKernel | CognitiveKernel\ValueObject\ArchetypeSnapshotRef |
| Vietnamese | Vietnamese\ValueObject\HeroOrigin (Heroes đã có WorldHero) |
| CivilizationDynamics | CivilizationDynamics\ValueObject\CivilizationResidual |
| WorldEvolution | WorldEvolution\Event\WorldEvolved |
| Institution | Institution\Entity\Institution, Institution\Repository\InstitutionRepositoryInterface |
| IPEngine | IPEngine\ValueObject\PromptScope (enum) |
| SocialSimulation | SocialSimulation\ValueObject\LegitimacyResult |

**Backward compatibility:** `App\Domains\Power\Enums\PowerStage` và `App\Domains\Social\Enums\AddressingScope` là class_alias trỏ tới Tuzy; file App chỉ còn gọi class_alias + @deprecated.

**Kiểm tra:** `php artisan test tests/Unit/Tuzy/` → 153 tests, 424 assertions OK.

## Phase 4 — Dọn dẹp

- Các class App đã chuyển sang Tuzy: dùng **extends** hoặc **class_alias**, đánh dấu **@deprecated**.
- Khi không còn reference tới class App có thể xóa file App (giữ alias nếu cần cho type-hint cũ).
- Chạy **tests/Unit/Tuzy/** để xác nhận domain Tuzy: **128 tests OK**.
- Test suite `tests/Unit/Cosmic/` hiện fail do thiếu nhiều class (AttractorIncarnation, WaveInterferenceEngine, … trong namespace Cosmic vs Cosmology) — không do thay đổi migration.

### Checklist dọn dẹp (đã tiến hành 2026-02-20)

1. ~~Tìm toàn bộ `use App\Domains\…`~~ — Giữ `use App\Domains\…` cho Aggregate/Service chưa chuyển; VO/Event đã alias nên type-hint vẫn trỏ Tuzy. Không đổi hàng loạt để tránh vỡ.
2. ~~Xóa nội dung class trong file App chỉ còn alias~~ — Đã xác nhận: IntelligenceType, ShockEvent, SagaEvaluationReport, MaterialState, Cosmic Attractor đều là file alias-only.
3. ~~Chạy test~~ — `php vendor/bin/phpunit tests/Unit/Tuzy/`: **135 tests, 374 assertions OK.**
4. ~~Cập nhật tài liệu~~ — BACKEND_OVERVIEW.md đã thêm **§0. Tuzy — Nguồn sự thật domain (DDD)** (vị trí, danh sách VO/Event, test path, link plan).

---

## Tiến độ (cập nhật khi làm)

- **2026-02-20:** World; Runtime; Saga; Evolution; Narrative (StorySlice, Intent); Cosmology (EpistemicVector); Character; Conflict; Intelligence; Material; Cosmic; CoreTruth; World (WorldHealthStatus); WorldManagement (HealthResult); Faction; Genre; Epistemology. App backward compat (extend/class_alias + @deprecated). **Tuzy: 153 tests OK.**
- **2026-02-21:** Phase 5 — Tất cả app/Domains có Tuzy: Genesis, Governance, Mutation, Power, Reader, Replay, Social, Historian, CognitiveKernel, Vietnamese, CivilizationDynamics, WorldEvolution, Institution, IPEngine, SocialSimulation. PowerStage và AddressingScope: App = class_alias Tuzy. **Tuzy: 153 tests, 424 assertions OK.**
