# World OS Constitution

> This document defines the **constitutional invariants** of World OS.
> These ADRs are not implementation details.
> They are **constraints on how the system is allowed to think**.

---

## ADR-1000: World OS Architecture

### Status

**Accepted**

### Context

World OS is designed to generate **emergent history**, not authored narratives. Without a strict architectural separation, the system risks collapsing into a storyteller or simulation toy.

### Decision

World OS SHALL be structured into four immutable layers:

1. **Cognitive Kernel** – archetypes, world laws, drift
2. **World Runtime** – simulation, AI, economy, power
3. **Historian Layer** – memory, pattern detection
4. **Human Layer** – bias seeding, selection, canonization

**Lower layers MUST NOT depend on higher layers.**

### Consequences

* Kernel stability enables long-term history
* Runtime and AI may evolve independently
* Human influence is bounded

---

## ADR-1001: Cognitive Kernel Invariants

### Status

**Accepted**

### Context

The Kernel defines the system's concept of reality and human perception. If mutable at runtime, historical continuity collapses.

### Decision

The following Kernel elements are **immutable per major version:**

* Archetype Pool (keys, domains)
* Archetype Polarity definitions
* Drift mechanics
* Mutation rules (fork-only, irreversible)
* World Law categories (power ceiling, constraint classes)

**Only weights and lineage may change over time.**

### Consequences

* History ages instead of resetting
* AI upgrades do not rewrite meaning
* Worlds share deep continuity

---

## ADR-1002: Archetype Lifecycle

### Status

**Accepted**

### Context

Archetypes must evolve to reflect history, but uncontrolled evolution destroys recognizability.

### Decision

Archetypes follow a **strict lifecycle:**

1. **Baseline** – initial weight
2. **Drift** – slow directional change via history
3. **Reinterpretation** – myth threshold crossing
4. **Mutation** – rare fork under collapse conditions

**Archetypes MUST NOT be deleted.**

### Consequences

* Familiar but altered worlds
* Long-term thematic depth
* Emergent moral divergence

---

## ADR-1003: Human-in-the-loop Contract

### Status

**Accepted**

### Context

Human authors are necessary but dangerous. Unlimited control collapses emergence into authorship.

### Decision

**Humans MAY:**
* Seed initial biases
* Inject blind pressures
* Select and canonize outcomes

**Humans MUST NOT:**
* Modify archetype weights directly
* Alter simulation outcomes
* Rewrite history retroactively

### Consequences

* Authors curate rather than dictate
* Surprise is preserved
* Canon becomes meaningful

---

## ADR-1004: Historian Non-Interference Rule

### Status

**Accepted**

### Context

Historical interpretation must not influence historical causation.

### Decision

The Historian Layer **SHALL:**
* Observe
* Record
* Compare
* Detect patterns

It **SHALL NOT:**
* Influence world state
* Inform AI generation
* Bias future simulations

### Consequences

* Clean separation of memory and causality
* Reliable pattern analysis
* Prevention of narrative feedback loops

---

## Closing Statement

> **World OS does not remember in order to improve.**
> **It remembers in order to continue being itself.**

---

## Constitutional Hierarchy

These ADRs have **constitutional status** and supersede all other governance documents.

**Amendment Process:**
1. Requires unanimous consensus
2. Major version increment
3. Historical continuity audit
4. Migration path for existing worlds

**Non-negotiable Constraints:**
* Kernel immutability
* Layer separation
* Human boundaries
* Historian non-interference
* Archetype lifecycle preservation
