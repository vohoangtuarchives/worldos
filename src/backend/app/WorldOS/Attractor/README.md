# Attractor Module
## 📋 Overview
Defines macroscopic gravity wells ("Attractors") representing stable narrative states or historical inevitabilities (e.g., "The Final War", "The First Singularity").

## 🏗️ Architecture
- **Domain Layer**: `AttractorEntity`, `AttractorType`
- **Service Layer**: `BifurcationManager` (evaluates path splits)

## 📐 Structure
- `Entities/` - AttractorEntity
- `Services/` - Bifurcation logic

## 🔗 Integration
- Attractors act as forces pulling the simulation. They feed into the **Influence** pipeline via `AttractorInfluence`.
