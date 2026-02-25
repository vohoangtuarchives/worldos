use App\Modules\Universe\Http\Controllers\WriterGenesisController;
use App\Modules\Narrative\Http\Controllers\WriterNarrativeController;
use App\Modules\Narrative\Http\Controllers\WriterSagaController;
use App\Modules\Universe\Http\Controllers\WriterUniverseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorldOS Writer API Routes
|--------------------------------------------------------------------------
|
| From docs §13.1: Writer API endpoints for World/Universe/Saga management.
|
*/

Route::prefix('writer')->group(function () {
    // Genesis — World & Universe creation
    Route::prefix('genesis')->group(function () {
        Route::post('/world', [WriterGenesisController::class, 'createWorld']);
        Route::post('/universe', [WriterGenesisController::class, 'createUniverse']);
    });

    // Saga — Experiment orchestration
    Route::post('/saga/advance', [WriterSagaController::class, 'advance']);
    Route::post('/sagas/create-from-active', [WriterSagaController::class, 'createFromActive']);

    // Universe — Observation
    Route::get('/universe/{id}/snapshot', [WriterUniverseController::class, 'snapshot']);

    // Narrative — IP Factory
    Route::prefix('narrative')->group(function () {
        Route::post('/series', [WriterNarrativeController::class, 'createSeries']);
        Route::get('/series/{id}', [WriterNarrativeController::class, 'getSeries']);
        Route::post('/chapter/generate', [WriterNarrativeController::class, 'generateChapter']);
        Route::post('/chapter/{id}/canonize', [WriterNarrativeController::class, 'canonizeChapter']);
    });
});

