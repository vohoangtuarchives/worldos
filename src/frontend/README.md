# WorldOS V3: The God Console 🖥️

The Frontend for WorldOS V3 is a sophisticated **God Console** designed for Writers and World Builders to observe, intervene, and analyze their simulations in real-time.

## 🏗 Architecture

Built on **Next.js 14 (App Router)** with a Feature-based architecture.

### Tech Stack
- **Framework**: Next.js 14 (React 18)
- **State Management**: React Query (TanStack Query)
- **Styling**: TailwindCSS + Shadcn/UI (Radix Primitives)
- **Icons**: Lucide React
- **Visuals**: Glassmorphism UI Kit

---

## 🌟 Key Views & Features

### 1. World Hub (`/world/[id]`)
The central command center for a specific world.
- **Live Metrics**: Real-time display of Entropy, Stability, and Population.
- **Intervention Console**: Buttons to Freeze, Resume, Step, or Rollback the simulation.
- **Emergency Actions**: Inject "Entropy Shock" or "Force Collapse" to test resilience.
- **Active Heroes**: A scrollable roster of currently active Heroes (Historical & Procedural) with their impact scores.

### 2. Material Wiki (`/wiki`)
An interactive encyclopedia of all Materials in the system.
- **Catalog**: Filterable list of all concepts (Physics, Civics, Philosophy).
- **Graph View**: Visualizes mutation pathways and prerequisites.

### 3. Saga Tree (`/saga/[id]`)
Visualizes the branching timelines of the multiverse.
- **Tree Visualization**: Shows forks, dead ends, and active timeline branches.
- **Time Travel**: Allows jumping back to previous epochs to fork new realities.

---

## 📂 Project Structure

```
src/
├── app/                  # Next.js App Router Pages
├── features/             # Feature-based Modules
│   ├── writer/           # Main Writer Interface
│   │   ├── components/   # Feature-specific components
│   │   ├── WorldHubView.tsx
│   │   ├── MaterialWikiView.tsx
│   │   └── useWriterApi.ts
│   └── reader/           # Reader-facing Interface
├── shared/               # Shared Utilities
│   ├── api/              # API Clients (Axios)
│   └── components/       # Common UI Components
└── lib/                  # Utils & Helpers
```

---

## 🚀 Getting Started

### Installation
```bash
npm install
# or
pnpm install
```

### Development
```bash
npm run dev
```

The app will be available at `http://localhost:3000`.

### Environment Variables
Create a `.env.local` file:
```
NEXT_PUBLIC_API_URL=http://localhost:8000
```
