# WorldOS Distributed Consistency Strategy

## Goals
- Strong consistency for critical power-stage transitions and world state updates.
- Fault isolation between services while preventing double-commit scenarios.
- Support replay/audit via event stream.

## Transaction Patterns

### 1. World Power Transition (Strong Consistency)
- Participants: World Simulation Service (coordinator), Power/Stage Service.
- Use **two-phase commit** over gRPC:
  1. `PrepareTransition` RPC ensures Power service checks threshold and locks stage row.
  2. Upon success, Simulation writes ledger entry and calls `CommitTransition`.
  3. On failure, `AbortTransition` releases lock.
- Database: Postgres with advisory locks per `world_id`.

### 2. Material Ingestion (Eventually Consistent but Confirmed)
- Primary write in Material Service DB.
- Publish `MaterialBatchIngested` event to Kafka topic.
- Consumers (World Simulation, Story Service) acknowledge via consumer offsets; replays supported.

### 3. Saga Chronology Updates
- Saga Service orchestrates multi-world storyline using **Saga pattern**:
  - Each step (create world, seed story, inject materials) exposes compensating action.
  - Saga orchestrator persists steps in dedicated `saga_runs` table and executes sequentially.
  - On failure, compensating actions triggered in reverse order.

## Infrastructure Components
- **Kafka/NATS** for event stream; at-least-once delivery.
- **Postgres** per service schema; cross-service interactions via gRPC only.
- **Redis** as distributed lock fallback (if Postgres lock insufficient).

## Next Steps
- Implement `Prepare/Commit/Abort` endpoints in Power service proto.
- Define compensating actions for Saga steps (e.g., delete world, remove seeds).
- Add observability: trace IDs per transaction, use OpenTelemetry.