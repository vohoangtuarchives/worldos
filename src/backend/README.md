# WorldOS V3: The Simulation Engine 🌍

**WorldOS** is a high-fidelity simulation engine designed to model the evolution of civilizations, historical resonance, and material dynamics. Version 3 introduces a robust Event-Driven Architecture and Domain-Driven Design to handle complex interactions between historical figures (Heroes) and societal forces (Materials).

## 🏗 Architecture

The system is built on **Laravel 11** and follows a strict **Domain-Driven Design (DDD)** approach.

### Domains
- **Evolution**: The core simulation loop (`WorldEvolutionKernel`). Handles physics, time ticks, and state transitions.
- **Cosmology**: Manages the `WorldStateVector` (Entropy, Order, Cohesion, Stability) and Universe snapshots.
- **Material**: Handles the existence, mutation, and interaction of "Materials" (societal concepts like *Nation State*, *Gunpowder*, *Confucianism*).
- **Vietnamese**: Captures the specific historical context, Hero archetypes (`VietnameseHero`), and cultural resonance.

### Key Patterns
- **Event-Driven**: The simulation enables loose coupling.
    - `WorldTicked`: Dispatched after every tick. Listeners check for spawn conditions.
    - `MaterialMutated`: Dispatched when a material evolves.
- **Bridge Pattern**: Services like `HeroMaterialBridge` and `MaterialWorldBridge` connect isolated domains without tight coupling.

---

## 🚀 Key Features

### 1. The Material Engine
Materials are not just resources; they are **active agents** of change.
- **Pressure System**: Materials exert pressure on the world (e.g., *Gunpowder* increases *Violence*).
- **Mutation**: Materials evolve over time (e.g., *Oral Tradition* -> *Writing System* -> *Printing Press*).
- **Seeders**: `AdvancedMaterialSeeder` populates the world with historical concepts.

### 2. Hero-Material Resonance (HMR)
A bi-directional interaction system where the Zeitgeist creates Heroes, and Heroes shape the Zeitgeist.
- **Spawning**:
    - High **Entropy** (>0.8) spawns `REBEL_LEADER` or `EMERGENCY_SAVIOR`.
    - High **Order** (>0.9) spawns `FOUNDING_KING` or `PHILOSOPHER_KING`.
    - Low **Cohesion** (<0.3) spawns `CULTURAL_HERO`.
- **Impact**: Heroes apply immediate modifiers to Materials (e.g., a *Legendary General* boosts *Patriotism* and *Violence*).

### 3. Procedural Generation
When historical heroes are exhausted, the system generates new unique figures:
- **VietnameseNameGenerator**: Creates authentic Hán Việt names (Họ + Đệm + Tên) with meanings.
- **HeroFactory**: Generates stats (Military, Governance, etc.) dynamically based on the current World State.

### 4. World Evolution Kernel
The heartbeat of the simulation.
- **Tick Rate**: Configurable (real-time or turn-based).
- **State Vector**: A multi-dimensional representation of the world's condition.

---

## 🛠 Tech Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Database**: PostgreSQL (JSONB heavy usage for flexible state)
- **Queue**: Redis (for async event processing)
- **Search**: Meilisearch (optional, for wiki)

---

## 🔧 Setup & Commands

### Prerequisites
- Docker & Docker Compose
- PHP 8.2+
- Composer

### Installation
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdvancedMaterialSeeder
```

### Running the Engine
```bash
# Start the simulation loop worker
php artisan queue:work

# Manually tick a world
php artisan world:tick {world_id}
```

---

## 📂 Directory Structure

```
app/
├── Domains/
│   ├── Cosmology/    # State Vectors, Universes
│   ├── Evolution/    # Kernel, Physics Engines
│   ├── Material/     # Material Entities, Mutations
│   └── Vietnamese/   # Heroes, NameGen, Resonance Listeners
├── Http/
│   ├── Controllers/  # API Endpoints
│   └── Resources/    # API Transformers
└── Models/           # Eloquent Models
```
