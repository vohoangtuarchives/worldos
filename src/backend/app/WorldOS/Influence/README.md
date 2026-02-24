# Influence Module
## 📋 Overview
Aggregates all macro-level pressures (Scars, Myths, Attractors) into a single `VectorForce` via the `InfluencePipeline`. This combined force mutates the Universe's state vector during a simulation tick.

## 🏗️ Architecture
- **Service Layer**: `InfluencePipeline` aggregator
- **Contracts**: `EvolutionInfluenceInterface`, `NarrativePressureBridgeInterface`
- **Value Objects**: `VectorForce`, `EvolutionContext`

## 📐 Structure
- `Influences/` - Implementations like `ScarInfluence`, `MythInfluence`
- `Services/` - `InfluencePipeline`

## 🔗 Integration
- Called by **Runtime** `TickUniverseAction` (step 5).
- Takes input from **CivilizationMemory** and **Attractor**.
- Sends output to **Runtime** `UniverseMutationService`.
