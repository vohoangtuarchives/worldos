# WorldOS V3: System Architecture

## Overview
WorldOS V3 is an event-driven simulation engine designed to model complex civilization evolution through the interaction of "Materials" (societal concepts) and "Heroes" (historical figures).

## Core Architectural Patterns

### 1. Domain-Driven Design (DDD)
The codebase is structured into distinct Domains to enforce separation of concerns:
- **Evolution**: The simulation kernel.
- **Cosmology**: The state of the universe.
- **Material**: The engine of societal change.
- **Vietnamese**: The specific historical context.

### 2. Event-Driven Architecture
To decouple complex interactions, V3 uses Laravel Events:
- **`WorldTicked`**: Dispatched by `WorldEvolutionKernel` after each simulation step.
- **Listeners**:
    - `CheckHeroSpawningListener`: Listens to `WorldTicked`. Checks if the world state justifies a Hero Spawn.
    - `ProcessMaterialMutations`: (Planned) Listens to changes to trigger new inventions.

### 3. The Kernel Loop
The `WorldEvolutionKernel` is the heart of the system.
1.  **Physics Tick**: Updates base parameters (Age, Entropy).
2.  **Material Tick**: Applies pressure from active materials to the World State.
3.  **Event Dispatch**: `WorldTicked` is fired.
4.  **Resonance**: Listeners allow other domains (like `Vietnamese`) to react and inject changes back into the world (e.g., spawning a Hero).

## Database Schema Highlights

### World & Universe
- `worlds`: Meta-container.
- `universes` (Runtime Instances): JSONB `state_vector` stores the fluid simulation state.

### Materials
- `material_templates`: Reference definitions (e.g. *Gunpowder*).
- `material_instances`: Runtime instances in a specific world. Contains `mutation_state` and `strength_level`.

### Heroes
- `vietnamese_heroes`: Historical seed data.
- `world_heroes`: Runtime instances. Can be historical clones or procedurally generated.
