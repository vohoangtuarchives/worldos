# DDD + Clean Architecture Refactor — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Refactor backend into Tuzy namespace with tactical DDD (Domain entities separate from Eloquent, repositories as ports, use cases in Application, Presentation in Tuzy) and Clean Architecture layers, applied across all bounded contexts.

**Architecture:** See `docs/plans/2026-02-20-ddd-refactor-design.md`. Root namespace `Tuzy` under `src/Tuzy/` with Domain, Application, Infrastructure, Presentation. Domain has no Laravel dependency; Infrastructure implements persistence (Entity ↔ Eloquent); Presentation holds HTTP controllers.

**Tech Stack:** PHP 8.x, Laravel, PSR-4 autoload. Backend path: `src/backend/`.

---

## Phase 0 — Tuzy scaffold

### Task 0.1: Composer autoload for Tuzy

**Files:**
- Modify: `src/backend/composer.json` (autoload.psr-4)

**Step 1: Add Tuzy namespace**

In `composer.json`, under `autoload.psr-4`, add (or merge with existing):

```json
"Tuzy\\": "src/Tuzy/"
```

Ensure `src/` is relative to backend root (e.g. `"Tuzy\\": "src/Tuzy/"` if composer.json is in `src/backend/`).

**Step 2: Regenerate autoload**

Run: `cd src/backend && composer dump-autoload`

Expected: No errors.

**Step 3: Commit**

```bash
git add src/backend/composer.json
git commit -m "chore: add Tuzy PSR-4 autoload"
```

---

### Task 0.2: Create Tuzy folder structure (no logic)

**Files:**
- Create: `src/backend/src/Tuzy/Domain/.gitkeep`
- Create: `src/backend/src/Tuzy/Application/.gitkeep`
- Create: `src/backend/src/Tuzy/Infrastructure/.gitkeep`
- Create: `src/backend/src/Tuzy/Presentation/.gitkeep`

Create only top-level folders; no PHP files yet. Use `.gitkeep` so empty dirs are tracked.

**Step 1: Create directories**

Create `src/backend/src/Tuzy/Domain/`, `Application/`, `Infrastructure/`, `Presentation/`.

**Step 2: Commit**

```bash
git add src/backend/src/Tuzy/
git commit -m "chore: add Tuzy layer folders"
```

---

## Phase 1 — World context (pilot)

### Task 1.1: World domain entity (plain PHP)

**Files:**
- Create: `src/backend/src/Tuzy/Domain/World/Entity/World.php`
- Create: `src/backend/tests/Unit/Tuzy/Domain/World/Entity/WorldTest.php`

**Step 1: Write failing test**

In `WorldTest.php`: test that `World::create(string $name)` returns an object with `getId()` and `getName()`, and has an identity (id can be passed or generated).

**Step 2: Run test — expect fail**

Run: `cd src/backend && php artisan test tests/Unit/Tuzy/Domain/World/Entity/WorldTest.php --no-config` or `./vendor/bin/phpunit tests/Unit/Tuzy/Domain/World/Entity/WorldTest.php`

Expected: FAIL (class/file not found or method missing).

**Step 3: Implement minimal Entity**

Create `World.php` in `Tuzy\Domain\World\Entity`. Class `World` with private `id`, `name`; static `create(string $name, ?string $id = null)`; getters `getId()`, `getName()`. No Eloquent; use plain PHP.

**Step 4: Run test — expect pass**

Run same test command. Expected: PASS.

**Step 5: Commit**

```bash
git add src/backend/src/Tuzy/Domain/World/Entity/World.php src/backend/tests/Unit/Tuzy/Domain/World/Entity/WorldTest.php
git commit -m "feat(tuzy): add World domain entity"
```

---

### Task 1.2: World repository interface (Domain port)

**Files:**
- Create: `src/backend/src/Tuzy/Domain/World/Repository/WorldRepositoryInterface.php`
- Create: `src/backend/tests/Unit/Tuzy/Domain/World/Repository/WorldRepositoryInterfaceTest.php` (optional: test via fake impl)

**Step 1: Define interface**

`WorldRepositoryInterface` in namespace `Tuzy\Domain\World\Repository` with methods:
- `findById(string $id): ?World` (World = `Tuzy\Domain\World\Entity\World`)
- `save(World $world): void`

**Step 2: Commit**

```bash
git add src/backend/src/Tuzy/Domain/World/Repository/WorldRepositoryInterface.php
git commit -m "feat(tuzy): add World repository interface"
```

---

### Task 1.3: World value object (optional, one VO)

**Files:**
- Create: `src/backend/src/Tuzy/Domain/World/ValueObject/WorldLawProfile.php` (or reuse name from existing codebase)
- Test: in `WorldTest.php` or new `WorldLawProfileTest.php`

Add one value object used by World aggregate; immutable, value equality. Implement minimal (e.g. from existing `App\Domains\World\ValueObjects\WorldLawProfile` if exists, or stub).

**Step 1: Write failing test for VO**

Test creation and equality (two VOs with same data are equal).

**Step 2: Implement VO**

Plain PHP, immutable.

**Step 3: Run tests, commit**

```bash
git add src/backend/src/Tuzy/Domain/World/ValueObject/WorldLawProfile.php tests/...
git commit -m "feat(tuzy): add WorldLawProfile value object"
```

---

### Task 1.4: Eloquent World repository (Infrastructure)

**Files:**
- Create: `src/backend/src/Tuzy/Infrastructure/Persistence/World/EloquentWorldRepository.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php` (or new `TuzyServiceProvider`) to bind `WorldRepositoryInterface` → `EloquentWorldRepository`
- Test: `src/backend/tests/Integration/Tuzy/Infrastructure/Persistence/World/EloquentWorldRepositoryTest.php`

**Step 1: Write integration test**

Use Laravel test case, DB (e.g. SQLite). Create World entity, save via repository, findById, assert same data. Use existing `App\Models\World` for persistence if it exists; repository maps Entity ↔ Model.

**Step 2: Run test — expect fail**

**Step 3: Implement EloquentWorldRepository**

Implement `WorldRepositoryInterface`. In `findById`: load `App\Models\World`, map to `Tuzy\Domain\World\Entity\World`. In `save`: map entity to model, save. Create new model if id not exists.

**Step 4: Run test — expect pass**

**Step 5: Register binding in provider**

In `AppServiceProvider` or new provider: `$this->app->bind(WorldRepositoryInterface::class, EloquentWorldRepository::class);`

**Step 6: Commit**

```bash
git add src/backend/src/Tuzy/Infrastructure/Persistence/World/EloquentWorldRepository.php src/backend/app/Providers/... tests/Integration/Tuzy/Infrastructure/Persistence/World/EloquentWorldRepositoryTest.php
git commit -m "feat(tuzy): add Eloquent World repository and binding"
```

---

### Task 1.5: CreateWorld use case (Application)

**Files:**
- Create: `src/backend/src/Tuzy/Application/World/CreateWorld/CreateWorldCommand.php`
- Create: `src/backend/src/Tuzy/Application/World/CreateWorld/CreateWorldHandler.php`
- Create: `src/backend/src/Tuzy/Application/World/CreateWorld/CreateWorldResult.php` (DTO)
- Test: `src/backend/tests/Unit/Tuzy/Application/World/CreateWorldHandlerTest.php`

**Step 1: Write failing test**

Handler receives CreateWorldCommand(name), calls repository->save with new World entity, returns CreateWorldResult with id. Use fake repository (in-memory impl of interface).

**Step 2: Run test — fail**

**Step 3: Implement Command, Result, Handler**

Handler constructor: `WorldRepositoryInterface $worldRepository`. handle(CreateWorldCommand $command): create `World::create($command->name)`, `$this->worldRepository->save($world)`, return CreateWorldResult with $world->getId().

**Step 4: Run test — pass**

**Step 5: Commit**

```bash
git add src/backend/src/Tuzy/Application/World/CreateWorld/*.php tests/Unit/Tuzy/Application/World/CreateWorldHandlerTest.php
git commit -m "feat(tuzy): add CreateWorld use case"
```

---

### Task 1.6: Presentation — CreateWorld controller and route

**Files:**
- Create: `src/backend/src/Tuzy/Presentation/Http/Controllers/World/CreateWorldController.php`
- Modify: `src/backend/routes/api.php` (or web) — register route pointing to CreateWorldController

**Step 1: Write controller**

Controller receives Request, builds CreateWorldCommand from request input, calls CreateWorldHandler::handle, returns JSON with result (id, name). Inject CreateWorldHandler.

**Step 2: Register route**

Route POST to create world, action CreateWorldController. Ensure Laravel can resolve controller from `Tuzy\Presentation\Http\Controllers\World\CreateWorldController` (namespace in route or RouteServiceProvider).

**Step 3: Manual or feature test**

Feature test: POST with name, assert 200 and JSON contains id.

**Step 4: Commit**

```bash
git add src/backend/src/Tuzy/Presentation/Http/Controllers/World/CreateWorldController.php src/backend/routes/api.php
git commit -m "feat(tuzy): add CreateWorld HTTP endpoint"
```

---

## Phase 2 — Replicate pattern for remaining contexts

Apply the same sequence per bounded context (Runtime, Saga, Cosmology, Evolution, Narrative, Mutation, Vietnamese):

1. Domain: Entity (and key VOs), Repository interface, Domain events if needed.
2. Infrastructure: Eloquent repository (or other adapter), bind in provider.
3. Application: One or more use cases (Command, Handler, Result).
4. Presentation: Controllers and routes.
5. Tests: Unit for Domain and Application; Integration for Infrastructure; Feature for critical flows.

Order suggested by dependency (see CONTEXT_MAP): World → Runtime → Saga; Cosmology/Evolution can follow World/Runtime. Narrative, Mutation, Vietnamese after.

**Tasks 2.1–2.N:** For each context, create tasks 2.x.1 (Entity), 2.x.2 (Repository interface), 2.x.3 (Repository impl + binding), 2.x.4 (One use case), 2.x.5 (Controller + route), with same step pattern (test first, implement, commit). Reference this plan and design doc for naming and paths (e.g. `Tuzy\Domain\Runtime\Entity\Universe`, `Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface`).

---

## Phase 3 — Migrate existing app code to Tuzy

- Replace direct use of `App\Domains\*` and `App\Models\*` in entrypoints (controllers, console commands) with Tuzy Application handlers and Tuzy repositories. Deprecate or remove old controllers/services step by step.
- Move remaining domain logic from `app/Domains/*` into `Tuzy\Domain\*` (entities, VOs, domain services) and call from Tuzy Application.
- Keep `app/Models` as persistence layer only; all domain logic in Tuzy Domain.

Break into tasks per module (e.g. Task 3.1: Migrate World API to Tuzy; Task 3.2: Migrate Saga runner to Tuzy; …).

---

## Phase 4 — Domain events and error handling

- Add domain events to aggregates (e.g. WorldDefined); Infrastructure adapter to dispatch to Laravel event bus after save.
- Introduce domain exceptions (WorldNotFoundException, etc.); map in Presentation (exception handler) to HTTP status.
- Add tests for error paths and event dispatch.

---

## Implementation status (done)

- **Phase 0–2:** Tuzy scaffold, World pilot, all contexts (Runtime, Saga, Cosmology, Evolution, Narrative, Vietnamese) with Entity, Repository, Create* use case, Controller, route, unit tests.
- **Phase 3:** AdminWorldController::store uses CreateWorldHandler; WriterWorldController, WriterUniverseController, WriterCosmologyController, WriterMaterialController use domain exceptions (WorldNotFoundException, UniverseNotFoundException, SagaNotFoundException) instead of findOrFail / manual 404.
- **Phase 4:** *Created events and *NotFoundException per context; Eloquent repositories dispatch events on create; `bootstrap/app.php` maps all Tuzy exceptions to 404 JSON.
- **Bootstrap:** Added `"App\\": "app/"` to `composer.json` to fix ChapterGeneratedListener redeclare; `php artisan test` and Tuzy + CreateWorldEndpointTest run successfully.

---

## Execution options

**Plan saved to `docs/plans/2026-02-20-ddd-refactor-implementation.md`.**

**1. Subagent-driven (this session)** — One subagent per task or per phase, review between tasks.

**2. Parallel session (separate)** — Open new session with executing-plans skill, run with checkpoints.
