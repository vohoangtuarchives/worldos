# Cosmology Module
## 📋 Overview
The core physics engine. Responsible for evolving the `WorldStateVector` across ticks based on the `LawVector`. Includes Cascade (layer emergence) and Stability analysis.

## 🏗️ Architecture
- **Domain Layer**: Contracts (`PhysicsEngineInterface`, `CascadeEngineInterface`)
- **Application Layer**: `WorldEvolutionKernel` orchestrator

## 📐 Structure
- `Services/` - Kernel orchestration
- `Contracts/` - Engine interfaces

## 🔗 Integration
- Called by **Runtime** (`TickUniverseAction`).
- Implemented by **SimulationEngine** (which provides concrete math).
