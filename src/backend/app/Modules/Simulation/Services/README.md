# Simulation Engine Module
## 📋 Overview
Implements the abstract physics contracts defined in **Cosmology**. Provides the concrete mathematical models for evolving a `WorldStateVector` over time using the 17D `LawVector` as parameters.

## 🏗️ Architecture
- **Infrastructure Layer**: Concrete implementations of Domain contracts (`PhysicsEngineInterface`, `CascadeEngineInterface`, etc).

## 📐 Structure
- `Physics/` - `BasePhysicsEngine`
- `Cascade/` - `CascadeEvolutionEngine`
- `Stability/` - `StabilityAnalyzer`
- `Feasibility/` - `FeasibilityChecker`

## 🔗 Integration
- Bound to **Cosmology** contracts via `SimulationEngineServiceProvider`.
