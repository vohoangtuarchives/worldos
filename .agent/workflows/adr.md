---
description: Foundation of project
---

# Unified Myth World Engine: Core Architecture Rules

This document establishes the foundational rules for the project, synthesized from `ADR-000X`. All subsequent ADRs and technical implementations must adhere to these invariants.

## 1. The World Clock (Immutable Physics)
*   **Rule 1.1: Absolute Continuity.** The World Clock **NEVER** stops, pauses, resets, or rolls back.
*   **Rule 1.2: Immutable History.** Events cannot be deleted or undone. They can only be overlaid by new Events or Scars.
*   **Rule 1.3: Time Sovereignty.** Narrative flow cannot override World Clock time. The physics of time takes precedence over the needs of the story.

## 2. Belief, Myth, & Scars (The Engine)
*   **Rule 2.1: Mechanistic Emergence.** A "Myth" is not created arbitrarily. It only emerges when:
    1.  **Belief** is repeated over time.
    2.  It is **Shared** by multiple independent entities.
    3.  It produces measurable **Behaviors** (Events/Scars).
*   **Rule 2.2: Permanence of Scars.** Scars are the permanent sediment of history. They cannot be erased. Accumulated Scars increase the "inertia" of reality.
*   **Rule 2.3: Power Without Control.** No system rule guarantees that a Myth or Power will achieve its intended outcome.
    *   *Formula:* `Power ∝ Scar Accumulation` ; `Control ∝ 1 / Complexity`.
    *   High Power implies Low Control.

## 3. The Observer (Epistemology)
*   **Rule 3.1: Observation as Intervention.** There is no "neutral" or "objective" view. Every observation creates a specific **Observer Version** with inherent bias.
*   **Rule 3.2: Observer/AI Constraints.** Observers (including AI/System Loggers) are strictly **FORBIDDEN** from:
    *   Generating Events directly.
    *   Modifying System Rules.
    *   Declaring an "Absolute Truth" or "Canon".
    *   Hiding, blurring, or deleting Scars.
*   **Rule 3.3: Versioning.** The system tracks multiple **World Versions** (perspectives), not a single "True" Reality.

## 4. Narrative & Story (Interpretation)
*   **Rule 4.1: Separation of World and Story.**
    *   **World**: The underlying physics, rules, and clock (Engine).
    *   **Story**: The interpretation of events by Observers (Narrative).
    *   *Constraint:* The World never changes its rules to suit the Story.
*   **Rule 4.2: Anti-Trope Protection.**
    *   **No Deus Ex Machina:** No external force saves the day without cost.
    *   **No Plot Armor:** Characters/Entities survive only by system rules, not narrative necessity.
    *   **No Retcon:** You cannot rewrite the past; you can only re-interpret it via a new Myth.
*   **Rule 4.3: Valid Inertia.** The state of "Inertia" (no new Beliefs/Myths) is a valid system state. The World Clock continues to tick, and history lengthens, even if "nothing happens."

## 5. Creator Constraints
*   **Rule 5.1: Non-Omnipotence.** The Creator is a participant, not a master. Every Creator intervention generates a Scar.
*   **Rule 5.2: No Reset.** There is no "New Game" or "Format" option. The world must survive its own mistakes.
*   **Rule 5.3: Silence is Valid.** The Creator's silence does not stop the World Clock.

## 6. Implementation Principles
*   **Rule 6.1:** **Log Everything.** All Observer biases and versions must be logged.
*   **Rule 6.2:** **Data Integrity.** Scars and History are append-only.
*   **Rule 6.3:** **Algorithm Determinism.** The Myth Emergence Engine must be purely deterministic based on input Belief/Events, not randomized or guided by "AI creativity."
