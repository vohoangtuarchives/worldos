# Runtime Module
## 📋 Overview
Manages the `UniverseEntity`, representing a single running simulation instance. Handles tick orchestration, snapshot creation, and state mutation.

## 🏗️ Architecture
- **Domain Layer**: `UniverseEntity`
- **Application Layer**: `TickUniverseAction`, `AdvanceUniverseAction`, `UniverseMutationService`
- **Infrastructure Layer**: Snapshots and Universe repos

## 📐 Structure
- `Actions/` - Tick orchestration
- `Services/` - MutationService (single gate)
- `Events/` - `UniverseTicked`, `UniverseCollapsed`

## 🔗 Integration
- Relies on **World** for rules.
- Delegates evolution to **Cosmology**.
- Mutated by **Influence**.
