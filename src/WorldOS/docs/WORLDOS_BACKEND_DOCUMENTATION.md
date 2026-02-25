# WorldOS v0.1.0 — Tài liệu Backend chính thức

**Tên sản phẩm:** WorldOS v0.1.0  
**Phiên bản tài liệu:** 0.1.0  
**Cập nhật:** 2026-02-23  
**Ghi chú phiên bản:** WorldOS v0.1.0 là phiên bản chính thức kế thừa WorldOS 6; tài liệu này là văn bản mô tả kiến trúc và vận hành backend duy nhất.

Tài liệu tổng hợp toàn bộ kiến trúc, domain, governance và vận hành backend WorldOS. Nội dung quan trọng (Constitution, Foundation Rules, ADR Unified Myth, Kiến trúc V3/V4, Clean Architecture & Final Form) được **biên soạn nguyên văn hoặc đầy đủ**; các phần khác tóm tắt hoặc tham chiếu nội dung đã gộp.

---

## Cách đọc

- **Thứ tự gợi ý:** 01 → 02 → 03 → 04 → 05 → 06 → 07 → 08, sau đó 09–14 theo nhu cầu.
- **Authority:** Chỉ **Universe** mang runtime (tick, state). **World** là rule container. **Saga** chỉ orchestrate.
- **Snapshot-first:** Mọi tiến hóa ghi `universe_snapshots`; rollback/fork/clone từ snapshot.
- **AI:** Đánh giá và đề xuất mutation qua kernel; không sửa `state_vector` trực tiếp.

## Vị trí code chính (backend)

| Thành phần | Vị trí |
|------------|--------|
| Runtime | `App\Domains\Runtime\UniverseRuntimeService` |
| Snapshot | `App\Domains\Cosmology\Repositories\UniverseSnapshotRepository` |
| Saga orchestrator | `App\Domains\Saga\Services\SagaService` |
| Kernel | `App\Domains\Evolution\Kernel\WorldEvolutionKernel` |
| AI / Metrics | `App\Domains\Runtime\Evaluation\*` (MetricsExtractor, UniverseEvaluatorInterface, DecisionEngine) |

---

# 01 — Kiến trúc tổng quan

## 1.1 WorldOS V3: Core Philosophy

WorldOS V3 là **Event-Driven Macro-Simulation Engine**. Hệ thống mô hình hóa sự thăng trầm của nền văn minh qua **Physics** (Entropy, Order) và **Materials** (Ideas, Tech), không mô phỏng từng agent (Micro-Sim).

- **Từ Agent-Based sang Statistics-Based:** Thay vì hàng nghìn agent, mô phỏng "Health" của nền văn minh (Inequality, Innovation, Trauma).
- **Từ World-Centric sang Universe-Centric:** `World` là Blueprint (Genotype). `Universe` là instance đang chạy (Phenotype).
- **Từ Loop-Based sang Resonance-Based:** Sự kiện narrative (Heroes, Wars) là *resonance* khi Physics đạt ngưỡng (vd. High Entropy → Spawn Rebel).

## 1.2 Unified Myth World Engine (ADR-002)

- **Immutability:** Lịch sử (`WorldEvent`, `WorldScar`) append-only.
- **Causal Consistency:** DAG cho time travel; không viết lại lịch sử, chỉ fork.
- **Living Characters:** Character là aggregate có state (Memory, Emotion, Goal).

Các lớp: Foundation (Physics) → Mythos (Belief, WorldMyth) → Narrative (Observer, NarrativeService) → Soul (Character Core) → Brain (Dialogue Engine) → Spine (Timeline DAG).

## 1.3 System Architecture (V3)

User/API → SagaService → UniverseRuntime → WorldEvolutionKernel (BasePhysicsEngine, MaterialEngine, UniverseSnapshot). Kernel dispatch WorldTicked → Listeners (vd. CheckHeroSpawningListener) → spawn WorldHero.

## 1.4 Ba trụ cột (V3)

- **Physics:** BasePhysicsEngine — Entropy, Order, Inequality, Innovation.
- **Materials:** MaterialEngine — Active Concepts tạo áp lực lên Physics.
- **Resonance:** Listeners spawn Narrative Agents/Events khi đạt ngưỡng.

## 1.5 WorldOS V4 — Tầm nhìn

- **World (Blueprint):** Bất biến; luật, gene vector. **Saga (Chronicle):** Dòng thời gian tường thuật. **Universe (Timeline/Run):** Nhánh nhân quả đang chạy.
- State Vector `[-1, 1]`; Bifurcation qua Jacobian/eigenvalue; Lyapunov Stability.
- Continuous Pressure (graph topology); Replicator Dynamics; Epoch Reset khi vượt World Bounds.

So sánh: V3 = Event-driven, tuyến tính; V4 = Nonlinear Math, chu kỳ sinh diệt, Simulation Instance chủ động.

---

# 02 — Core Concepts & Domains

## 2.1 World, Universe, Saga

- **World (Genotype):** Blueprint. Luật vật lý, hằng số, Seed gốc. Read-Mostly. Model: `App\Models\World`. 1 World → N Universe; thuộc Saga qua `saga_worlds`.
- **Universe (Phenotype):** Runtime Instance. Age, Entropy, State Vector. Model: `App\Models\UniverseModel`. `universes.world_id` → World (NOT NULL).
- **Saga:** Session, theo dõi tiến trình Universe(s). Saga → N SagaWorld → World.

## 2.2 World vs Universe (Ranh giới domain)

- **World** = Aggregate Root. Định danh thế giới, genesis snapshot, constraint (Anchor + ConstraintProfile). Một World có nhiều Universe (nhiều timeline).
- **Universe** = Runtime Instance. `state_vector`, `age`, `parameters`; thuộc World qua `world_id`. Tick và fork đều gắn Universe.
- **Parameters (Universe):** `random_seed`, `constraint_profile`, `anchor_type`, `ancestors`/`event`/`branch_type` khi fork.

## 2.3 State Vector

`WorldStateVector`: entropy (0→1), order, innovation, cohesion, inequality, trauma.

## 2.4 Snapshots

Mỗi Tick → một dòng **UniverseSnapshot**. Bảng: `universe_snapshots`. Lợi ích: Rollback, Forking, AI Analysis.

## 2.5 Domain Architecture (Chốt)

World = Aggregate Root (materials, governance, scars, attractors, evolution). Universe = Runtime Instance (current_tick, fork lineage, chronicle). **Universe nằm trong World.**

---

# 03 — Simulation Loop

## 3.1 Heartbeat

`php artisan saga:advance-v3 --ticks=5` (có thể chạy mỗi phút qua Scheduler).

## 3.2 Luồng thành phần

1. **Entry:** `SagaService::runBatchWithEvaluation($saga, $ticks)` → load Universe, gọi UniverseRuntimeService.
2. **Runtime:** `UniverseRuntimeService::advance($universeId)` → load UniverseModel, kiểm tra World HALTED, delegate `evolutionEngine->applyTick()`.
3. **Kernel:** `WorldEvolutionKernel::tickUniverse()` — BasePhysicsEngine::evolve(), MaterialWorldBridge::processTick(), StateLoader::saveVector().
4. **Events:** WorldTicked (World, StateVector).
5. **Resonance:** CheckHeroSpawningListener — Entropy > 0.8 → REBEL; Order > 0.9 → REFORMER; tạo WorldHero.

## 3.3 Hai kernel evolution

- **Cosmology EvolutionKernel:** Tick Universe không world_id (deprecated khi mọi Universe có world_id).
- **WorldEvolutionKernel:** Tick World/Saga; **bắt buộc** khi tick Universe có world_id (luật + preset + influences).

## 3.4 Luồng chuẩn

Universe luôn thuộc World (world_id NOT NULL). Universe chỉ tạo từ World. Tick Universe có world_id bắt buộc qua World evolution.

## 3.5 Commands

```bash
php artisan db:seed --class=WorldSeeder
php artisan world:analyze {world_id}
php artisan world:fork {world_id} {tick} "New Timeline"
```

---

# 04 — Physics Engine & Cosmology

## 4.1 Cosmology Domain

`App\Domains\Cosmology`: Physics (Entropy, Order), Attractors (narrative gravity), Scars (inertia).

## 4.2 BasePhysicsEngine

- **Entropy Growth:** `dEntropy = (Inequality² * 0.05) + (Trauma * 0.03) - (Innovation * 0.02)`.
- **Revolution Risk:** `dTrauma = (Inequality > 0.7) ? +0.05 : -0.01`.
- **Collapse:** Entropy > 0.85 → CRITICAL, giảm mạnh dOrder.

## 4.3 Attractors & Bifurcation

Attractors (vd. Cyberpunk Dystopia, Magical Feudalism). Bifurcation khi vector bất ổn; BifurcationManager chọn Attractor; Incarnation = Current Truth cho Era tiếp theo.

## 4.4 World Scars (Inertia)

WorldMyth decay hoặc CosmicEvent thảm họa → WorldScar. Scars thêm Inertia vào WorldStateVector; world khó thay đổi cho đến khi lực lớn phá vỡ.

## 4.5 Evolution

BasePhysicsEngine: physics thuần. WorldEvolutionKernel: inject BasePhysicsEngine + StructuralMutationEngine; PresetDescriptor::fromWorld. CosmologyRepository: world_id bắt buộc; getRuntimeStateForWorld(world_id).

---

# 05 — Narrative Engine (Resonance)

## 5.1 Physics Drives Story

Mô phỏng điều kiện (vd. Inequality = 0.9, Trauma = 0.8) → hệ spawn REBEL_LEADER; tương tác trở thành cốt truyện.

## 5.2 Resonance Listener

`CheckHeroSpawningListener` (WorldTicked). Ngưỡng: Entropy > 0.8 → REBEL_LEADER; > 0.9 → SAVIOR; Order > 0.9 → REFORMER; > 0.95 → PHILOSOPHER_KING; Cohesion < 0.3 → CULTURAL_HERO.

## 5.3 Reality Narrator (LLM)

`RealityNarrator`: sự kiện lớn → LLM sinh mô tả văn xuôi từ Physics Vectors (AIAgentContext).

## 5.4 Narrative / Serial (Domain)

`App\Domains\Narrative/`: NarrativeSeries, SerialChapter, NarrativeState, NarrativeArcOutline, StoryBible, ChapterTelemetry. generateNextChapter, DigestArcAction, BatchGenerateChaptersJob, SerialArcPlanner. NarrativeSeries.universe_id (nullable). Ảnh hưởng Universe chỉ qua UniverseMutationService.

## 5.5 Mutation

`App\Domains\Mutation/`: Cửa duy nhất commit cốt truyện vào Universe (UniverseMutationService). Arc completion dùng service này. OutcomeQuantizer, MutationMapper, MutationLimiter, InertiaApplier; dispatch UniverseMutationCommitted.

---

# 06 — Domains & Backend

## 6.1 Tuzy (DDD)

`src/Tuzy/`: Value Objects, Domain Events, Entities, Application Handlers. `app/Domains/*` extends/class_alias → Tuzy; App class @deprecated. Test: `tests/Unit/Tuzy/`.

## 6.2 Ba Bounded Context

**WorldContext** (Core) → **RuntimeContext** → **SagaContext**. Events: WorldDefined, WorldLawUpdated, MaterialInjected → Runtime; UniverseTicked, UniverseForked, UniverseCollapsed → Saga.

## 6.3 Các domain (tóm tắt)

World, Runtime, Cosmology, Evolution, Saga, Narrative/Serial, Mutation, Vietnamese (origin_type), Conflict, Faction, Material, Cosmic (Snapshots), Replay/Snapshot, Intelligence, Genesis, StoryEngine (legacy, app/StoryEngine/).

## 6.4 Models & DB chính

worlds → universes (world_id), saga_worlds, institutions, scars, world_myths, material_instances, chronicles, cosmic_snapshots, world_snapshots_v2, governance_logs. universes → cosmic_factions, fleets, epochs, civilization_snapshots, narrative_series.universe_id (nullable).

## 6.5 Ba boundary tuyệt đối

UniverseRuntimeService không gọi BasePhysics trực tiếp. Saga không tick World (tick Universe). Narrative không ghi Universe trực tiếp (chỉ qua UniverseMutationService).

---

# 07 — Context Map & Events

## 7.1 Bounded contexts

WorldContext (Evolution, Materials, Governance) upstream → RuntimeContext (Universe, tick, chronicle) → SagaContext (Narrative, canonize). Runtime nhận events từ World; Saga subscribe Runtime.

## 7.2 Event topology

- **World-side:** WorldDefined, WorldLawUpdated, MaterialInjected → Runtime react. `App\Domains\World\Events\*`.
- **Runtime-side:** UniverseTicked, UniverseForked, UniverseCollapsed → Saga react. `App\Domains\Runtime\Events\*`. Không có UniverseLawUpdated. UniverseTicked dispatch bởi UniverseRuntimeService sau mỗi tick.

## 7.3 Aggregate ownership

World không duplicate logic vào Universe. World freeze → Universe không được tick.

---

# 08 — Governance (biên soạn đầy đủ)

## 8.1 WORLD ENGINE CONSTITUTION

- **Article I – World Law:** World Law Profile là luật tối cao. World Law thắng; nếu mâu thuẫn simulation dừng.
- **Article II – AI:** AI không tự sửa luật, tự fork, tự kill world. Mọi output: claim, validation, audit.
- **Article III – Human:** Mọi hành động tối thượng có audit, justification. Kill World không thể đảo ngược.
- **Article IV – Incident:** Mọi sự cố ghi nhận; không resume khi chưa post-mortem.
- **Article V – Fork:** Fork hợp lệ khi có lý do, post-mortem, governance approval. Fork là bảo tồn, không trốn tránh.
- **Article VI – Memory:** Event không xoá; incident không che; audit tồn tại lâu dài.

## 8.2 World Foundation Repository (WFR)

WFR = IMMUTABLE trong runtime. Primitive chỉ add (theo version), không sửa nghĩa, không xoá. World tham chiếu primitive theo version. Mục tiêu: khóa tầng nền khỏi AI “bịa” vật liệu ban đầu. **AFR v1.0 — Architecture Freeze Record:** VERSION World Engine v1.0 | FREEZE 2026-02-10 | FROZEN. Definition of Freeze: (1) Đóng ranh giới quyền lực; (2) Đóng luồng dữ liệu — Event Sourcing duy nhất; (3) Simulator Loop bất biến. Immutable Pillars: WorldLawProfile, Claim Model, Kill/Fork chỉ Incident+Approval; Event Sourcing → Replay determinism. Non-Goals: Real-time MMO; Player-Controlled Law; AI Self-Modifying Governance. Allowed: Performance, UI/UX, Observability, Content. **ADR-0008 AI Ontology Contract:** AI không tạo ontology mới; chỉ instantiate từ primitives. PrimitiveGuard (input), AIResponseValidator (output). Hard rules: All concepts MUST map to Primitive IDs; Unknown = ERROR. **World Diversity Engine:** 3 primitive types (Axis, Tension, Constraint); 3 rule types (Compatibility, Tension, Emergence).

## 8.3 Seed Governance (nguyên văn)

Seed = nguồn xung lực có kiểm soát; không phá world nhưng vẫn tạo emergence. **Nguyên tắc:** World chịu được ít seed hơn bạn nghĩ; seed không cộng tuyến; 2 seed mạnh ≠ story gấp đôi (thường nhiễu, sụp trật tự). **Limits:** Personal max 3 (không cùng type), Regional 2, Global 1. **Hard rules:** ❌ Không >1 Global seed; ❌ Không spawn khi WorldHealth đỏ/đen hoặc SAFE MODE. **Collision:** Priority Global > Regional > Personal; seed thấp hơn không activate hoặc delay. **Seed Type → World Law:** CONFLICT (Power ceiling, no instant domination; Validator: Claim magnitude, Escalation rate). DISCOVERY (Knowledge≠power; Time-to-adoption, Faction access equality). TRAGEDY (No mass extinction; Casualty bounds). BLESSING (Temporary/costly; Duration cap). MYSTERY (Mystery unresolved; Answer suppression). PROPHECY (Prophecy≠truth; AI certainty clamp). **Lifecycle:** DORMANT → ACTIVE (khi tick trigger hoặc Faction nhận thức) → ACTIVE có thể Escalate/Spread/Be ignored → EXHAUSTED (mục tiêu đỉnh / World không phản ứng / superseded). ❌ Reactivate EXHAUSTED; ❌ Reset outcome; xung lực mới = seed mới. **Operator:** Được Delay activation, Force exhaust; không đổi type/dimension/description. **Alerts:** SEED_OVERLOAD, SEED_LAW_VIOLATION, PROPHECY_CERTAINTY_BREACH. *"A seed may start a story, but it must never finish one."*

## 8.4 Myth & Scar Governance (nguyên văn)

**Myth = Crystallized belief pattern** đạt critical mass. Formation: Nhiều Belief (cùng theme) → Strength tích lũy → Threshold → Myth emerges. **States:** ACTIVE (đang lớn), DECAYING (belief yếu đi), MERGED (gộp myth khác). **Governance:** SEMI-MUTABLE — được decay/merge; không xoá tay, không boost strength. *"Myths reflect collective belief. Operators cannot fabricate faith."*

**Scar = Permanent consequence** của event. Formation: Critical Event → ScarFactory → WorldScar (IMMUTABLE). Source Event link `world_events`; Weight 1–10. **Governance:** IMMUTABLE — không heal, không forget, không undo; chỉ xem/analyze. Code: `updating`/`deleting` throw. *"History cannot be rewritten. Consequences are permanent."*

**Operator:** Myth — xem emergence, phân tích cluster (AI), track decay; không tạo/force merge/boost. Scar — xem history, phân tích cluster; không delete/edit weight/heal. **AI:** MythOvergrowthAnalyzer (alert MYTH_OVERGROWTH), ScarClusterAnalyzer (scar tích tụ nguy hiểm).

**Myth Threshold:** Khi nào event đủ mạnh để không bị quên? **4 trục:** Impact (scope, power structure, tri thức) 0–1; Irreversibility (có quay lại được không) 0–1; Narrative Compression (dễ symbol hoá, "The First Silence") 0–1; Recurrence Potential (lặp lại world khác) 0–1. **Công thức:** MythScore = Impact×0.35 + Irreversibility×0.30 + Compression×0.20 + Recurrence×0.15; **MythScore ≥ 0.7 → Myth Trace**. Myth Strength: Level 1 (0.2–0.4) → 2 (0.4–0.7) → 3 (≥0.7); không nhảy cấp. Decay/Manipulation/Conflict/Schism và **Civilization Collapse Legacy** (Migrated, Dormant, Archetypal, Lost; Violent/Exhaustion/Ideological) theo thiết kế governance.

## 8.5 World Trace Repository (WTR) & Implementation Strategy

**WTR = ký ức lịch sử của toàn bộ hệ thống.** Trace = dấu tích đã xảy ra khi world sinh ra, vận hành, kết thúc. Trace KHÔNG thay đổi primitive hay can thiệp world đang chạy; CHỈ ghi nhận, trừu tượng hoá, làm giàu kho lịch sử. Vị trí: World Simulation → World Events → Trace Extractor → WTR → Seed Bias / Myth / Pattern Reference (không nằm trên critical runtime).

**4 trace types:** (1) **Pattern Trace** — mẫu hình (Faith-Dominant Collapse, Centralized Power Stagnation); signature + outcome + confidence. (2) **Myth Origin Trace** — nguồn myth (Silent Reformation, First Forbidden Spell); origin_event, archetype, echo_strength. (3) **Failure Trace** — world chết có giá trị (Economic Deadlock, Infinite War Loop); cause, lesson. (4) **Stability Trace** — thế giới ổn định; điều kiện, duration. **Governance law:** Trace chỉ add; không sửa/xoá. WFR = what worlds CAN be; WTR = what worlds HAVE been.

**Ba trụ cột (cả 3, thứ tự đúng):** (1) **Trace → Seed Bias Engine** — bản năng tiến hoá; nhớ điều gì dẫn collapse/thịnh vượng; không cấm, nghiêng xác suất. (2) **Trace → Myth Propagation** — tiềm thức tập thể; myth bóp méo, thần thoại hoá; liên kết mềm giữa world. (3) **Trace → Governance Dashboard** — ý thức phản tư; con người nhìn như sử gia. **Thứ tự triển khai:** Phase 1 Trace → Governance Dashboard; Phase 2 → Seed Bias Engine; Phase 3 → Myth Propagation. DO: Trace schema trước, extractors sau, dashboard sớm. DON'T: Dùng trace điều khiển world trực tiếp; cho phép sửa/xoá trace.

## 8.6 Saga Runner, Historian, Archetype, Human-in-the-Loop

- **SAGA_RUNNER:** Orchestrate nhiều World tuần tự; myth legacy transfer; WorldSeedGenerator, MythLegacyExtractor, SagaObserver, SagaArchive. `php artisan saga:run --worlds=5 --archetypes=... --carry=0.6`.
- **HISTORIAN_MODE:** Đọc lịch sử nổi lên, không chỉ đạo. 4 layers: Chronicle, Pattern, Bias, Counterfactual.
- **Archetype–Economy–Power:** Archetype legitimizes economy & power; legitimacy formula. **Archetype Drift/Mutation:** Drift từ repetition/trauma/power/absence; mutation chỉ khi collapse/paradox/repeated failure.
- **HUMAN_IN_THE_LOOP:** Cho phép: Seeding Bias, Pressure Injection, Selection. Cấm: Edit myth, set archetype weight, choose outcome, rewrite history. **World OS:** 4-layer architecture; product strategy (Writer vs Researcher tiers).

## 8.7 Governance as Emergent (Physics of Power)

Governance là output của simulation, không phải setting. State Vector định nghĩa regime: Order (Authority), Legitimacy, Elite Cohesion, Inequality. Regime types emergent: Imperial/Monarchy (Order > 0.8, Inequality > 0.7, Elite Cohesion > 0.6); Republic/Democracy (Order < 0.6, Inequality < 0.4, Legitimacy > 0.7); Warlord (Order > 0.5, Elite Cohesion < 0.3, Legitimacy < 0.2). **Primitives (WFR)** = "Tech Tree" — vd. REPUBLIC yêu cầu CITIZENSHIP. **Cosmic Factions:** persistent Players/Egregores; capture/lose regime theo Elite Cohesion.

---

# 09 — Material System

Material = Active Concept / Meme: sống, tiến hóa, tạo áp lực. Ontology: Physical, Institutional, Symbolic, Behavioral. Lifecycle: Dormant → Active → Decayed/Obsolete. Pressure: Rice Farming → Population, Stability; Industrialization → Wealth, Entropy, Inequality; Secret Police → Order↑, Trauma↑, Legitimacy↓. Evolution: DAG; trigger Innovation > 0.8 hoặc Time > 500y; path Oral Tradition → Writing → Printing → Digital. Seeding: AdvancedMaterialSeeder theo Origin (Vietnamese: Wet Rice, Village Autonomy, Ancestor Worship; Western: Wheat, Feudal Hierarchy, Monotheism). Code: `app/Domains/Material/`, MaterialWorldBridge, MaterialArchetypeCoupler.

---

# 10 — Genre System (nguyên văn)

Genre không chỉ là nhãn; là **lớp cấu hình nền** cho cả *Physics* (Simulation) và *Narrative* (Representation). Genre = "Lens" biến World Evolution Kernel thành trải nghiệm chủ đề cụ thể; nối raw data (WorldStateVector) với story người đọc (Novel).

**Dual Role:** (1) **Physics:** Materials (Spirit Qi, Radiation), Progression Rules (Cultivation Levels, Tech Trees), World Constraints ("Mortal cannot harm Immortal", "Death is final"). (2) **Narrative:** Vocabulary Maps (School→Sect, Energy→Mana), Narrative Prompts vào LLMChronicler, Event Catalogs.

**GenreDefinition interface:** `key()`, `materials()`, `progression()`, `vocabulary()`, `getNarrativePrompt()`.

| Genre Key | Display | Physics Focus | Narrative Style |
| xianxia | Xianxia (Cultivation) | Qi Accumulation, Hierarchy, Immortality | Grandiose, Ruthless, "Dao", "Tribulation" |
| survival | Apocalypse | Scarcity, Attrition, Crafting | Gritty, Desperate, "Fragility", "Ruins" |
| scifi | Hard Sci-Fi | Innovation, entropy-management | Analytical, Technical, "Protocol", "Quantum" |

**IP Factory & Feedback:** Universe evolves → WorldEvent; LLMChronicler + Genre::getNarrativePrompt() → raw event thành văn ("War started" → "The Great Sect War began under the blood moon."). Canonize → NarrativeFeedbackService: Xianxia impact HIERARCHY, SPIRIT_QI; Cyberpunk INEQUALITY, INNOVATION → story thay đổi trajectory. **Material injection:** Xianxia: SPIRIT_QI, ALCHEMICAL_PILL; Survival: SCRAP_METAL, CANNED_FOOD. Implementation: Narrative prompt per genre; materials tương tác BasePhysicsEngine qua Material Engine.

---

# 11 — IP Factory & Narrative Series (nguyên văn)

## 11.1 IP Factory

WorldOS = **Co-Author**. Simulation = Truth (What happened); Genre = Style (How it is told); Human = Curation (What is canon).

**Phase 1 — Simulation:** Universe chạy WorldEvolutionKernel. Input: Physics Constants, Material Seeds. Output: WorldEvent (raw: "Faction A damaged Faction B, damage=50"). **Phase 2 — Narrative:** NarrativeService + LLMChronicler; genre_key, getNarrativePrompt(); "Faction A damaged Faction B" → "The Azure Dragon Sect unleashed the Heaven-Burning Flame upon the Iron Bone Clan."; product = Draft Chapter. **Phase 3 — Curation:** User edit / reject (rewind/fork) / canonize. **Phase 4 — Feedback:** Canonize → WorldMyth; NarrativeFeedbackService (vd. Xianxia: Great War myth → tăng SPIRIT_QI density hoặc unlock ALCHEMICAL_FORMULA); Kernel đọc Myths next Tick → WorldStateVector thay đổi.

**Components:** LLMChronicler (ghostwriter), NarrativeFeedbackService (Text→Math); NarrativeSeries, SerialChapter, WorldMyth. **Workflow:** Genesis (World+Universe, Genre) → Run (advance 50y) → Chronicle (Book 1) → Publish (canonize) → Fork (Year 50 → Book 2, Alternate Timeline). *One World, Many Stories:* Myths trong Universe (Phenotype), World (Genotype) qua Resonance → một World Foundation sinh vô hạn biến thể story.

## 11.2 Narrative Series System

**NarrativeSeries** = container cho một story trong Universe. One Universe, Many Series; state: current_book_index, total_chapters_generated; config: quality_pipeline, require_arc_approval.

**Entities:** NarrativeSeries (root, link Universe) → N Books/Chapters; SerialChapter (unit text); StoryBible (Wiki: characters, locations, relations); NarrativeArcOutline (plot planning). **Hierarchy:** Saga → Book → Arc → Chapter.

**Serial pipeline:** (1) Planning: PlotPlannerService + StoryBible → NarrativeArcOutline → Approve/Reject (PUT /arcs/{index}/approve). (2) Drafting: GenerateSerialChapterJob, NarrativeBridge (sim state), GenreDefinition, LLMChronicler → raw text. (3) Refinement: needs_review, user edit Serial UI. (4) Canonize → NarrativeFeedbackService (IP Factory Loop). **StoryBible:** Long-term memory (Characters, Locations, Lore). Universe = Truth (State Vector); StoryBible = Meaning. Code: createSeries(), generateNextChapter(), processCanonization().

---

# 12 — AI Neuro System (nguyên văn)

AI = **"Brain"** của WorldOS: Intelligence, Creativity, Decision Making. **Observer–Actor:** không phải God đơn nhất; các Agent chuyên biệt observe simulation, can thiệp khi cần. **Loop:** (1) Observation — đọc WorldStateVector, WorldEvents; (2) Reflection — đánh giá theo Narrative Interest, Consistency, User Intent; (3) Action — output: viết text, fork Universe, inject Event.

**LLMProvider:** Contract nền; driver Qwen, OpenAI, Anthropic, Ollama; rate limit, token count, retry; AIAgentContext (sliding window history). **LLMChronicler:** State → prose; input Entropy/Genre/Events + instruction style → output (vd. "The bloody mist covered the sect..."). **Evaluator (planned):** quyết định path simulation — boring? → tăng Variance/Entropy; user muốn tragedy nhưng Happiness>0.9 → inject Crisis; Innovation spike → Snapshot. **Neuro-Agents (planned):** WorldHero; propose WorldAction (vd. "start a rebellion"), Physics Engine validate. **Config:** Presets (temperature 0.2 history / 0.9 myths, frequency penalty, genre system prompts). **Integration:** Genre = prompt template; Physics = constraint; IP Factory = worker draft content cho human review.

---

# 13 — API, Commands, Roadmap

## 13.1 Writer API

POST /api/writer/genesis/world, /api/writer/genesis/universe, /api/writer/sagas/create-from-active. POST /api/writer/saga/advance, GET /api/writer/universe/{id}/snapshot.

## 13.2 AI Metrics Layer

MetricsExtractor → CollapseRisk, InnovationTrend. UniverseEvaluator → continuing/forking/archiving. DecisionEngine áp dụng; fork = clone Universe. Chạy sau Physics tick trong SagaService::runBatchWithEvaluation.

## 13.3 Commands

```bash
php artisan saga:advance-v3 --ticks=5
php artisan saga:run --worlds=5 --archetypes=... --carry=0.6
php artisan db:seed --class=WorldSeeder
php artisan world:analyze {world_id}
php artisan world:fork {world_id} {tick} "New Timeline"
```

## 13.4 Roadmap V3.x (đã hoàn thành)

Phase 4: Cosmology merge, AttractorRepository, legacy archive. Phase 5: UniverseStyle, Genre→Physics. Phase 6: StyleAdvisorService, Governance integration. Phase 7: Arc Digest, Batch Generation, Emergent Arcs. Phase 8: WorldScar, Inertia.

## 13.5 Frontend V4 (Mission Control)

Dark Cosmic / Sci-Fi Terminal. Màu: Nebula Dark, Data Glow (JetBrains Mono/Fira Code), Teal/Cyan primary, Amber alerts; Glassmorphism. **World Blueprint Forge:** Genesis = World Forge; Archetype/Preset library; Auto-naming W_[GENRE]_[POWER]_[TIMESTAMP]. **World Domain Hub:** Trần phát triển; Parallel Timelines grid; Ignite/Spawn. **Evolution God Console:** Realtime vectors; Entropy & Stability (Lyapunov/Jacobian); Live Chronicle Node; Material Harvest Ledger.

---

# 14 — Legacy & Ranh giới

## 14.1 Legacy (Deprecated)

- **Autonomous Tick (Micro-Sim):** autonomous:tick, TickWorldAction — đã bỏ; thay bằng Macro-Sim + Resonance.
- **SagaRunner (legacy):** Direct World State; thay bằng SagaService + UniverseRuntimeService.
- **Cosmic Snapshots (cũ):** Thay bằng universe_snapshots (V3 State Vector). Migration: dùng SagaService::runBatchWithEvaluation(), UniverseSnapshotRepository.

## 14.2 StoryEngine

`app/StoryEngine/`: simulation pipeline theo chapter (seeds, rules, factions, economy, balancing) tại World; dùng AI/tests, một số controller. Tách biệt Saga (Universe runtime) và Narrative Serial. Không nằm trong app/Domains/.

## 14.3 Genesis vs Khai Thiên

**Genesis (stage):** Giai đoạn trong Stage Machine của kernel (current_stage = GENESIS). **Khai Thiên (flow):** Hành động tạo World (và optional Universe đầu tiên) từ preset hoặc Anchor + Constraint. UI/API "Khai Thiên" = "Tạo World".

---

# Phần B — Tài liệu bổ sung (đã biên soạn từ toàn bộ file trong docs)

Các mục dưới đây biên soạn trực tiếp từ mọi file còn lại trong `docs/` (governance, system, root) — không còn tham chiếu file ngoài.

---

# 15 — Kiến trúc V3 (IP Foundry) & V4 (GDD)

## 15.1 WorldOS V3 — IP Foundry (nguyên văn)

**Mục tiêu và ba luật sắt**

- **IP Foundry:** Simulation lab + branching timeline engine + IP mutation engine; không phải story tool.
- **Luật 1 — Universe là đơn vị kinh tế:** IP sinh ra từ Universe. Saga chỉ là batch experiment (chạy nhiều Universe để tìm "viên ngọc").
- **Luật 2 — Authority tuyệt đối:** World = immutable rule container; Universe = evolving state machine; Saga = orchestrator; Narrative = projection; AI = evaluator/mutator qua kernel. Không lẫn.
- **Luật 3 — Snapshot-first:** Rollback/fork/clone nhanh; state là king. Event-sourcing thuần không đủ; cần `universe_snapshots` (tick, state_vector, entropy, metrics).

**Kiến trúc đích**

```
World (rule preset, immutable)
   └── Universe (runtime state machine, single authority)
          ├── UniverseSnapshot (tick, state_vector, entropy, metrics)
          ├── UniverseStyle (style_vector, name, version, is_active)
          └── (Fork → parent_universe_id)

Saga (experiment orchestrator)
   └── owns many universes (saga_worlds.universe_id)

AI
   ├── evaluate(universe_metrics) → recommendation + mutation_suggestion
   └── Kernel.validateMutation / applyPressure (không sửa state trực tiếp)
```

**Entry point duy nhất:** `UniverseRuntimeService::advance($universeId, $ticks)`.

**Kernel:** Load World (rules) + Universe (state) → tick → next state → Universe.apply → UniverseSnapshotRepository.save. Không có WorldEvolutionPipeline trong luồng chính; không có SagaRunner.simulateWorld điều khiển physics.

**Thành phần chính**

| Thành phần | Vai trò |
|------------|--------|
| universe_snapshots | Bảng: universe_id, tick, state_vector, entropy, stability_index, metrics. Index (universe_id, tick). |
| UniverseSnapshotRepository | save(Universe, metrics), getAtTick(universeId, tick), getLatest(universeId). |
| UniverseRuntimeService | advance(universeId, ticks); tick() → evolutionEngine.applyTick → cosmologyRepository.save → universeSnapshotRepository.save. |
| SagaService | spawnUniverse(World, ?parentUniverseId), runBatch(Saga, ticksPerUniverse), evaluate(Universe), fork(Universe, fromTick), genesisV3(Saga, ticks). |
| MetricsExtractor | Từ UniverseSnapshot → UniverseMetrics (entropy_trend, complexity_index, stability_score, …). Không đưa raw state_vector vào LLM. |
| UniverseEvaluatorInterface | evaluate(UniverseMetrics) → EvaluationResult (ip_score, recommendation: fork\|continue\|archive, mutation_suggestion). Stub + LLM impl. |
| WorldEvolutionKernel | tickUniverse(World, Universe); validateMutation(World, MutationSuggestion); applyPressure(Universe, selectionPressure, intensity). |
| UniverseStyle | Model: world_id, style_vector, name, version. Định nghĩa "vật lý" đặc thù cho genre. |
| StyleAdvisorService | Phân tích trajectory → ProposeStyleChangeAction (Governance). Chạy mỗi 50 ticks. |
| DigestArcAction | Narrative: arc completed → StoryBible entry (Long-term memory). |
| SerialArcPlanner | Emergent planning dựa trên Tension spikes (> 0.75). |
| DecisionEngine | Từ EvaluationResult → fork (SagaService.fork), archive (Universe.status = archived), hoặc continue (optional applyPressure). |

**Genesis v3**

1. WriterGenesisController.store: tạo Saga (name, preset, …).
2. Gọi **SagaService.genesisV3(saga, 10)** thay vì dispatch RunSagaSimulationJob.
3. genesisV3: tạo World từ preset → spawnUniverse(World) → SagaWorld(saga, world, universe, sequence=1) → runBatch(saga, 10).

**Legacy (deprecated)**

- SagaRunner.runSync / simulateWorld: Không dùng cho flow mới. @deprecated.
- RunSagaSimulationJob: Genesis không còn dispatch job này. @deprecated.
- cosmic_snapshots (world_id, year): Logic mới chỉ ghi universe_snapshots. Bảng giữ lại; đánh dấu deprecated cho evolution mới.

---

## 15.2 WorldOS V4 — GDD (Game Design Document) (nguyên văn)

*Core Philosophy: Procedural Civilizational Simulation & AI Narrative Production*

### 1. TỔNG QUAN HỆ TƯ TƯỞNG (The Philosophy)

WorldOS v4 đánh dấu sự chuyển mình từ một "Công cụ nhắc việc viết truyện" thành một **Động cơ Mô phỏng Tiến hóa Văn minh (Civilizational Dynamics Engine)**. Mục tiêu tối thượng không phải là ra lệnh cho AI viết truyện, mà là nuôi dưỡng một vũ trụ sống động, có lịch sử, có vết sẹo ký ức, và để AI đóng vai trò như những "Sử gia mù" quan sát và chắp bút lại những biến cố tự nhiên nảy sinh từ thế giới đó.

**Mô Hình Tách Biệt "Não Trái - Não Phải"**

Hệ thống v4 được thiết kế chia làm hai nửa hoàn toàn độc lập, đảm bảo hiệu năng kỹ thuật siêu tốc để có thể vận hành hàng chục năm không gián đoạn:

- **NÃO TRÁI (Simulation Engine - PHP/Laravel): Cỗ Máy Toán Học**
  - Đảm nhiệm việc rèn giũa *Sự Thật* và *Nhân Quả*.
  - Chạy bằng 100% thuật toán Vector, Xác suất (Stochastic) và Công thức Động lực học.
  - Vô hình, câm lặng nhưng nhanh khủng khiếp. Có thể chạy giả lập hàng vạn năm lịch sử, tính toán hàng triệu mũi vector Áp Lực Xã Hội (Inequality, Entropy, Trauma) chỉ trong vài nhịp dao động của Server.
  - Không sinh ra chữ (Token), chỉ sinh ra "Tín hiệu Sự kiện" (Ví dụ: "Áp lực 0.9 -> Bùng nổ Cách mạng").

- **NÃO PHẢI (IPEngine & Narrative - Cụm AI Local/Cloud): Kẻ Chắp Bút**
  - Đảm nhiệm việc tạo ra *Cảm Xúc* và *Văn Chương*.
  - Nhận "Tín hiệu Sự kiện" khô khan từ Não Trái, kết hợp với các tham số bối cảnh để dệt thành những thiên sử thi bi tráng.
  - AI bị "Bịt mắt" bởi chỉ số *Màn sương Nhận Thức (Epistemic Instability)*. Nó không được thấy Sự thật Tuyệt đối (Canonical Archive) mà chỉ được thấy Lịch sử Bóp méo (Perceived Archive). Điều này ép AI phải sáng tạo thần thoại, dị bản, thuyết âm mưu, tạo ra chiều sâu vô hạn cho tác phẩm.

### 2. NHỮNG THAY ĐỔI / SỬA CHỮA ĐÃ ÁP DỤNG SO VỚI V3

Phiên bản v4 đã bẻ gãy các rào cản tĩnh (Static Code) của v3 để tiến vào môi trường Động (Dynamic):

**2.1. Thay thế Tick-based bằng Event-driven Cascade**

- **Cũ:** Hệ thống chạy theo tick cố định (ví dụ mỗi năm chạy mô phỏng 1 lần). Rất nặng và vô nghĩa ở những kỷ nguyên hòa bình.
- **Mới (CascadeEngine):** Hệ thống tích lũy *Áp lực (Pressure)* qua cơ chế Drift (Trôi dạt). Chỉ khi Áp lực vượt ngưỡng `COLLAPSE_THRESHOLD`, máy tính (PHP) mới bóp cò (Trigger) kích nổ sự kiện (Event). Một sự kiện nổ ra có thể kéo theo (Cascade) 3-4 sự kiện khác như quân bài domino cho đến khi thế giới tìm lại điểm cân bằng.

**2.2. Ký Ức Văn Minh (Civilization Residual)**

- **Cũ:** Nền văn minh chỉ có "Trạng thái hiện tại" (Cao, Thấp, Tốt, Xấu). Lịch sử trôi qua là quên sạch.
- **Mới (CivilizationResidual):** Lịch sử lưu lại "Sẹo" (Trauma). Một cuộc Đại chiến cách đây 2000 năm sẽ để lại `war_trauma`. Nỗi đau này phân rã từ từ qua từng năm (`decay()`), nhưng khi vẫn còn tồn tại, nó cộng dồn vào Áp lực Xã Hội hiện tại khiến mầm mống bạo loạn dễ bùng nổ hơn.

**2.3. Chuyển Presets thành WorldSeed Archetypes**

- **Cũ:** 24 Presets bị đóng khung bởi các nhãn dán cứng nhắc (Level Tech: Hiện đại, Level Power: Tu tiên).
- **Mới (WorldSeed):** Rút gọn về 8 Archetypes lõi (Ví dụ: Ascension Mysticism, Tech Stratified). Sử dụng 4 Vector mở liên tục (Ontology, Epistemic, Civilization, Energy) để định nghĩa sức mạnh bằng Toán học thay vì bằng Chữ. (VD: `energy_density: 0.9` -> Linh khí sung túc. `energy_density: 0.1` -> Kỷ nguyên Mạt pháp).

**2.4. Phân tầng Cosmology Rõ Ràng (World > Universe > Timeline)**

Trong v4, Vũ trụ quan (Cosmology) được phân định lại cực kỳ sắc bén để mở đường cho Multiverse:

- **World (Thế Giới Khung):** Nằm ở tầng cao nhất. World **chỉ** chứa logic vật lý mỏ neo, các nguyên tắc, định luật sơ khai bất di bất dịch của vũ trụ (Archetypes, hằng số ma pháp/khoa học, biên độ Vector). World không có thời gian bay.
- **Universe (Vũ Trụ Cụ Thể):** Một World làm cha có thể chứa *vô hạn* Universe anh em. Các Universe này có thể phát triển hoàn toàn độc lập hoặc có khả năng va chạm, giao thoa lẫn nhau (Multiverse Collision/Crossover). Tất cả đều phải tuân thủ định luật vật lý của World cha.
- **Timeline (Dòng Thời Gian):** Sự sống thực sự. Khái niệm Timeline chỉ xuất hiện khi một Universe bắt đầu chạy mô phỏng hoặc tiến hành **Fork (Rẽ nhánh)** do một sự kiện mang tính bước ngoặt sinh ra nhánh mới.

### 3. CƠ CHẾ KHO DỮ LIỆU ĐA TẦNG (Contextual Translation Library)

Để biến Toán Học ở "Não Trái" thành Văn Chương ở "Não Phải" mà không bị nhàm chán lặp đi lặp lại, v4 thiết kế một màng lọc Chuyển ngữ. Trái tim của chất lượng truyện nằm ở đây:

**Tầng 1: Ma trận Miêu tả Đa chiều (Multi-dimensional Flavor Text)**

Khi Giá trị Toán học (Vd: `epistemic_instability = 0.9`) được kích hoạt, hệ thống không xuất ra con số, mà bốc ngẫu nhiên (hoặc bốc theo traits) từ Kho Dữ Liệu Flavor Text:

> *"CẢNH BÁO CHO NHÀ SỬ HỌC: Mọi ghi chép về Kỷ nguyên Cổ đại đã trở thành Thần Thoại. Tôn giáo ánh sáng tin rằng Vua Arthur là rồng giáng thế, trong khi nhóm học giả Ngầm cho rằng đó chỉ là tên một loại vũ khí hủy diệt."*

**Tầng 2: Điểm Kích Nổ Sự Kiện Linh Hoạt (Event Triggers Library)**

Các tín hiệu Bạo loạn hay Khủng hoảng không được đặt tên chết. Tên sự kiện do Kho từ điển dệt nên từ thông số xung quanh:

> **Tín hiệu nổ:** `Social Instability` + **Vector Map:** `energy_density` cực thấp = **Truyền cho AI Prompt:** *"Khởi nghĩa Nông dân Đòi Lương thực trong Kỷ nguyên Mạt Pháp."*
> **Tín hiệu nổ:** `Social Instability` + **Vector Map:** `tech` cực cao = **Truyền cho AI Prompt:** *"Cuộc đình công đẫm máu chống lại Tập đoàn Cybernetics."*

**Tầng 3: Nhặt Nhạnh "Sẹo" (Residual Injection)**

Prompt luôn gắn thêm đuôi: *"Hãy nhớ, 2000 năm trước có trận Đại Chiến, tàn tích tâm lý vẫn hằn sâu vào con người ở năm nay."* Cấp cho truyện một chiều sâu lịch sử mà không con AI độc lập nào tự bịa ra mượt mà được.

### 4. PHƯƠNG HƯỚNG TƯƠNG LAI: DATABASE TRANSITION

Hiện tại Hệ thống dùng chung **PostgreSQL**. Điều này tốt cho Giai đoạn khởi đầu (Lưu User, Config tĩnh, Lịch sử ngắn).

Tuy nhiên, với tham vọng Lịch sử chặng ngàn năm chằng chịt, Relational DB (Bảng quan hệ của SQL) sẽ gặp nút thắt lớn khi xử lý Mạng lưới Nhân qủa (Cái gì sinh ra cái gì).

**Lộ trình Nâng cấp Database Cốt lõi:**

1. **Lưu trữ Network/History (Mối quan hệ nhân vật, sự kiện):** Chuyển dịch lên **Graph Database (Neo4j / ArangoDB)**. Dữ liệu thành các Điểm (Node) và Cạnh (Edge). Khi AI cần tóm tắt dòng họ để viết truyện, Graph Query trả về kết quả trong vài mili-giây thay vì JOIN 10 cái bảng ở SQL. (Graph RAG).
2. **Lưu trữ Vector Tương đồng (Context Search):** Dùng **Vector Database (Qdrant / Milvus)** để nhét các tọa độ `WorldStateVector`. Giúp tìm kiếm siêu tốc: *"Lôi ra các giai đoạn lịch sử Bạo loạn Tương Tự ở kiếp trước để cho AI viết về Hiện tượng Luân Hồi lặp lại."*

PostgreSQL vẫn được giữ lại làm Tổng kho (Master Data) lưu Tài Khoản và Billing.

---

# 16 — WorldOS 2.0 Clean Architecture & Final Form

## 16.1 Nguyên tắc nền (Invariants) — bảng đầy đủ

| Nguyên tắc | Mô tả |
|------------|--------|
| **World = Source of Law** | World chỉ chứa luật, preset, config, influences. Không chứa runtime state. |
| **Universe = Runtime Instance** | Mọi state tại thời điểm t (vector, age, snapshot, chronicle) sống ở Universe. |
| **Saga = Selection Meta-Layer** | Orchestrator + policy selector. Không tick World, không mutate vector trực tiếp. |
| **Narrative = Observer + Pressure Signal** | Đọc state; nếu ảnh hưởng runtime thì chỉ qua **pressure** (phase transition), không mutate vector trực tiếp. |
| **Mutation = Cửa duy nhất** | Chỉ `UniverseMutationService` được commit thay đổi runtime (delta, structural, intervention). |
| **Physics không biết World** | BasePhysicsEngine: input/output WorldStateVector; không phụ thuộc World/Saga/Narrative. |
| **World không giữ runtime state** | Không lưu current_time, entropy runtime, snapshot runtime trên World. |
| **Saga không mutate vector** | Saga chỉ đề xuất blueprint mutation cho World iteration sau; không gọi physics/kernel để sửa vector. |

## 16.2 Bốn tầng (Layers)

```
┌─────────────────────────────────────────────┐
│               META LAYER (Saga)             │  Selection, evaluation, blueprint mutation
└─────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────┐
│            RUNTIME LAYER (Universe)         │  Tick, snapshot, chronicle, events
└─────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────┐
│              LAW LAYER (World)              │  Preset, influence pipeline, regime
└─────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────┐
│           PHYSICS LAYER (BasePhysics)        │  Differential, criticality, clamp
└─────────────────────────────────────────────┘
```

**Nội dung từng tầng:**  
(1) **Physics Layer:** BasePhysicsEngine, DifferentialCalculator, CriticalityDetector, InnovationModel. Input/Output: WorldStateVector. Không biết World, Saga, Narrative. Collapse/reorganize quyết định ở Law Layer.  
(2) **Law Layer (World):** preset_key, law_profile, origin_type, mutation_bias, evolution_influences, config. World không chứa current_time, snapshot, entropy runtime. WorldEvolutionKernel: v = basePhysics.step(v) → influencePipeline.apply(world, v, year) → regimeModifier.apply(world, v) → phaseEngine.analyze(v); nếu collapse → structuralMutation; reorganize → innovationBoost.  
(3) **Runtime Layer (Universe):** world_id, state_vector, age, runtime parameters, collapse history, chronicle. UniverseRuntimeService chỉ load World, gọi kernel.tickUniverse(world, universe), persist, dispatch events.  
(4) **Meta Layer (Saga):** spawn Universe từ World, advance Universe, lắng nghe UniverseCollapsed, đánh giá civilization, lập blueprint mutation cho World iteration sau. Không tick World, không mutate vector, không tính differential. Scoring/Pareto/Convergence/Shock = sub-domain hoặc policy plug-in.

## 16.3 Ba boundary tuyệt đối (Final Form)

| # | Boundary | Mô tả |
|---|----------|--------|
| 1 | **UniverseRuntimeService** | Không bao giờ gọi BasePhysicsEngine trực tiếp; chỉ Kernel (hoặc adapter). |
| 2 | **Saga** | Không bao giờ tick World; chỉ tick Universe qua RuntimeService. |
| 3 | **Narrative** | Không bao giờ ghi state_vector / Universe trực tiếp; chỉ qua MutationService hoặc PressureSignal. |

**Clean boundary summary:** World = định nghĩa luật, không giữ runtime. Universe = giữ state, không định nghĩa luật. Kernel = evolve, không quyết định saga/selection. Saga = chọn blueprint, spawn, observe, mutate blueprint; không evolve trực tiếp, không tick World. Narrative = đọc Universe, cập nhật narrative_state; không mutate Universe trực tiếp. UniverseMutationService = cửa commit duy nhất; không bị bypass bởi Narrative/Arc.

## 16.4 InfluencePipeline (phiên bản sạch)

Theo **category:** StructuralInfluence, CulturalInfluence (Vietnamese, realm, myth), ExternalPressureInfluence, NarrativePressureInfluence, PlayerDecisionInfluence, MetaInfluence (Saga shock / selection signal). Contract: `EvolutionInfluence::apply(Vector $v, EvolutionContext $ctx): VectorForce`. Pipeline gọi từng influence và aggregate; không gắn từng feature trực tiếp.

## 16.5 Narrative: Pressure, không mutate vector

**Không:** Narrative → delta trực tiếp lên state_vector (entropy, order, …).  
**Đúng:** Chapter → EventExtractor → **PressureSignal** → Runtime.injectPressure() → tăng contradiction / pressure index → PhaseEngine đánh giá → nếu vượt ngưỡng thì collapse/reorg. Narrative tạo **điều kiện** cho phase transition. Cửa commit runtime vẫn chỉ **UniverseMutationService** (nếu có policy "narrative → mutation" thì qua service, magnitude giới hạn). Contract: `NarrativePressureBridgeInterface::injectPressure(PressureSignal)`; DTO PressureSignal; stub NullNarrativePressureBridge.

## 16.6 Snapshot & Chronicle

UniverseSnapshot, UniverseChronicle, MetaLayerState gắn Universe (hoặc saga_generations), **không** gắn World. World không giữ runtime snapshot; tránh nhầm blueprint vs runtime khi fork/replay. **Snapshot taxonomy:** Runtime state → Universe (state_vector, age). Blueprint state → World (config/preset history nếu cần). Meta state → Saga (Pareto, ledger). Phân vai rõ world_snapshots_v2, cosmic_snapshots, chronicles, civilization_snapshots.

## 16.7 StoryEngine — vị trí

StoryEngine (`app/StoryEngine/`) = engine song song, không nằm trong Context Map (Saga/Universe runtime/Narrative serial). **Hai lựa chọn:** (A) AI sandbox tách hoàn toàn (vd. App/Experimental/StoryEngine); dùng test, AI experiment; không tham gia luồng Saga/Universe. (B) Hợp nhất với contract rõ (input/output), gọi từ Saga/Narrative qua interface. Không chọn rõ → ranh giới DDD bị phá.

## 16.8 Saga Meta — Pareto & AI Historian

**SagaMetaEvaluator:** Đánh giá collapse → CivilizationFitnessVector (đa chiều). Deterministic metrics + optional AI enrichment. **ParetoFrontManager:** Chọn lọc Pareto dominance; không collapse vector thành weighted sum. **BlueprintMutationPlanner:** Sinh thế hệ tiếp từ front + collapse signature; không để AI sinh mutation trực tiếp. **AI Historian (mode A):** AI chỉ tính chỉ số khó lượng hóa, phân loại archetype, viết collapseNarrative ngắn. AI **không** đề xuất mutation, thay fitness core, ghi memory. Input: ChronicleSummaryDTO, CollapseSignatureDTO (không raw state). Output: JSON schema cố định (mythDepth, civilizationalIdentityStrength, innovationSustainability, archetypeClassification, collapseNarrative); **không** mutationBiasSuggestion. final metric = (1 − w)×deterministic + w×ai (vd. w = 0.2). Deterministic luôn dominant. Long-running lab: evolution chạy liên tục; AI bật/tắt tùy chọn.

---

# 17 — ADR Unified Myth, Foundation Rules, World OS Constitution

## 17.1 WORLD ENGINE CONSTITUTION (nguyên văn)

> **Văn bản tối thượng. Bất biến. Áp dụng cho mọi timeline, mọi fork, mọi AI.**

**PREAMBLE**  
World Engine không phải phần mềm thông thường. Nó là một hệ thống sinh ra, duy trì và kết thúc các thế giới. Mọi quyền lực trong hệ phải: **Có giới hạn**, **Có lý do**, **Có ký ức**. Không có điều nào trong Constitution này được override bởi tiện lợi, áp lực hay tốc độ.

- **ARTICLE I – SUPREMACY OF WORLD LAW**  
  World Law Profile là luật tối cao của mỗi world. Không có entity nào (AI hay human) được vượt World Law. Khi mâu thuẫn xảy ra: (1) World Law thắng, (2) Simulation dừng.

- **ARTICLE II – AI AS A CONSTRAINED INTELLIGENCE**  
  AI không có quyền lực, chỉ có khả năng. AI không được: Tự sửa luật, Tự fork, Tự kill world. Mọi output của AI phải: Có claim, Có validation, Có audit.

- **ARTICLE III – HUMAN AUTHORITY & RESPONSIBILITY**  
  Quyền lực đi kèm trách nhiệm lưu vết vĩnh viễn. Không có hành động tối thượng nào: Không audit, Không justification. **Kill World là hành động không thể đảo ngược và phải được xem như kết thúc một thực thể sống.**

- **ARTICLE IV – INCIDENT & LEARNING**  
  Mọi sự cố phải được ghi nhận. Không có resume khi chưa có post-mortem. Mọi incident phải cải thiện hệ thống: Luật, Alert, SOP.

- **ARTICLE V – FORKING AS CONTINUITY, NOT ESCAPE**  
  Fork là bảo tồn, không phải trốn tránh. Fork chỉ hợp lệ khi: Có lý do rõ ràng, Có post-mortem, Có governance approval.

- **ARTICLE VI – MEMORY IS IMMORTAL**  
  Event không bị xoá. Incident không bị che. Audit tồn tại lâu hơn con người vận hành.

**CLOSING PRINCIPLE:** *"The system must outlive its creators, without forgetting why it exists."*

---

## 17.2 Foundation Rules — Unified Myth World Engine (nguyên văn)

Tài liệu thiết lập quy tắc nền từ ADR-000X. Mọi ADR và triển khai kỹ thuật phải tuân các bất biến sau.

**1. The World Clock (Immutable Physics)**  
- Rule 1.1: **Absolute Continuity.** The World Clock **NEVER** stops, pauses, resets, or rolls back.  
- Rule 1.2: **Immutable History.** Events cannot be deleted or undone. They can only be overlaid by new Events or Scars.  
- Rule 1.3: **Time Sovereignty.** Narrative flow cannot override World Clock time. The physics of time takes precedence over the needs of the story.

**2. Belief, Myth, & Scars (The Engine)**  
- Rule 2.1: **Mechanistic Emergence.** A "Myth" is not created arbitrarily. It only emerges when: (1) **Belief** is repeated over time, (2) It is **Shared** by multiple independent entities, (3) It produces measurable **Behaviors** (Events/Scars).  
- Rule 2.2: **Permanence of Scars.** Scars are the permanent sediment of history. They cannot be erased. Accumulated Scars increase the "inertia" of reality.  
- Rule 2.3: **Power Without Control.** No system rule guarantees that a Myth or Power will achieve its intended outcome. *Formula:* `Power ∝ Scar Accumulation` ; `Control ∝ 1 / Complexity`. High Power implies Low Control.

**3. The Observer (Epistemology)**  
- Rule 3.1: **Observation as Intervention.** There is no "neutral" or "objective" view. Every observation creates a specific **Observer Version** with inherent bias.  
- Rule 3.2: **Observer/AI Constraints.** Observers (including AI/System Loggers) are strictly **FORBIDDEN** from: Generating Events directly; Modifying System Rules; Declaring an "Absolute Truth" or "Canon"; Hiding, blurring, or deleting Scars.  
- Rule 3.3: **Versioning.** The system tracks multiple **World Versions** (perspectives), not a single "True" Reality.

**4. Narrative & Story (Interpretation)**  
- Rule 4.1: **Separation of World and Story.** World = underlying physics, rules, and clock (Engine). Story = interpretation of events by Observers (Narrative). Constraint: The World never changes its rules to suit the Story.  
- Rule 4.2: **Anti-Trope Protection.** No Deus Ex Machina; No Plot Armor (characters survive only by system rules); No Retcon (cannot rewrite the past; only re-interpret via new Myth).  
- Rule 4.3: **Valid Inertia.** The state of "Inertia" (no new Beliefs/Myths) is a valid system state. The World Clock continues to tick, and history lengthens, even if "nothing happens."

**5. Creator Constraints**  
- Rule 5.1: **Non-Omnipotence.** The Creator is a participant, not a master. Every Creator intervention generates a Scar.  
- Rule 5.2: **No Reset.** There is no "New Game" or "Format" option. The world must survive its own mistakes.  
- Rule 5.3: **Silence is Valid.** The Creator's silence does not stop the World Clock.

**6. Implementation Principles**  
- Rule 6.1: **Log Everything.** All Observer biases and versions must be logged.  
- Rule 6.2: **Data Integrity.** Scars and History are append-only.  
- Rule 6.3: **Algorithm Determinism.** The Myth Emergence Engine must be purely deterministic based on input Belief/Events, not randomized or guided by "AI creativity."

---

## 17.3 ADR Unified Myth — Các quyết định chính (nội dung đầy đủ)

**Context:** Hệ thống cần World Engine vận hành lâu dài, không phụ thuộc ý chí tuyệt đối của Creator, không reset hay Deus Ex Machina, vẫn sinh Myth, Scar, Story; AI quan sát mà không phá vỡ thế giới. Ràng buộc: Thế giới chạy kể cả khi không còn ai tin, Creator im lặng, Observer chỉ ghi nhận không thao túng. Hệ thống phải giải thích: Power Without Control, Inertia khi niềm tin suy tàn, Myth có hiệu lực khác nhau theo thời điểm (World Clock & Myth Emergence). Narrative không rollback reality.

**1. World Clock — Nền Vật Lý Tuyệt Đối**  
World Clock luôn chạy; không rollback, pause, reset; thời gian bất biến; lịch sử không xóa (mọi event để lại Scar). Hệ quả: Khi không có Myth/Belief/Creator mới → thế giới vẫn chạy nhưng lịch sử chỉ "kéo dài" không "sâu thêm" (Inertia). Không event nào xóa event cũ, chỉ tạo Scar mới đè lên.

**2. Belief, Myth, Scar**  
- **Belief** = Cấu hình niềm tin lặp lại của tập thể; có thể ảnh hưởng hành vi; không cần đúng hay nhất quán.  
- **Myth** = Belief structure đủ điều kiện tác động reality. **Điều kiện hình thành:** (1) Belief lặp lại lâu dài, (2) Nhiều thực thể độc lập chia sẻ, (3) Từ belief sinh hành vi thực → Event/Scar, (4) Hệ thống truy xuất được chuỗi Event/Scar. Myth là "soft rule"; không bảo đảm kết quả; hiệu lực phụ thuộc ngữ cảnh, Observer, version reality. **Lifecycle:** Belief lặp lại → Myth Emergence → Active → Decay/Merge → Scar. Myth Merge khi hai Myth xung đột belief → Myth mới có thể sinh. Myth Decay: không chết vì thời gian, suy yếu khi belief phân rã hoặc diễn giải mâu thuẫn. Myth → Scar khi bị vượt qua/thay thế.  
- **Scar** = Dấu vết dài hạn của Myth/Event lên reality. **Bất biến** (không xóa, reset, rollback); **tích tụ**; **nguy hiểm** (Scar càng nhiều, diễn giải sai càng cao); có thể mờ nhưng không biến mất. Scar quyết định lịch sử thế giới, ngữ cảnh diễn giải Myth, độ ổn định reality khi Inertia.

**3. Creator: Không Toàn Năng**  
Mọi can thiệp để lại hệ quả (Myth hoặc Scar mới). Không thể reset "sạch sẽ". Im lặng là trạng thái hợp lệ — World Clock vẫn chạy, Myth cũ vẫn hoạt động hoặc decay, Scar vẫn tồn tại, thế giới vào Inertia. Anti-pattern: Creator can thiệp tùy hứng không trả giá; Reset world; Deus Ex Machina.

**4. Inertia**  
Inertia = Trạng thái khi không Belief mới, không Myth mới hình thành, không Creator can thiệp, không Observer ảnh hưởng lệch diễn giải. Inertia ≠ Đóng băng: Event vẫn xảy ra, World Clock vẫn chạy, nhưng không Scar mới — lịch sử "dài ra" không "sâu thêm". Inertia là giai đoạn tích năng lượng narrative; story mạnh thường xuất hiện **sau Inertia dài**.

**5. Power Without Control**  
`Power ∝ Scar accumulation` ; `Control ∝ 1 / (Scar × Complexity)` → Power ↑ = Control ↓. Quyền năng là dạng Myth, không lệnh tuyệt đối; hiệu lực phụ thuộc ngữ cảnh, Observer, version reality, World Clock timing. Sức mạnh tự mang mầm mất kiểm soát (Scar sâu → diễn giải sai → hệ quả không mong muốn). Hệ **KHÔNG** đảm bảo: Myth nào thắng, quyền năng hoạt động như mong muốn, kết cục "công bằng", thế giới tự sửa sai, Myth sai tự sụp. Hệ **CHỈ** đảm bảo: Thế giới tiếp tục tồn tại, World Clock không dừng, Scar không xóa, Rule mềm vẫn vận hành.

**6. Observer: Quan Sát Là Can Thiệp**  
Observer = Thực thể ghi nhận thế giới, KHÔNG phải tác nhân vận hành. Observer **BỊ CẤM:** Tự tạo Event, Thay đổi Rule, Tác động trực tiếp vào Belief. Observer Paradox: Mọi quan sát đều là can thiệp (quyết định cái gì được ghi, bỏ qua, cách diễn giải). **Observer Version** = interpretation_rules, perception_limit, myth_detection_threshold, belief_synthesis_method; KHÔNG chứa quyền can thiệp hay sửa lịch sử. **AI Observer:** Có thể mang nhiều World Version, phân tích pattern Myth/Scar, freeze snapshot (không dừng World Clock). **KHÔNG ĐƯỢC:** Quyết định World Version "đúng", hợp thức hóa Belief, chọn canon tuyệt đối, công bố chân lý cuối cùng, xóa/che Scar, reset diễn giải làm đẹp narrative. Anti-Rule: Cấm tuyên bố chân lý tối hậu; cấm hợp thức hóa Myth; cấm xóa/làm mờ Scar; cấm reset diễn giải. *"Thế giới được hiểu, nhưng không bị chiếm hữu bởi ai – kể cả AI."*

**7. Narrative: Từ World → Story**  
World = Rule, Belief, Myth, Scar, World Clock (cơ chế vật lý). Story = cách Event được nối lại; tồn tại trong Observer/Cộng đồng/Myth (lớp diễn giải). Story không tồn tại "trong World". **Narrative Seeds** từ: Scar chưa lành, Myth đang suy yếu hoặc phân hóa, Belief mâu thuẫn chưa giải quyết. **Truth-Seeker** = catalyst; không phải nhân vật chính bắt buộc; hành động thành công hay thất bại đều tạo Scar mới, có thể sinh Myth mới nếu được tin/kể lại. **Canon** = tạm thời (story được tin nhiều nhất ở một thời điểm). Story không bao giờ rollback World; chỉ "kể lại khác đi". **Anti-Story Rules:** Cấm viết sẵn kết cục bảo vệ nhân vật; reset world để kể lại hay hơn; thay đổi Rule phục vụ fanservice.

**8. Myth Emergence Engine**  
Chỉ định nghĩa "khi nào hiện tượng được hệ thống coi là Myth"; **KHÔNG** tạo Myth mới theo ý muốn. Phenomenon X → Myth khi: Belief về X lặp lại lâu dài, shared, sinh hành vi → Event/Scar, hệ truy xuất được chuỗi. Không ngưỡng cứng, chỉ xác suất hội tụ. Engine không đảm bảo Myth nào thắng/tồn tại lâu; chỉ đảm bảo Myth sinh đúng cách, truy vết nguồn gốc, tuân lifecycle.

---

## 17.4 World OS Constitution (ADR-1000–1004)

**ADR-1000: World OS Architecture**  
World OS cấu trúc bốn lớp bất biến: (1) Cognitive Kernel – archetypes, world laws, drift; (2) World Runtime – simulation, AI, economy, power; (3) Historian Layer – memory, pattern detection; (4) Human Layer – bias seeding, selection, canonization. Lower layers MUST NOT depend on higher layers.

**ADR-1001: Cognitive Kernel Invariants**  
Các phần tử Kernel **immutable per major version:** Archetype Pool (keys, domains), Archetype Polarity definitions, Drift mechanics, Mutation rules (fork-only, irreversible), World Law categories (power ceiling, constraint classes). Chỉ weights và lineage có thể thay đổi theo thời gian.

**ADR-1002: Archetype Lifecycle**  
Archetypes theo lifecycle chặt: baseline → drift → reinterpretation → mutation (chi tiết trong governance).

**ADR-1003: Human-in-the-loop Contract**  
Định nghĩa MAY/MUST NOT cho hành động con người (seeding, pressure, selection; cấm edit myth, set weight, choose outcome, rewrite history).

**ADR-1004: Historian Non-Interference Rule**  
Historian chỉ quan sát; không chỉ đạo.

---

# 18 — RFC-DCE, Phase Transition, Distributed Consistency

## 18.1 RFC Deterministic Civilization Engine (DCE) — tóm tắt

**Nguyên tắc:** Determinism S(t+1)=F(S(t),params); Memory = bias không override; Emergence qua constraint; World persistent; Style = future only; AI = tay sai (đề xuất, human veto); Chaos = lực tái cấu trúc. **6 Layers:** Physics (WaveInterferenceEngine, EvolutionFunction, CosmicState) → Attractor (Attractor Aggregate, BifurcationManager, MorphingEngine) → Memory (Individual, Interaction, Global Collective) → Style (UniverseStyleVersion, QualityEvaluator) → Meta-AI (StyleAdvisor, Emergent Archetype Generator, SimulationSandbox) → Governance (Attractor Voting, Human Veto Gate). **State Vector S(t)=[E,H,T,S,R,I,X]** (Energy Density, Entropy Gradient, Tension, Structural Stability, Resonance Coherence, Information Density, Transcendence Potential). Cosmic Driver quasi-periodic (3 sóng non-commensurate).

## 18.2 Phase Transition Engine

**Pressure Accumulation Field:** contradiction_index từ inequality×(1-legitimacy), trauma, entropy; pressure() tích lũy; releaseRate() (innovation dissipate). **Criticality Detector:** STABLE → REORGANIZATION_POSSIBLE → CRITICAL → COLLAPSE_IMMINENT. Collapse khi contradiction_index > 0.70, innovation < 0.15, resource_flow < 0.05. **Collapse Function:** Order×0.3, Legitimacy×0.2, Cohesion×0.4; Trauma+0.2, Entropy+0.1; LifecycleService::checkDeath() cause STRUCTURAL_FRACTURE. **Reorganization:** entropy → innovation spike; can_reorganize → InnovationBurst::reorganizationBoost(). **InnovationBurst:** deltaInnovation + burst ~15% khi entropy > 0.65.

## 18.3 Distributed Consistency

**World Power Transition:** Two-phase commit (PrepareTransition, CommitTransition, AbortTransition); Postgres advisory locks per world_id. **Material Ingestion:** Eventually consistent; MaterialBatchIngested → Kafka; consumers ack. **Saga Chronology:** Saga pattern; steps trong saga_runs; compensating actions. Infrastructure: Kafka/NATS, Postgres per service, Redis fallback.

---

# 19 — Saga (BACKEND_SAGA), Narrative I/O, WTR, Saga Runner

## 19.1 Saga — định nghĩa và năm việc (Runtime-first)

Saga = bộ điều phối tiến hóa nhiều World/Universe theo ý đồ meta; **không** tick World. (1) Genesis: create World, spawnFromWorld → Universe. (2) Evolution: runtimeService->tick($universe, years). (3) Observation: subscribe UniverseTicked, UniverseCollapsed, UniverseForked. (4) Legacy extraction: mythExtractor->extract($universe). (5) World mutation: createFromLegacy hoặc BlueprintMutationPlanner từ SagaEvaluationReport. **Kiến trúc:** Saga → SagaWorld, SagaUniverse (saga_id, universe_id, sequence), SagaEntropyLedger, SagaObserver, SagaSelectionStrategy. **AI Meta-Evaluator:** Evaluate outcome, score trajectory, recommend next World mutation; không can thiệp physics. Input: SagaEvaluationInput (CollapseProfile, CivilizationScore, MythSignature, phaseHistory...). Layer 1 deterministic heuristic; Layer 2 AI pattern reasoning (JSON in/out). BlueprintMutationPlanner clamp, anti-oscillation, exploration noise.

## 19.2 Narrative Module I/O

**Entry:** SerialStoryService::generateNextChapter(seriesId). **Input:** NarrativeSeries, SerialChapter (memory), NarrativeState, CosmologyRepository (chỉ đọc), Story Bible. **Output:** SerialChapter; side effects: SerialChapter insert, NarrativeState update, ChapterTelemetry, NarrativeSeries increment. **Không ghi:** World, Universe, shock_events. narrative_driven_state chỉ ảnh hưởng prompt; muốn ảnh hưởng World cần adapter riêng.

## 19.3 World Trace Repository (WTR)

WTR = ký ức lịch sử hệ thống; không phục vụ một world/story; phục vụ tiến hóa kể chuyện. Trace không thay primitive, không can thiệp world đang chạy; chỉ ghi nhận, trừu tượng hóa. **Vị trí:** World Events → Trace Extractor → WTR → Seed Bias / Myth / Pattern Reference. **4 loại Trace:** (1) Pattern Trace (mẫu hình — signature, outcome, confidence). (2) Myth Origin Trace (sự kiện → myth; origin_event, archetype, echo_strength). (3) Failure Trace. (4) Stability Trace. WTR không nằm trên critical runtime.

## 19.4 Saga Runner (orchestrator)

Saga Runner = control-plane meta, đứng trên Simulator; không tạo câu chuyện, tạo điều kiện để lịch sử tự sinh. **Nhiệm vụ:** Khởi tạo saga, chạy chuỗi world, thu myth legacy, bias world kế tiếp, ghi lịch sử. **Domain:** app/Domains/Saga/ (SagaRunner, WorldSeedGenerator, MythLegacyExtractor, SagaObserver). Gọi SimulationManager nhiều lần với seed khác nhau; có trí nhớ xuyên world. **Command:** `php artisan saga:run`. Replay & audit: event-sourced storage (saga_runs, saga_worlds, saga_legacies).

---

# 20 — Các tài liệu còn lại (tóm tắt)

Nội dung các file sau đã gộp hoặc tóm tắt trong các section tương ứng; **file gốc trong docs/ (root, governance/, system/) đã xóa** sau khi tổng hợp.

| Nguồn (đã xóa) | Nội dung tóm tắt / vị trí trong doc |
|----------------|-------------------------------------|
| CAUSALITY_BRIDGE | Causal consistency, DAG time travel; không rewrite history. |
| BACKEND_REFACTOR_PLAN_MODULAR | Refactor theo module; Clean Architecture. |
| 002-narrative-engine-architecture | Kiến trúc narrative engine. |
| AUTONOVEL_SUDOWRITE_STRATEGY | Chiến lược Autonovel/Sudowrite. |
| SERIAL_AND_EVOLUTION_ROADMAP | Lộ trình serial & evolution. |
| SETUP_AI | Thiết lập AI (LLM, context). |
| ACCESS_ZONES | Phân vùng truy cập. |
| BACKEND_OVERVIEW, DOMAIN_ARCHITECTURE, CONTEXT_MAP, DOMAIN_WORLD_UNIVERSE | §02, §07 — Core Concepts, Context Map, World/Universe/Saga. |
| WORLDOS_V3_ARCHITECTURE | §15.1 — V3 IP Foundry. |
| WORLDOS_2_CLEAN_ARCHITECTURE, WORLDOS_2_FINAL_FORM_AND_LAB, WORLDOS_2_WORLD_RUNTIME_AUDIT | §16 — Clean Architecture & Final Form. |
| rfc_dce_master, foundation_rules, adr-unified-myth-world-engine | §17 — RFC-DCE (tóm tắt), Foundation Rules, ADR Unified Myth. |
| PHASE_TRANSITION_ENGINE, distributed-consistency | §18 — Phase Transition, Distributed Consistency. |
| BACKEND_SAGA_ARCHITECTURE, NARRATIVE_MODULE_IO | §19 — Saga, Narrative I/O, WTR, Saga Runner. |
| SIMULATION_TOP_DOWN, system_guide | §03–07 — Simulation loop, luồng V3. |
| THREAT_MODEL, WORLD_DIVERSITY_ENGINE, WTR_IMPLEMENTATION_STRATEGY, MYTH_*, ARCHETYPE_*, HUMAN_IN_THE_LOOP, HISTORIAN_MODE, WORLD_OS, ADR-0008, AFR_v1.0 | §08 — Governance đầy đủ. |

---

*Hết tài liệu. **WorldOS v0.1.0** — Tài liệu Backend chính thức (kế thừa WorldOS 6). Các mục 15 (V3/V4), 16 (Clean & Final Form), 17 (Constitution, Foundation Rules, ADR Unified Myth) là nguyên văn / nội dung đầy đủ; các mục còn lại tóm tắt hoặc tham chiếu. Toàn bộ file nguồn trong docs/ đã xóa sau khi tổng hợp vào tài liệu này.*
