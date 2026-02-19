# The Material Engine

## Concept
In WorldOS, "Materials" are not just physical resources but "Active Concepts". *Democracy*, *Gunpowder*, *Rice Farming* are all Materials.

## Core Mechanics

### 1. Pressure System
Materials exert "Pressure" on the World State Vector.
- **Input**: What the material needs to survive (e.g., *Industrialization* needs *Order*).
- **Output**: What the material produces (e.g., *Industrialization* produces *Pollution* (Entropy) and *Goods* (Stability)).

### 2. Mutation Logic
Materials evolve through directed acyclic graphs (DAGs).
- **Triggers**: Conditions like "Time passed > 100 years" or "Entropy > 0.5".
- **Pathways**:
    - *Oral Tradition* -> *Writing System* (Requires: High Cohesion).
    - *Writing System* -> *Printing Press* (Requires: High Order).
- **Implementation**: `MaterialEngine` checks active instances each tick for mutation readiness.

### 3. Seeding
The `AdvancedMaterialSeeder` initializes a world with basic materials based on its "Origin Type" (e.g., Vietnamese origin gets *Rice Farming*, *wet_rice_civilization*).
