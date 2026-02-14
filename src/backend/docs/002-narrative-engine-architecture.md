# ADR-002: Narrative Engine Architecture

## Context
The system requires a robust Narrative Engine capable of generating long-form stories (Novel/Light Novel) with satisfying consistency, character depth, and "living" interactions. Simple LLM prompting is insufficient for maintaining state, hidden information, and causal logic over long arcs.

## Decision
We will adopt a **Domain-Driven Design (DDD)** approach where the AI (LLM) acts as a **Generator Adapter**, but the **Domain Logic** controls the State, Rules, and Flow.

### 1. Core Principles
*   **Character as Aggregate Root**: Characters are not text profiles. They are stateful entities with `Beliefs`, `Memories`, `Goals`, and `Emotions`.
*   **Dialogue as State Machine**: Conversations are not loops. They are state transitions driven by `Intent` and governed by `Guards`.
*   **Causal Consistency**: Use a Directed Acyclic Graph (DAG) for Timelines. Past events cannot be overwritten; they must be forked.
*   **Intent-Based Guarding**: The Guard layer validates *Intent* (structural data), not raw text.

### 2. Architecture Components

#### A. Character Agent (The Being)
*   **Entity**: `Character` (Aggregate Root).
*   **Components**:
    *   `MemoryCollection`: Separated into `Semantic` (Facts) and `Episodic` (Experiences).
    *   `EmotionState`: Dynamic intensity that decays/amplifies based on Triggers.
    *   `GoalStack`: Hierarchical motivations.
*   **Interaction**: `react(SceneStimulus) -> Intent`.

#### B. Dialogue Engine (The Interaction)
*   **TurnScheduler**: Determines who speaks based on `Goal Threat` and `Emotion Intensity`.
*   **IntentExtractor**: Converts LLM output into structured Intent (e.g., `PROBE_INFO`, `DEFLECT`).
*   **ConsistencyGuard**: Validates Intent against Canon, Knowledge, and Beliefs.
*   **SceneUpdater**: Applies valid Intent to mutate State (Memory, Emotion, Relationship).

#### C. Timeline (The Spine)
*   **DAG Structure**: `TimelineNode` parent/child relationships.
*   **Snapshots**: State is snapshotted at critical nodes.
*   **Forking**: Allows "What If" scenarios without breaking the Main timeline.

### 3. Implementation Phases
1.  **Phase 9: Character Core**: Implement `Character` Aggregate, `Memory`, `Emotion`.
2.  **Phase 10: Dialogue Engine**: Implement `Scheduler`, `Intent`, `Guard`, `SceneUpdater`.
3.  **Phase 11: Timeline DAG**: Implement Graph structure and Causal Consistency checks.

## Consequences
*   **Complexity**: Significantly higher initial engineering effort compared to simple RAG.
*   **Stability**: Eliminates "hallucinations" regarding facts/canon.
*   **Scalability**: Supports infinite rewriting, forking, and long-term consistency.
