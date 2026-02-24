# Style Module
## 📋 Overview
Handles Narrative Genre to Physics mapping. Modifies a World's `LawVector` on-the-fly (`UniverseStyleEntity->calculatePhysicsOverlay`) to inject genre tropes (e.g. higher mutation in Xianxia, higher entropy in Cyberpunk) into the simulation logic.

## 🏗️ Architecture
- **Domain Layer**: `UniverseStyleEntity`, `StyleVector`, `GenreKey`
- **Infrastructure Layer**: Style Repo and Models

## 📐 Structure
- `Entities/` - `UniverseStyleEntity`
- `ValueObjects/` - 4D `StyleVector`

## 🔗 Integration
- Injected into **Runtime** `TickUniverseAction` (step 3) to override the active World blueprint.
