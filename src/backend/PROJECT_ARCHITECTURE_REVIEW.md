# Unified Myth World Engine - Comprehensive Architecture Review

**Date**: 2026-02-10
**Version**: 3.0 (Includes Service Logic Detail)
**Status**: Backend Complete

---

## 1. Executive Summary
The **Unified Myth World Engine** is a dual-layer simulation platform designed to generate high-fidelity narrative history. It is composed of two distinct but interconnected engines:
1.  **The Myth Engine** (Micro-Level): Simulates individual psychology, dialogue, and belief systems.
2.  **The Simulation Engine** (Macro-Level): Simulates geopolitical forces, economics, and history.

The system uses **Event Sourcing** for replayability and **Domain-Driven Design** to separate concerns between the "World" (Physics) and the "Story" (Experience).

---

## 2. Service Logic Inventory (The Business Layer)

This section details the specific logic handled by Domain Services, separating "Data Structure" from "Active Logic".

### A. Narrative Services (The Brain)
*Located in `App\Domains\Narrative\*\Services`*

#### 1. `DialogueEngine`
*   **Role**: Contextual Text Generation Orchestrator.
*   **Logic**:
    *   **Context Building**: Aggregates `Character` memory, `Scene` history, and `World` state into a massive System Prompt.
    *   **LLM Integration**: Calls OpenAI/DeepSeek via `LLMProvider`.
    *   **Intent Parsing**: Converts raw text (e.g., "I draw my sword!") into structured data (`Intent::ATTACK`).
    *   **Guard Rails**: Uses `ConsistencyGuard` to reject outputs that contradict a character's `GoalStack` (e.g., a Pacifist won't murder without extreme pressure).

#### 2. `TurnScheduler`
*   **Role**: Dynamic Turn Management (Not simple Round-Robin).
*   **Logic**: calculates "Pressure" score for every agent in a scene: `Pressure = (Goal Priority + Emotion Intensity + Random Noise)`. The agent with the highest pressure acts next. This simulates "interrupting" and "dominating" conversations.

#### 3. `CausalConsistency`
*   **Role**: Time Travel safety.
*   **Logic**: When a user tries to modify a past event (Node A), this service checks if the modification contradicts any future events (Node B) by traversing the `TimelineDAG`. If a contradiction is found, it forces a **Fork** instead of an edit.

### B. World Services (The Meta-Physics)
*Located in `App\Domains\World\Services`*

#### 4. `MythEmergenceService`
*   **Role**: From Belief to Reality.
*   **Logic**: Scans `WorldBelief` records. If a belief (e.g., "The King is Divine") is repeated > `EMERGENCE_THRESHOLD` (3 times), it "crystallizes" into a `WorldMyth`. This myth then grants passive buffs/debuffs to the world.

#### 5. `WorldForkService`
*   **Role**: Multiverse Management.
*   **Logic**:
    *   Creates a new `World` record.
    *   **Deep Copies** all `WorldEvent` records up to the fork tick.
    *   **Remaps** `WorldScar` records to the new events.
    *   This ensures the new timeline starts identical to the old one but is now independent.

### C. Story Engine Services (The Simulation)
*Located in `App\StoryEngine\Services`*

#### 6. `StoryContentGenerator`
*   **Role**: The Narrator.
*   **Logic**: Takes the abstract `Seed` (e.g., `POWER_GAP`, Severity: 5) and the `WorldState` (Public Awareness: 10), and generates a prose chapter title and content. It uses "Xianxia" (Cultivation) terminology by default via its System Prompt.

#### 7. `ReplayEngine`
*   **Role**: The Time Machine.
*   **Logic**:
    *   Clears current `WorldState` (Memory/Economy).
    *   Fetches all `WorldEvent` records for a specific timeline.
    *   Iterates through them sequentially, calling `Event::apply($worldState)`.
    *   Result: The exact state of the world at any point in history, without needing to store snapshots for every tick.

#### 8. `BalancingApplier` (The Heavenly Way)
*   **Role**: Automated Game Master.
*   **Logic**:
    *   Calculates `WorldHealth` (Danger Score).
    *   **Injection**: If Danger > 70, injects `TEMPORARY_TRUCE`. If Danger < 20, injects `ANCIENT_RUIN_DISCOVERY`. 
    *   This acts as a negative feedback loop to keep the simulation stable.

---

## 3. Key Workflows (How Services Interact)

### The "Legend is Born" Workflow
1.  **Myth**: `MythEmergenceService` creates a new Myth ("The Dragon lives").
2.  **Simulation**: `Simulator` picks up this Myth.
3.  **Effect**: `RuleApplier` increases the probability of `Seed::MYSTERY` appearing.
4.  **Narrative**: `StoryContentGenerator` sees the active Myth and mentions "The Dragon" in the next chapter's text.

### The "Time Travel" Workflow
1.  **User Action**: User wants to change Chapter 5 outcome.
2.  **Check**: `CausalConsistency` sees Chapter 6 depends on Chapter 5.
3.  **Fork**: `WorldForkService` creates "Timeline B", copying events 1-5.
4.  **Re-Simulate**: `Simulator` runs from Chapter 5 in Timeline B with the new outcome.

---

## 4. Conclusion
The codebase is structured around **Deep Logic**.
*   **Narrative Services** handle *Quality* (Coherence, Personality).
*   **World Services** handle *Reality* (Time, Myths, Scars).
*   **Simulation Services** handle *Flow* (Events, Balancing).

The Logic Layer is complete and robust.
