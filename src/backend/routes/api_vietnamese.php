<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VietnameseHeroController;
use App\Http\Controllers\Api\Writer\WriterSagaController;
use App\Http\Controllers\Api\Writer\WriterUniverseController;
use App\Http\Controllers\Api\Writer\WriterWorldController;
use App\Http\Controllers\Api\Writer\WriterWorldHubController;
use App\Http\Controllers\Api\Writer\WriterGenesisController;
use App\Http\Controllers\Api\Writer\WriterGodConsoleController;
use App\Http\Controllers\Api\Writer\WriterWorldSnapshotController;
use App\Http\Controllers\Api\Writer\WriterAIAgentController;
use App\Http\Controllers\Api\Writer\WriterMaterialController;
use App\Http\Controllers\Api\SerialController;
use App\Http\Controllers\Api\StoryBibleController;
use App\Http\Controllers\Api\ClusterController;

/*
|--------------------------------------------------------------------------
| Auth — Sanctum Token (Bearer)
|--------------------------------------------------------------------------
*/
// Tuzy DDD — Create World (pilot)
Route::post('v4/tuzy/worlds', \Tuzy\Presentation\Http\Controllers\World\CreateWorldController::class);

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user',         [AuthController::class, 'user']);
    Route::post('logout',      [AuthController::class, 'logout']);
    Route::post('logout-all',  [AuthController::class, 'logoutAll']);
});

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('stats', [\App\Http\Controllers\Api\AdminController::class, 'stats']);
    Route::get('evolution/overview', [\App\Http\Controllers\Api\AdminController::class, 'evolutionOverview']);
    Route::post('evolution/ai-toggle', [\App\Http\Controllers\Api\AdminController::class, 'toggleAI']);
    Route::get('universes', [\App\Http\Controllers\Api\AdminController::class, 'universes']);
    Route::post('universe/{id}/lock', [\App\Http\Controllers\Api\AdminController::class, 'toggleLock']);
});

/*
|--------------------------------------------------------------------------
| Writer API — protected by Sanctum
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('writer')->group(function () {

    // Sagas
    Route::get('sagas',                    [WriterSagaController::class, 'index']);
    Route::get('sagas/stats',              [WriterSagaController::class, 'getStats']);
    Route::get('sagas/{sagaId}',          [WriterSagaController::class, 'show']);
    Route::post('sagas/create-from-active',[WriterSagaController::class, 'createFromActive']);
    Route::get('saga/{sagaId}/tree',       [WriterSagaController::class, 'tree']);
    Route::post('saga/{sagaId}/advance',   [WriterSagaController::class, 'advance']);
    Route::post('saga/{sagaId}/run',       [WriterSagaController::class, 'run']);

    // Universes (v3 snapshot-first, metrics)
    Route::get('universes',                        [WriterUniverseController::class, 'index']);
    Route::get('universes/{universeId}/snapshots', [WriterUniverseController::class, 'snapshots']);
    Route::get('universes/{universeId}/metrics',   [WriterUniverseController::class, 'metrics']);
    Route::get('universes/{universeId}/style',     [WriterUniverseController::class, 'style']);

    // Worlds
    Route::get('worlds',                   [WriterWorldController::class, 'index']);
    Route::get('worlds/{id}',              [WriterWorldController::class, 'show']);
    Route::post('worlds/{id}/instances',   [WriterWorldController::class, 'storeInstance']);
    Route::get('worlds/{id}/heroes',       [\App\Http\Controllers\Api\Writer\WriterWorldHeroController::class, 'index']);

    // World Hub actions
    Route::post('worlds/{id}/freeze',      [WriterWorldHubController::class, 'freeze']);
    Route::post('worlds/{id}/resume',      [WriterWorldHubController::class, 'resume']);
    Route::post('worlds/{id}/step',        [WriterWorldHubController::class, 'step']);
    Route::post('worlds/{id}/rollback',    [WriterWorldHubController::class, 'rollback']);
    Route::post('worlds/{id}/inject',      [WriterWorldHubController::class, 'inject']);
    Route::post('worlds/{id}/scar',        [WriterWorldHubController::class, 'scar']);
    Route::post('worlds/{id}/emergency/{action}', [WriterWorldHubController::class, 'emergency']);

    // God Console
    Route::get('worlds/{id}/god-console/metrics',   [WriterGodConsoleController::class, 'getMetrics']);
    Route::post('worlds/{id}/god-console/intervene', [WriterGodConsoleController::class, 'intervene']);

    // Snapshots & Events
    Route::get('worlds/{id}/snapshots',              [WriterWorldSnapshotController::class, 'index']);
    Route::get('worlds/{id}/snapshots/compare',      [WriterWorldSnapshotController::class, 'compare']);
    Route::post('worlds/{id}/snapshots',             [WriterWorldSnapshotController::class, 'store']);
    Route::get('worlds/{id}/events',                 [WriterWorldSnapshotController::class, 'events']);
    Route::post('worlds/{id}/events/replay',         [WriterWorldSnapshotController::class, 'replay']);

    // Material Wiki — catalog + per-world instances/analytics
    Route::get('materials/catalog',                  [WriterMaterialController::class, 'catalog']);
    Route::get('materials/{code}/detail',            [WriterMaterialController::class, 'detail']);
    Route::get('worlds/{id}/materials',              [WriterMaterialController::class, 'index']);
    Route::get('worlds/{id}/materials/timeline',     [WriterMaterialController::class, 'timeline']);
    Route::get('worlds/{id}/materials/analytics',    [WriterMaterialController::class, 'worldAnalytics']);
    Route::post('worlds/{id}/materials/activate',    [WriterMaterialController::class, 'activate']);
    Route::patch('materials/{instanceId}/strength',  [WriterMaterialController::class, 'adjustStrength']);
    Route::post('materials/{instanceId}/retire',     [WriterMaterialController::class, 'retire']);

    // Genesis
    Route::get('genesis/presets',          [WriterGenesisController::class, 'presets']);
    Route::post('genesis/world',           [WriterGenesisController::class, 'storeWorld']);
    Route::post('genesis/universe',        [WriterGenesisController::class, 'storeUniverse']);

    // AI Mission Control
    Route::prefix('ai')->group(function () {
        Route::get('metrics',                  [WriterAIAgentController::class, 'getMetrics']);
        Route::get('generations',              [WriterAIAgentController::class, 'getGenerations']);
        Route::get('agents',                   [WriterAIAgentController::class, 'getAgents']);
        Route::get('feature-configs',          [WriterAIAgentController::class, 'getFeatureConfigs']);
        Route::post('feature-configs',         [WriterAIAgentController::class, 'upsertFeatureConfig']);
        Route::delete('feature-configs/{featureKey}', [WriterAIAgentController::class, 'deleteFeatureConfig']);
        Route::get('request-logs/filters',     [WriterAIAgentController::class, 'getRequestLogFilters']);
        Route::get('request-logs',             [WriterAIAgentController::class, 'getRequestLogs']);
        Route::get('request-logs/{id}',        [WriterAIAgentController::class, 'getRequestLogDetail']);
        Route::post('intervene',               [WriterAIAgentController::class, 'intervene']);
    });

    // Governance / Proposals
    Route::get('governance/proposals/{worldId}',  [\App\Http\Controllers\Api\Writer\WriterGovernanceController::class, 'proposals']);
    Route::post('governance/proposals/{id}/approve', [\App\Http\Controllers\Api\Writer\WriterGovernanceController::class, 'approve']);
    Route::post('governance/proposals/{id}/reject',  [\App\Http\Controllers\Api\Writer\WriterGovernanceController::class, 'reject']);
});

/*
|--------------------------------------------------------------------------
| Serial API (truyện dài kỳ) — protected by Sanctum
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Cluster API (control plane — snapshot for dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('cluster')->group(function () {
    Route::get('snapshot', [ClusterController::class, 'snapshot']);
    Route::get('governor', [ClusterController::class, 'governor']);
    Route::get('system', [ClusterController::class, 'system']);
    Route::post('emergency-freeze', [ClusterController::class, 'emergencyFreeze']);
});

/*
|--------------------------------------------------------------------------
| Serial API (truyện dài kỳ) — protected by Sanctum
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('serial')->group(function () {
    Route::get('series',                    [SerialController::class, 'index']);
    Route::get('genres',                    [SerialController::class, 'genres']);
    Route::get('universes',                 [SerialController::class, 'universes']);
    Route::post('series',                   [SerialController::class, 'store']);
    Route::get('series/{id}',               [SerialController::class, 'show']);
    Route::patch('series/{id}',             [SerialController::class, 'update']);
    Route::delete('series/{id}',            [SerialController::class, 'destroy']);
    Route::post('series/{id}/generate-next-chapter', [SerialController::class, 'generateNextChapter']);
    Route::post('series/{id}/generate-chapters',    [SerialController::class, 'generateChapters']);
    Route::get('series/{id}/arcs',          [SerialController::class, 'arcs']);
    Route::put('series/{id}/arcs/{index}/approve', [SerialController::class, 'approveArc']);
    Route::put('series/{id}/arcs/{index}/reject', [SerialController::class, 'rejectArc']);
    Route::post('series/{id}/outline/generate',    [SerialController::class, 'generateOutline']);
    Route::get('series/{id}/story-bible',   [StoryBibleController::class, 'show']);
    Route::put('series/{id}/story-bible',  [StoryBibleController::class, 'update']);
    Route::post('series/{id}/story-bible/generate-from-premise', [StoryBibleController::class, 'generateFromPremise']);
    Route::get('series/{id}/story-bible/characters', [StoryBibleController::class, 'indexCharacters']);
    Route::post('series/{id}/story-bible/characters', [StoryBibleController::class, 'storeCharacter']);
    Route::post('series/{id}/chapters/{chapterId}/canonize', [SerialController::class, 'canonizeChapter']);
});

/*
|--------------------------------------------------------------------------
| Vietnamese Heroes (public)
|--------------------------------------------------------------------------
*/
Route::prefix('vietnamese-heroes')->group(function () {
    Route::get('/',                      [VietnameseHeroController::class, 'index']);
    Route::get('/search',                [VietnameseHeroController::class, 'search']);
    Route::get('/statistics',            [VietnameseHeroController::class, 'statistics']);
    Route::get('/dimension-distribution',[VietnameseHeroController::class, 'dimensionDistribution']);
    Route::get('/top/{dimension}',       [VietnameseHeroController::class, 'topByDimension']);
    Route::get('/era/{era}/profile',     [VietnameseHeroController::class, 'eraProfile']);
    Route::get('/{id}',                  [VietnameseHeroController::class, 'show']);
    Route::get('/{id}/events',           [VietnameseHeroController::class, 'events']);
});
