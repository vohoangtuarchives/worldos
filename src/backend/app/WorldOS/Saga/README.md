# Saga Module
## 📋 Overview
The orchestration layer above Universes. A Saga runs multi-universe experiments, handles branching timelines (via Forks), and coordinates the evolution of multiple interconnected simulations toward an outcome.

## 🏗️ Architecture
- **Domain Layer**: `SagaEntity`, `SagaId`, `SagaStatus`
- **Application Layer**: Actions for orchestration (`AdvanceSagaAction`, `CreateSagaAction`)

## 📐 Structure
- `Actions/` - Orchestration logic
- `Entities/` - SagaEntity

## 🔗 Integration
- Runs multiple **Runtime** (`TickUniverseAction`) operations in sequence.
- Receives commands from the API or Artisan Console.
