# World Module
## 📋 Overview
Manages the `WorldEntity`, which serves as the immutable blueprint (genotype) for universes. Contains the core `LawVector`.

## 🏗️ Architecture
- **Domain Layer**: `WorldEntity`, `WorldId`, `WorldStatus`
- **Infrastructure Layer**: `WorldEloquentRepository`, `WorldModel`
- **Application Layer**: Create, List, Halt, Kill actions

## 📐 Structure
- `Actions/` - CRUD ops
- `Contracts/` - Interfaces
- `Entities/` - WorldEntity
- `Providers/` - WorldServiceProvider

## 🔗 Integration
- Used by **Runtime** to spawn new universes.
