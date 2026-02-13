<?php

use App\Http\Controllers\StoryController;
use App\Http\Controllers\WorldController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// World Routes
Route::prefix('worlds')->name('worlds.')->group(function () {
    Route::get('/', [WorldController::class, 'index'])->name('index');
    Route::get('/create', [WorldController::class, 'create'])->name('create');
    Route::post('/', [WorldController::class, 'store'])->name('store');
    Route::get('/{worldId}', [WorldController::class, 'show'])->name('show');
    Route::get('/{worldId}/dashboard', [WorldController::class, 'dashboard'])->name('dashboard');
    Route::get('/{worldId}/edit', [WorldController::class, 'edit'])->name('edit');
    Route::put('/{worldId}', [WorldController::class, 'update'])->name('update');
    Route::delete('/{worldId}', [WorldController::class, 'destroy'])->name('destroy');
    
    // World Actions
    Route::post('/{worldId}/tick', [WorldController::class, 'tick'])->name('tick');
    Route::post('/{worldId}/start', [WorldController::class, 'start'])->name('start');
    Route::post('/{worldId}/stop', [WorldController::class, 'stop'])->name('stop');
    Route::get('/{worldId}/status', [WorldController::class, 'status'])->name('status');
    Route::get('/{worldId}/intelligence', [WorldController::class, 'intelligence'])->name('intelligence');
    Route::get('/{worldId}/materials', [WorldController::class, 'materials'])->name('materials');
    Route::get('/{worldId}/realtime', [WorldController::class, 'realtime'])->name('realtime');
});

// API Routes for AJAX calls
Route::prefix('api/worlds')->name('api.worlds.')->group(function () {
    Route::post('/start-all', [WorldController::class, 'startAll'])->name('start-all');
    Route::post('/stop-all', [WorldController::class, 'stopAll'])->name('stop-all');
    Route::get('/{worldId}/realtime', [WorldController::class, 'realtime'])->name('realtime');
});

Route::get('/story', StoryController::class);

// API Routes for Reader Interaction
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\Api\Reader\ReaderInteractionController;

Route::post('/api/world/{id}/react', [ReactionController::class, 'store']);
Route::get('/api/world/{id}/reactions', [ReactionController::class, 'index']);

// Reader Interaction API (Phase 16)
Route::prefix('api/reader')->group(function () {
    Route::get('/worlds/{world}/choices/{epoch}', [ReaderInteractionController::class, 'getChoices']);
    Route::post('/worlds/{world}/vote', [ReaderInteractionController::class, 'vote']);
    Route::post('/worlds/{world}/react', [ReaderInteractionController::class, 'react']);
    Route::get('/worlds/{world}/results/{epoch}', [ReaderInteractionController::class, 'getResults']);
});

// Admin WMCP Routes (ADR-0007)
Route::prefix('admin/wmcp')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\WMCP\DashboardController::class, 'index'])->name('admin.wmcp.dashboard');
    
    // Worlds
    Route::get('/worlds', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'index'])->name('admin.wmcp.worlds.index');
    Route::get('/worlds/create', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'create'])->name('admin.wmcp.worlds.create');
    Route::post('/worlds', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'store'])->name('admin.wmcp.worlds.store');
    Route::get('/worlds/{id}', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'show'])->name('admin.wmcp.worlds.show');
    Route::get('/worlds/{id}/edit', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'edit'])->name('admin.wmcp.worlds.edit');
    Route::put('/worlds/{id}', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'update'])->name('admin.wmcp.worlds.update');
    Route::post('/worlds/{id}/fork', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'fork'])->name('admin.wmcp.worlds.fork');
    Route::post('/worlds/{id}/safe-mode', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'toggleSafeMode'])->name('admin.wmcp.worlds.safe_mode');
    Route::post('/worlds/{id}/halt', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'halt'])->name('admin.wmcp.worlds.halt');
    Route::get('/worlds/{id}/edit-laws', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'editLaws'])->name('admin.wmcp.worlds.edit_laws');
    Route::put('/worlds/{id}/update-laws', [\App\Http\Controllers\Admin\WMCP\WorldController::class, 'updateLaws'])->name('admin.wmcp.worlds.update_laws');
    
    // Factions
    Route::get('/worlds/{world}/factions/create', [\App\Http\Controllers\Admin\WMCP\FactionController::class, 'create'])->name('admin.wmcp.factions.create');
    Route::post('/worlds/{world}/factions', [\App\Http\Controllers\Admin\WMCP\FactionController::class, 'store'])->name('admin.wmcp.factions.store');
    Route::get('/factions/{faction}/edit', [\App\Http\Controllers\Admin\WMCP\FactionController::class, 'edit'])->name('admin.wmcp.factions.edit');
    Route::put('/factions/{faction}', [\App\Http\Controllers\Admin\WMCP\FactionController::class, 'update'])->name('admin.wmcp.factions.update');
    Route::delete('/factions/{faction}', [\App\Http\Controllers\Admin\WMCP\FactionController::class, 'destroy'])->name('admin.wmcp.factions.destroy');
    
    // World Factors (Comprehensive Overview)
    Route::get('/worlds/{world}/factors', [\App\Http\Controllers\Admin\WMCP\DashboardController::class, 'worldFactors'])->name('admin.wmcp.worlds.factors');
    
    // Timelines
    Route::get('/timelines', [\App\Http\Controllers\Admin\WMCP\TimelineController::class, 'index'])->name('admin.wmcp.timelines.index');
    
    // Governance
    Route::get('/governance', [\App\Http\Controllers\Admin\WMCP\GovernanceController::class, 'index'])->name('admin.wmcp.governance.index');
    Route::get('/governance/{id}', [\App\Http\Controllers\Admin\WMCP\GovernanceController::class, 'show'])->name('admin.wmcp.governance.show');
    
    // Material Analytics
    Route::get('/materials/analytics', [\App\Http\Controllers\Admin\WMCP\MaterialAnalyticsController::class, 'index'])->name('admin.materials.analytics');
    Route::get('/materials/analytics/data', [\App\Http\Controllers\Admin\WMCP\MaterialAnalyticsController::class, 'data'])->name('admin.materials.analytics.data');
    
    // Material Management
    Route::get('/materials', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'index'])->name('admin.materials.index');
    Route::get('/materials/create', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'create'])->name('admin.materials.create');
    Route::post('/materials', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'store'])->name('admin.materials.store');
    Route::get('/materials/compatibility', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'editCompatibility'])->name('admin.materials.compatibility');
    Route::post('/materials/compatibility', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'updateCompatibility'])->name('admin.materials.compatibility.update');
    Route::get('/materials/{id}', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'show'])->name('admin.materials.show');
    Route::get('/materials/{id}/edit', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'edit'])->name('admin.materials.edit');
    Route::put('/materials/{id}', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'update'])->name('admin.materials.update');
    Route::delete('/materials/{id}', [\App\Http\Controllers\Admin\WMCP\MaterialController::class, 'destroy'])->name('admin.materials.destroy');
    
    // Material Extraction Pipeline
    Route::get('/material-extraction', [\App\Http\Controllers\Admin\Material\ExtractionController::class, 'index'])->name('admin.material.extraction.index');
    Route::get('/material-extraction/{template}', [\App\Http\Controllers\Admin\Material\ExtractionController::class, 'show'])->name('admin.material.extraction.show');
    Route::post('/material-extraction/{template}/approve', [\App\Http\Controllers\Admin\Material\ExtractionController::class, 'approve'])->name('admin.material.extraction.approve');
    Route::post('/material-extraction/{template}/reject', [\App\Http\Controllers\Admin\Material\ExtractionController::class, 'reject'])->name('admin.material.extraction.reject');
    
    // Simulation
    Route::get('/simulation', [\App\Http\Controllers\Admin\WMCP\SimulationController::class, 'index'])->name('admin.wmcp.simulation.index');
    Route::post('/simulation/{worldId}/run', [\App\Http\Controllers\Admin\WMCP\SimulationController::class, 'run'])->name('admin.wmcp.simulation.run');
    
    
    // Incidents (Post-Mortems)
    Route::get('/incidents', [\App\Http\Controllers\Admin\WMCP\IncidentController::class, 'index'])->name('admin.wmcp.incidents.index');
    Route::get('/worlds/{world}/incidents/create', [\App\Http\Controllers\Admin\WMCP\IncidentController::class, 'create'])->name('admin.wmcp.incidents.create');
    Route::post('/worlds/{world}/incidents', [\App\Http\Controllers\Admin\WMCP\IncidentController::class, 'store'])->name('admin.wmcp.incidents.store');
    Route::get('/incidents/{incident}', [\App\Http\Controllers\Admin\WMCP\IncidentController::class, 'show'])->name('admin.wmcp.incidents.show');
    Route::put('/incidents/{incident}', [\App\Http\Controllers\Admin\WMCP\IncidentController::class, 'update'])->name('admin.wmcp.incidents.update');
    
    // World Events (Observability)
    Route::get('/events', [\App\Http\Controllers\Admin\WMCP\EventController::class, 'index'])->name('admin.wmcp.events.index');
    Route::get('/events/{id}', [\App\Http\Controllers\Admin\WMCP\EventController::class, 'show'])->name('admin.wmcp.events.show');
    Route::get('/events/export/json', [\App\Http\Controllers\Admin\WMCP\EventController::class, 'export'])->name('admin.wmcp.events.export');
    
    // AI Generations (Observability - Article II Compliance)
    Route::get('/ai-generations', [\App\Http\Controllers\Admin\WMCP\AIGenerationController::class, 'index'])->name('admin.wmcp.ai-generations.index');
    Route::get('/ai-generations/{id}', [\App\Http\Controllers\Admin\WMCP\AIGenerationController::class, 'show'])->name('admin.wmcp.ai-generations.show');
    
    // World Alerts (Observability - Article IV Compliance)
    Route::get('/alerts', [\App\Http\Controllers\Admin\WMCP\AlertController::class, 'index'])->name('admin.wmcp.alerts.index');
    Route::get('/alerts/{id}', [\App\Http\Controllers\Admin\WMCP\AlertController::class, 'show'])->name('admin.wmcp.alerts.show');
    Route::post('/alerts/{id}/resolve', [\App\Http\Controllers\Admin\WMCP\AlertController::class, 'resolve'])->name('admin.wmcp.alerts.resolve');
    
    // Seed Library (Content Management - AFR Compliant)
    Route::resource('seeds', \App\Http\Controllers\Admin\WMCP\SeedController::class)->names([
        'index' => 'admin.wmcp.seeds.index',
        'create' => 'admin.wmcp.seeds.create',
        'store' => 'admin.wmcp.seeds.store',
        'show' => 'admin.wmcp.seeds.show',
        'edit' => 'admin.wmcp.seeds.edit',
        'update' => 'admin.wmcp.seeds.update',
        'destroy' => 'admin.wmcp.seeds.destroy',
    ]);
    Route::post('/seeds/{template}/inject/{world}', [\App\Http\Controllers\Admin\WMCP\SeedController::class, 'inject'])->name('admin.wmcp.seeds.inject');
    Route::get('/worlds/{world}/seeds', [\App\Http\Controllers\Admin\WMCP\SeedController::class, 'active'])->name('admin.wmcp.seeds.active');
    Route::post('/worlds/{world}/seeds/{seed}/exhaust', [\App\Http\Controllers\Admin\WMCP\SeedController::class, 'forceExhaust'])->name('admin.wmcp.seeds.force_exhaust');
    
    // World Foundation Repository (Primitive Catalog - Read-Only + Proposal)
    Route::get('/primitives', [\App\Http\Controllers\Admin\WMCP\PrimitiveController::class, 'index'])->name('admin.wmcp.primitives.index');
    Route::get('/primitives/{id}', [\App\Http\Controllers\Admin\WMCP\PrimitiveController::class, 'show'])->name('admin.wmcp.primitives.show');
    Route::get('/primitives-propose', [\App\Http\Controllers\Admin\WMCP\PrimitiveController::class, 'propose'])->name('admin.wmcp.primitives.propose');
    Route::post('/primitives-propose', [\App\Http\Controllers\Admin\WMCP\PrimitiveController::class, 'submitProposal'])->name('admin.wmcp.primitives.submit_proposal');
    
    // Myth & Scar Management (Read-Only - Immutable Consequences)
    Route::get('/myths', [\App\Http\Controllers\Admin\WMCP\MythController::class, 'index'])->name('admin.wmcp.myths.index');
    Route::get('/myths/{id}', [\App\Http\Controllers\Admin\WMCP\MythController::class, 'show'])->name('admin.wmcp.myths.show');
    Route::get('/scars', [\App\Http\Controllers\Admin\WMCP\ScarController::class, 'index'])->name('admin.wmcp.scars.index');
    Route::get('/scars/{id}', [\App\Http\Controllers\Admin\WMCP\ScarController::class, 'show'])->name('admin.wmcp.scars.show');
    
    // World Health History (Advanced Observability)
    Route::get('/health', [\App\Http\Controllers\Admin\WMCP\HealthController::class, 'index'])->name('admin.wmcp.health.index');
    Route::get('/health/{world}', [\App\Http\Controllers\Admin\WMCP\HealthController::class, 'show'])->name('admin.wmcp.health.show');
    
    // Governance Audit Log (Advanced Observability)
    Route::get('/audit', [\App\Http\Controllers\Admin\WMCP\AuditController::class, 'index'])->name('admin.wmcp.audit.index');

    // God Console — Cosmic Observatory & Control (Phase 9-12)
    Route::prefix('god-console/{worldId}')->group(function () {
        // Dashboard view
        Route::get('/', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'index'])->name('admin.wmcp.god-console.index');

        // Observe
        Route::get('/metrics', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'metrics'])->name('admin.wmcp.god-console.metrics');
        Route::get('/trajectory', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'trajectory'])->name('admin.wmcp.god-console.trajectory');
        Route::get('/attractors', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'attractors'])->name('admin.wmcp.god-console.attractors');

        // Monitor
        Route::get('/alerts', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'alerts'])->name('admin.wmcp.god-console.alerts');
        Route::post('/alerts/{alertId}/acknowledge', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'acknowledgeAlert'])->name('admin.wmcp.god-console.alerts.ack');

        // Control
        Route::post('/control/freeze', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'freeze'])->name('admin.wmcp.god-console.freeze');
        Route::post('/control/resume', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'resume'])->name('admin.wmcp.god-console.resume');
        Route::post('/control/step', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'step'])->name('admin.wmcp.god-console.step');
        Route::post('/control/rollback', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'rollback'])->name('admin.wmcp.god-console.rollback');

        // Emergency
        Route::post('/emergency/{action}', [\App\Http\Controllers\Admin\WMCP\GodConsoleController::class, 'emergency'])->name('admin.wmcp.god-console.emergency');
    });

    // Historian Research Platform (Phase 5)
    Route::prefix('historian')->name('admin.historian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\Historian\DashboardController::class, 'index'])->name('dashboard');
        
        // Sagas
        Route::get('/sagas', [\App\Http\Controllers\Admin\Historian\SagaController::class, 'index'])->name('sagas.index');
        Route::get('/sagas/{saga}', [\App\Http\Controllers\Admin\Historian\SagaController::class, 'show'])->name('sagas.show');
        
        // Patterns
        Route::get('/patterns', [\App\Http\Controllers\Admin\Historian\PatternController::class, 'index'])->name('patterns.index');
        
        // Archetypes
        Route::get('/archetypes', [\App\Http\Controllers\Admin\Historian\ArchetypeController::class, 'index'])->name('archetypes.index');
        Route::get('/archetypes/{key}', [\App\Http\Controllers\Admin\Historian\ArchetypeController::class, 'show'])->name('archetypes.show');
    });
});

// Writer Console Routes
use App\Http\Controllers\WriterConsole\WriterConsoleController;
use App\Http\Controllers\WriterConsole\WorldCreationController;
use App\Http\Controllers\WriterConsole\SagaExplorerController;
use App\Http\Controllers\WriterConsole\CanonController;

Route::middleware(['auth', 'verified'])->prefix('writer')->name('writer.')->group(function () {
    // Dashboard
    Route::get('/', [WriterConsoleController::class, 'index'])->name('dashboard');
    Route::get('/terminology', [WriterConsoleController::class, 'terminology'])->name('terminology');

    // World/Saga Creation
    // Genesis (World Creation)
    Route::get('/genesis', [WorldCreationController::class, 'genesis'])->name('genesis');
    Route::post('/genesis', [WorldCreationController::class, 'storeGenesis'])->name('genesis.store');

    Route::post('/sagas', [WorldCreationController::class, 'store'])->name('sagas.store');

    // Saga Exploration
    Route::get('/sagas', [SagaExplorerController::class, 'index'])->name('sagas.index');
    Route::get('/sagas/{saga}', [SagaExplorerController::class, 'show'])->name('sagas.show');
    Route::get('/sagas/{saga}/worlds/{worldSequence}', [SagaExplorerController::class, 'showWorld'])->name('sagas.worlds.show');
    Route::get('/sagas/{saga}/tree', [SagaExplorerController::class, 'tree'])->name('sagas.tree');
    Route::post('/sagas/{saga}/run', [SagaExplorerController::class, 'run'])->name('sagas.run');

    // Canonization
    Route::post('/canon/events', [CanonController::class, 'store'])->name('canon.events.store');

    // World Hub (Unified World Page — 5-tab view)
    Route::get('/worlds/{worldId}', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'show'])->name('worlds.hub');
    Route::post('/worlds/{worldId}/inject', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'injectEvent'])->name('worlds.inject');
    Route::post('/worlds/{worldId}/scar', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'createScar'])->name('worlds.scar');
    Route::post('/worlds/{worldId}/freeze', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'freeze'])->name('worlds.freeze');
    Route::post('/worlds/{worldId}/resume', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'resume'])->name('worlds.resume');
    Route::post('/worlds/{worldId}/step', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'step'])->name('worlds.step');
    Route::post('/worlds/{worldId}/rollback', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'rollback'])->name('worlds.rollback');
    Route::post('/worlds/{worldId}/emergency/{action}', [\App\Http\Controllers\WriterConsole\WorldHubController::class, 'emergency'])->name('worlds.emergency');

    // Material Intervention (Writer Tools)
    Route::get('/worlds/{worldId}/materials', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'index'])->name('materials.state-viewer');
    Route::post('/worlds/{worldId}/materials/activate', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'activate'])->name('materials.activate');
    Route::post('/materials/{instanceId}/adjust-strength', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'adjustStrength'])->name('materials.adjust-strength');
    Route::post('/materials/{instanceId}/retire', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'retire'])->name('materials.retire');
    Route::post('/materials/{instanceId}/force-mutation', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'forceMutation'])->name('materials.force-mutation');
    Route::get('/worlds/{worldId}/materials/timeline', [\App\Http\Controllers\WriterConsole\MaterialInterventionController::class, 'timeline'])->name('materials.timeline');

    // World God Console (Phase 29)
    Route::get('/world/dashboard', [\App\Http\Controllers\Writer\WorldDashboardController::class, 'index'])->name('world.dashboard');
    Route::post('/world/inject', [\App\Http\Controllers\Writer\WorldDashboardController::class, 'injectEvent'])->name('world.inject');
    Route::post('/world/scar', [\App\Http\Controllers\Writer\WorldDashboardController::class, 'createScar'])->name('world.scar');

    // Story Publication (Phase 27)
    Route::post('/world/{world}/publish', [\App\Http\Controllers\Writer\StoryPublicationController::class, 'publish'])->name('story.publish');
    Route::get('/story/{story}', [\App\Http\Controllers\Writer\StoryPublicationController::class, 'show'])->name('story.show');
});

// Authentication Routes (Simplified for Writer Console Prototype)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('writer');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Reader Interaction Routes (Phase 16)
use App\Http\Controllers\Writer\ReaderController;
use App\Http\Controllers\Writer\StoryPublicationController;

Route::middleware(['auth'])->prefix('reader')->name('reader.')->group(function () {
    Route::get('/saga/{world}', [ReaderController::class, 'index'])->name('interact');
    Route::post('/saga/{world}/choice', [ReaderController::class, 'processChoice'])->name('choice');
    Route::post('/saga/{world}/advance', [ReaderController::class, 'advance'])->name('advance');

});

// Official Story Publication Routes (Phase 27) - Moved to writer prefix group (L194-196)
