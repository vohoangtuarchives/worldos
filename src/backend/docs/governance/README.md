# Master Governance Index

> **World Management & Control Protocol (WMCP) - Complete Governance Framework**

This index organizes all governance documents for the WMCP system. Each document establishes rules, principles, and constraints for different aspects of world simulation and storytelling.

---

## 📋 Document Status Overview

**Total Documents:** 24
**Status:** Complete World OS - Cognitive Operating System for Emergent History

---

## 🏛️ CONSTITUTIONAL DOCUMENTS

**These ADRs have constitutional status and supersede all other governance documents.**

### 0. [WORLD_OS_CONSTITUTION.md](./WORLD_OS_CONSTITUTION.md)

**Purpose:** Define constitutional invariants of World OS

**5 Constitutional ADRs:**
1. **ADR-1000:** World OS Architecture (4 immutable layers)
2. **ADR-1001:** Cognitive Kernel Invariants (immutable per major version)
3. **ADR-1002:** Archetype Lifecycle (baseline → drift → reinterpretation → mutation)
4. **ADR-1003:** Human-in-the-loop Contract (MAY/MUST NOT)
5. **ADR-1004:** Historian Non-Interference Rule (observe only)

**Key Principle:**
> World OS does not remember in order to improve. It remembers in order to continue being itself.

**Status:** ✅ Constitutional - Immutable

---

## I. FOUNDATIONAL DOCUMENTS

These documents establish the core principles and laws governing the entire system.

### 1. [CONSTITUTION.md](./CONSTITUTION.md)

**Purpose:** The supreme law of the WMCP system

**Key Principles:**
* Immutability contracts
* World sovereignty
* Replay determinism
* Human governance supremacy

**Defines:**
* 4 Articles (Law, Simulation, Story, Governance)
* 3 Pillars (Primitives, Events, Myths/Scars)
* Constitutional amendments process

**Status:** ✅ Foundation - All systems must comply

---

### 2. [AFR_v1.0.md](./AFR_v1.0.md) - Absolute Foundation Repository v1.0

**Purpose:** First implementation of WFR primitives

**Contains:**
* 25 core primitives across 5 domains
* Primitive definitions with constraints
* Version control (v1.0.0)

**Defines:**
* Governance, Power, Economy, Culture, Magic domains
* Immutable primitive semantics

**Status:** ✅ Active - Current WFR version

**Related Systems:** WFR, Diversity Engine

---

## II. AI CONTRACTS & CONSTRAINTS

Documents that define what AI can and cannot do.

### 3. [ADR-0008_AI_ONTOLOGY_CONTRACT.md](./ADR-0008_AI_ONTOLOGY_CONTRACT.md)

**Purpose:** AI cannot create new ontology

**Key Rules:**
* AI must instantiate from primitives only
* Unknown concepts = ERROR, not creativity
* Ontology gaps require human governance

**Enforcement:**
* PrimitiveGuard (input gate)
* AIResponseValidator (output gate)

**Status:** ✅ Critical - Prevents AI from creating new primitives

**Related Systems:** WFR, Seed System, AI Generations

---

## III. WORLD FOUNDATION REPOSITORY (WFR)

Documents governing the primitive system and world diversity.

### 4. [WORLD_FOUNDATION_REPOSITORY.md](./WORLD_FOUNDATION_REPOSITORY.md)

**Purpose:** Define the primitive system architecture

**Key Concepts:**
* Primitives = immutable building blocks
* 5 domains structure
* Version control & governance

**Status:** ✅ Core - Implemented

**Related Docs:** AFR_v1.0, AI_ONTOLOGY_CONTRACT

---

### 5. [WORLD_DIVERSITY_ENGINE.md](./WORLD_DIVERSITY_ENGINE.md)

**Purpose:** Create controlled world diversity through combination rules

**Key Concepts:**
* 3 primitive types: Axis, Tension, Constraint
* 3 rule types: Compatibility, Tension, Emergence
* Diversity = disciplined difference

**Key Insight:**
> Diversity comes from **combination rules**, not primitive quantity

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** WFR, WorldBuilder

---

## IV. SEED GOVERNANCE

Documents governing narrative input control.

### 6. [SEED_GOVERNANCE.md](./SEED_GOVERNANCE.md)

**Purpose:** Control narrative inputs to worlds

**Key Rules:**
* Seed lifecycle (PENDING → ACTIVE → EXHAUSTED/CANCELLED)
* Concurrency limits (max 3 active per world)
* Governance approval required

**Enforcement:**
* SeedGuard
* Lifecycle state machine

**Status:** ✅ Implemented

**Related Systems:** World Events, AI Generations

---

## V. MYTH & SCAR SYSTEMS

Documents governing emergent and permanent consequences.

### 7. [MYTH_SCAR_GOVERNANCE.md](./MYTH_SCAR_GOVERNANCE.md)

**Purpose:** Distinguish between mutable myths and immutable scars

**Key Distinctions:**

| Aspect      | Myth              | Scar               |
| ----------- | ----------------- | ------------------ |
| Mutability  | Semi-mutable      | Immutable          |
| Source      | Belief/Culture    | World-level trauma |
| Decay       | Can fade/change   | Permanent          |
| Governance  | Read + Deprecate  | Read-only          |

**Status:** ✅ Implemented

---

### 8. [MYTH_THRESHOLD.md](./MYTH_THRESHOLD.md)

**Purpose:** Define when events become myths

**Key Formula:**
```
MythScore = 
  Impact * 0.35 +
  Irreversibility * 0.30 +
  Compression * 0.20 +
  Recurrence * 0.15

Threshold ≥ 0.7 → Myth Trace
```

**4 Axes:**
1. Impact - How many affected
2. Irreversibility - Can it be undone
3. Compression - Can it be symbolized
4. Recurrence - Can it echo in other worlds

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** WTR, Myth Propagation

---

### 9. [MYTH_STRENGTH_PIPELINE.md](./MYTH_STRENGTH_PIPELINE.md)

**Purpose:** Define myth strength levels and progression

**3 Levels:**
* Level 1 (0.2-0.4): Weak Echo - narrative flavor
* Level 2 (0.4-0.7): Cultural Anchor - taboos, rituals
* Level 3 (≥0.7): Active Myth - organized religion

**Key Rule:**
> Myths must progress 1 → 2 → 3, cannot skip levels

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** WTR, Myth Propagation

---

### 10. [MYTH_DECAY_SYSTEM.md](./MYTH_DECAY_SYSTEM.md)

**Purpose:** Define how myths weaken and die

**4 Decay Vectors:**
1. Contradiction Pressure - Reality proves myth wrong
2. Internal Hypocrisy - Elite violate myth
3. Trauma Override - Myth-driven disaster
4. Counter-Myth Emergence - Competing myth

**Key Principle:**
> Myths don't die from time, they die from being unable to live

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** WTR, Counter-Myth System

---

### 11. [MYTH_MANIPULATION_PROPAGANDA.md](./MYTH_MANIPULATION_PROPAGANDA.md)

**Purpose:** Define how factions manipulate myths

**4 Manipulation Types:**
1. Amplification - Increase myth exposure
2. Sanitization - Remove painful elements (creates truth debt)
3. Reframing - Change myth meaning (creates variants)
4. Suppression - Block transmission (accumulates trauma)

**Key Principle:**
> Those who control myths do not control truth. They only decide how long truth must wait.

**Cost System:**
* All manipulation has costs
* Truth debt always explodes eventually
* Suppression breeds violence

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Myth Decay, Conflict System

---

### 12. [MYTH_CONFLICT_SCHISM.md](./MYTH_CONFLICT_SCHISM.md)

**Purpose:** Define myth conflict and schism mechanics

**3 Conflict Types:**
1. Exclusive - Mutually incompatible myths
2. Hierarchical - Competing for domain supremacy
3. Interpretive - Same myth, different meanings

**State Machine:**
```
LATENT → ACTIVE → FRACTURED → RESOLVED
```

**Key Concepts:**
* Conflict = domain-based power struggle
* Schism = myth fork (Git-like)
* Major Epoch = conflict state transition

**Key Insight:**
> History breaks along the lines people refuse to cross

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Myth Manipulation, Major Epoch System

---

### 13. [CIVILIZATION_COLLAPSE_LEGACY.md](./CIVILIZATION_COLLAPSE_LEGACY.md)

**Purpose:** Define how myths survive civilization collapse

**4 Legacy Modes:**
1. Migrated - Carried by refugees to new worlds
2. Dormant - Stored as folklore, can resurrect
3. Archetypal - Compressed to pure pattern/emotion
4. Lost - Only in historical records

**Archetype System:**
* Strip doctrine, keep pattern + emotion
* Enable infinite stories without repetition
* AI sees archetypes, not original history

**Key Principle:**
> What survives is not the strongest, but what people still need

**3 Collapse Types:**
* Violent - Fragments myth into contradictory versions
* Exhaustion - Fades myth to folklore
* Ideological - Replaces myth with counter-myth

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** WTR, Archetype Pool, World Generation

---

## VI. META-LEVEL ORCHESTRATION

Documents governing multi-world orchestration and saga management.

### 14. [SAGA_RUNNER.md](./SAGA_RUNNER.md)

**Purpose:** Orchestrate multiple worlds sequentially with myth legacy transfer

**Key Concepts:**
* Meta-level control plane above Simulator
* World loop: Generate seed → Run → Collapse → Extract legacy
* Archetype extraction and transfer between worlds
* Saga as "many civilizations forgetting the same thing"

**Architecture:**
```
SagaRunner
 ├─ WorldSeedGenerator
 ├─ SimulationManager (existing)
 ├─ SagaObserver
 ├─ MythLegacyExtractor
 └─ SagaArchive
```

**Key Features:**
* Artisan command: `php artisan saga:run --worlds=5 --archetypes=... --carry=0.6`
* Event-sourced storage (saga_runs, saga_worlds, saga_legacies)
* Replay & divergence analysis
* AI isolation (AI doesn't know saga context)

**Domain Placement:**
```
app/Domains/Saga/  (new domain, above StoryEngine)
```

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** All subsystems (meta-orchestrator)

---

## VII. HISTORIAN MODE & READING HISTORY

Documents governing how to read and understand emergent history.

### 17. [HISTORIAN_MODE.md](./HISTORIAN_MODE.md)

**Purpose:** Read emergent history without directing it

**4 Layers of Reading:**
1. Chronicle View - Raw events
2. Pattern View - What repeats
3. Bias View - System memory influencing future
4. Counterfactual View - What could have been different

**Historian Queries:**
* ✅ Pattern queries: "What collapsed more than once?"
* ✅ Bias queries: "What archetypes increased weight?"
* ❌ Moral queries: "Which myth was correct?"

**Key Principles:**
* Historian observes, never directs
* No timeline tuyệt đối
* No narrator toàn tri
* Only fragments + patterns

**Myth Legacy Schema:**
```php
{
  archetype: "SilentGod",
  residue_type: "trauma | reverence | taboo | hope",
  intensity: 0.1 – 1.0,
  distortion: 0.0 – 1.0
}
```

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Saga Runner, WTR, Archetype Drift

---

## VIII. ARCHETYPE SYSTEM & SOCIAL SIMULATION

Documents governing the archetype system and its coupling with economy/power.

### 18. [ARCHETYPE_ECONOMY_POWER_COUPLING.md](./ARCHETYPE_ECONOMY_POWER_COUPLING.md)

**Purpose:** Define how archetypes legitimize economy and power

**Core Principle:**
> Archetype doesn't create actions. It creates how society accepts or justifies actions.

**Coupling Architecture:**
```
Archetype
   ↓ biases
Perception Filter
   ↓ legitimizes
Economy & Power Actions
   ↓ creates
Inequality / Scarcity / Violence
   ↓ feeds back
Archetype Weight Drift
```

**Legitimacy Formula:**
```
legitimacy = f(archetype_weight, myth_intensity) - economic_inequality - trauma_memory
```

**Key Insight:**
* Economy tệ nhưng xã hội chưa sụp (archetype strong)
* Power yếu nhưng tồn tại lâu (archetype backing)
* Collapse xảy ra bất ngờ (legitimacy = 0)

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Economy, Power, Myth System

---

### 19. [ARCHETYPE_DRIFT.md](./ARCHETYPE_DRIFT.md)

**Purpose:** Define how archetypes change over time due to history

**Core Definition:**
> Archetype Drift = sự trôi chậm của các lực nhận thức tập thể do lịch sử tích lũy

**4 Sources of Drift:**
1. Repetition Pressure - Overuse loses meaning
2. Trauma Residue - Legacy creates bias
3. Power Capture - Elite monopolize interpretation
4. Absence Pressure - Suppression causes overshoot

**Drift Formula:**
```
drift_delta = repetition + trauma + power_capture - restoration
weight = clamp(weight + drift_delta, 0.0, 1.0)
```

**Drift vs Mutation:**
* Drift: Slow, continuous, reversible, from history
* Mutation: Rare, sudden, permanent, from catastrophe

**Key Principle:**
* Drift along polarity axis (order ↔ chaos)
* Not random new meaning
* Triggers myth reinterpretation

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Archetype Pool, Myth System, Historian Mode

---

## IX. WORLD OS ARCHITECTURE

Documents defining the complete operating system architecture.

### 20. [ARCHETYPE_MUTATION.md](./ARCHETYPE_MUTATION.md)

**Purpose:** Define when and how archetypes can mutate

**Only 3 Mutation Triggers:**
1. Civilization Collapse (extreme)
2. Myth Paradox (irreconcilable)
3. Repeated Failure Across Sagas

**Mutation Mechanics:**
* Mutation = fork archetype, not create new
* Irreversible
* Spreads slowly
* Carries scar history

**Example:**
```
Before: "sacrifice"
After: "sacrifice_redemptive" + "sacrifice_extractive"
```

**Key Rule:**
> Mutation is not evolution. It is history admitting it cannot continue with the words it has.

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Archetype Drift, Civilization Collapse

---

### 21. [HUMAN_IN_THE_LOOP.md](./HUMAN_IN_THE_LOOP.md)

**Purpose:** Define what humans can and cannot do

**3 Allowed Actions:**
1. Seeding Bias (choose conditions)
2. Pressure Injection (blind pressure, not directed)
3. Selection (post-saga curation)

**Forbidden Actions:**
❌ Edit myth directly
❌ Set archetype weight
❌ Choose outcome
❌ Rewrite history

**Human Role:**
* Curator, not Author
* Choose what is remembered
* Not what happens

**Success Metric:**
> "Mình không hề viết đoạn này…"

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Saga Runner, Writer Console

---

### 22. [WORLD_OS.md](./WORLD_OS.md)

**Purpose:** Define complete operating system architecture

**4-Layer Architecture:**
```
Human Layer (Writer)
    ↓
Historian Layer (Memory)
    ↓
World Runtime (Simulation)
    ↓
Cognitive Kernel (Immutable)
```

**Cognitive Kernel (Locked):**
1. Archetype System
2. World Law System
3. Coupling Rules

**Key Principle:**
* Kernel is immutable from above
* Runtime can upgrade, Kernel cannot
* Layers must not merge

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** All systems (meta-architecture)

---

### 23. [WORLD_OS_PRODUCT_DIRECTION.md](./WORLD_OS_PRODUCT_DIRECTION.md)

**Purpose:** Define product strategy and user experience

**Mission Statement:**
> World OS enables writers to explore stories by cultivating worlds, not by scripting plots.

**Two User Tiers:**
1. Primary: Writer/IP Builder (priority)
2. Secondary: Researcher/System Thinker (hidden)

**Writer-Facing Mental Model:**
* Never show: archetype weight, drift formula
* Only show: World Mood, Cultural Tension, Belief Pressure

**Product Tiers:**
1. Writer Platform (SaaS)
2. Research Platform (Academic)
3. Core Engine (Open-source)

**Success Metrics:**
* Writer: "World có chiều sâu"
* Researcher: "Hệ tự sinh giả thuyết"

**Status:** 📝 Design Complete - Roadmap Defined

**Related Systems:** All systems (product wrapper)

---

## X. WORLD TRACE REPOSITORY (WTR)

Documents governing system memory and learning.

### 15. [WORLD_TRACE_REPOSITORY.md](./WORLD_TRACE_REPOSITORY.md)

**Purpose:** System-level historical memory

**4 Trace Types:**
1. Pattern Trace - What usually happens
2. Myth Origin Trace - Events that became myths
3. Failure Trace - Why worlds collapsed
4. Stability Trace - What helps worlds survive

**Key Distinction:**
* WFR = What worlds CAN be
* WTR = What worlds HAVE been

**Status:** 📝 Design Complete - Implementation Pending

**Related Systems:** Seed Bias, Myth Propagation, Governance Dashboard

---

### 16. [WTR_IMPLEMENTATION_STRATEGY.md](./WTR_IMPLEMENTATION_STRATEGY.md)

**Purpose:** Define safe implementation order for WTR

**3 Pillars:**
1. **Phase 1:** Trace → Governance Dashboard (read-only)
2. **Phase 2:** Trace → Seed Bias Engine (soft influence)
3. **Phase 3:** Trace → Myth Propagation (story-facing)

**Key Insight:**
> Not "choose one" but "all three" in correct order

**Status:** 📝 Implementation Roadmap - Ready to Execute

**Related Systems:** WTR, All subsystems

---

## 📊 SYSTEM DEPENDENCY MAP

```
CONSTITUTION (Foundation)
    ↓
├─> WFR (AFR_v1.0)
│   ├─> AI_ONTOLOGY_CONTRACT
│   └─> WORLD_DIVERSITY_ENGINE
│
├─> SEED_GOVERNANCE
│   └─> Seed Lifecycle
│
├─> MYTH_SCAR_GOVERNANCE
│   ├─> MYTH_THRESHOLD
│   ├─> MYTH_STRENGTH_PIPELINE
│   ├─> MYTH_DECAY_SYSTEM
│   ├─> MYTH_MANIPULATION_PROPAGANDA
│   ├─> MYTH_CONFLICT_SCHISM
│   └─> CIVILIZATION_COLLAPSE_LEGACY
│
├─> WTR
│   └─> WTR_IMPLEMENTATION_STRATEGY
│       ├─> Seed Bias Engine
│       ├─> Myth Propagation
│       └─> Governance Dashboard
│
└─> SAGA_RUNNER (Meta-Orchestrator)
    ├─> WorldSeedGenerator
    ├─> MythLegacyExtractor
    └─> SagaObserver
    └─> WTR_IMPLEMENTATION_STRATEGY
        ├─> Seed Bias Engine
        ├─> Myth Propagation
        └─> Governance Dashboard
```

---

## 🎯 IMPLEMENTATION STATUS

### ✅ Implemented (6 docs)
1. CONSTITUTION
2. AFR_v1.0 (WFR)
3. AI_ONTOLOGY_CONTRACT (PrimitiveGuard)
4. WORLD_FOUNDATION_REPOSITORY
5. SEED_GOVERNANCE
6. MYTH_SCAR_GOVERNANCE

### 📝 Design Complete - Pending Implementation (17 docs)
7. WORLD_DIVERSITY_ENGINE
8. MYTH_THRESHOLD
9. MYTH_STRENGTH_PIPELINE
10. MYTH_DECAY_SYSTEM
11. MYTH_MANIPULATION_PROPAGANDA
12. MYTH_CONFLICT_SCHISM
13. CIVILIZATION_COLLAPSE_LEGACY
14. SAGA_RUNNER
15. WORLD_TRACE_REPOSITORY
16. WTR_IMPLEMENTATION_STRATEGY
17. HISTORIAN_MODE
18. ARCHETYPE_ECONOMY_POWER_COUPLING
19. ARCHETYPE_DRIFT
20. ARCHETYPE_MUTATION
21. HUMAN_IN_THE_LOOP
22. WORLD_OS
23. WORLD_OS_PRODUCT_DIRECTION

---

## 🛣️ RECOMMENDED IMPLEMENTATION ORDER

### Phase 1: Foundation (Complete ✅)
* WFR + Primitives
* Seed Governance
* Basic Myth/Scar tracking

### Phase 2: Diversity & Rules (Next)
* Diversity Engine (Axis/Tension/Constraint)
* Combination Rules
* Emergence Rules

### Phase 3: WTR Infrastructure
* Trace extraction service
* WTR Dashboard (read-only)
* Pattern recognition

### Phase 4: Advanced Myth System
* Myth Threshold calculator
* Myth Strength progression
* Myth Decay engine
* Myth Manipulation & Propaganda engine
* Myth Conflict & Schism system
* Counter-myth competition

### Phase 5: Learning Loop
* Seed Bias Engine
* Myth Propagation System
* Civilization Collapse handling
* Archetype Pool system
* Complete WTR integration

### Phase 6: Meta-Orchestration
* Saga Runner implementation
* WorldSeedGenerator (archetype-biased)
* MythLegacyExtractor
* SagaObserver & SagaArchive
* Artisan command interface

---

## 🔗 CROSS-REFERENCES

### When working on WFR:
Read: AFR_v1.0, AI_ONTOLOGY_CONTRACT, WORLD_DIVERSITY_ENGINE

### When working on Seeds:
Read: SEED_GOVERNANCE, WTR (for bias)

### When working on Myths:
Read: MYTH_SCAR_GOVERNANCE, MYTH_THRESHOLD, MYTH_STRENGTH_PIPELINE, MYTH_DECAY_SYSTEM, MYTH_MANIPULATION_PROPAGANDA, MYTH_CONFLICT_SCHISM, CIVILIZATION_COLLAPSE_LEGACY

### When working on WTR:
Read: WORLD_TRACE_REPOSITORY, WTR_IMPLEMENTATION_STRATEGY

### When working on Saga:
Read: SAGA_RUNNER, CIVILIZATION_COLLAPSE_LEGACY, MYTH_THRESHOLD, WTR

### When working on Historian:
Read: HISTORIAN_MODE, SAGA_RUNNER, ARCHETYPE_DRIFT, WTR

### When working on Archetype System:
Read: HISTORIAN_MODE, ARCHETYPE_ECONOMY_POWER_COUPLING, ARCHETYPE_DRIFT, ARCHETYPE_MUTATION, MYTH_THRESHOLD

### When working on World OS:
Read: WORLD_OS, WORLD_OS_PRODUCT_DIRECTION, HUMAN_IN_THE_LOOP

### When working on Writer Experience:
Read: WORLD_OS_PRODUCT_DIRECTION, HUMAN_IN_THE_LOOP, SAGA_RUNNER

---

## 📚 GOVERNANCE PRINCIPLES SUMMARY

### Immutability Hierarchy
1. **Constitution** - Hardest to change
2. **WFR Primitives** - Version-controlled, proposal system
3. **Scars** - Immutable per-world
4. **Myths** - Semi-mutable, decay allowed
5. **Seeds** - Controllable lifecycle

### AI Boundaries
* ✅ AI can: Instantiate, combine, infer within primitives
* ❌ AI cannot: Create primitives, modify ontology, override governance

### Human Governance
* All primitive changes require human approval
* All governance document changes require ADR
* Operators have final authority over system behavior

---

## 📖 HOW TO USE THIS INDEX

**For Developers:**
1. Start with CONSTITUTION
2. Read relevant domain docs (WFR, Seeds, Myths, WTR)
3. Check implementation status
4. Follow recommended implementation order

**For Operators:**
1. Understand CONSTITUTION principles
2. Use governance dashboards (when implemented)
3. Review trace patterns (Phase 3+)
4. Propose primitive changes through WFR

**For Designers:**
1. Study WORLD_DIVERSITY_ENGINE for world creation
2. Reference MYTH system docs for narrative design
3. Use WTR patterns for inspiration

---

## 🔄 VERSION HISTORY

**v1.0 (2026-02-10)**
* Initial comprehensive governance framework
* 12 documents covering all core systems
* Implementation roadmap defined

**v2.0 (2026-02-10)**
* Expanded to 15 documents
* Complete Civilizational Memory Engine framework
* Added: Myth Manipulation & Propaganda
* Added: Myth Conflict & Schism Engine
* Added: Civilization Collapse & Legacy System
* Archetype Pool for cross-world memory

**v2.1 (2026-02-10)**
* Expanded to 16 documents
* Added Meta-Level Orchestration layer
* Added: Saga Runner (multi-world orchestrator)
* Domain architecture: app/Domains/Saga/
* Artisan command interface for saga execution

**v2.2 (2026-02-10)**
* Expanded to 19 documents
* Added Social Simulation layer
* Added: Historian Mode (reading emergent history)
* Added: Archetype-Economy-Power Coupling
* Added: Archetype Drift (civilizational aging)
* System transitions from logic simulation to social simulation

**v3.0 (2026-02-10)**
* Expanded to 23 documents
* Complete World OS architecture
* Added: Archetype Mutation (structural change)
* Added: Human-in-the-Loop (curator interface)
* Added: World OS (4-layer architecture)
* Added: World OS Product Direction (market strategy)
* System becomes complete operating system for emergent history

---

## FOUNDATION TRUTH

> **A system without governance is chaos.
> A system with too much governance is death.
> This framework seeks the narrow path between.**
