<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Universe\Http\Controllers\UniverseController;
use App\Http\Controllers\Api\SimulationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Universe Routes (legacy — dần dần migrate sang /simulation)
Route::post('/universes', [UniverseController::class, 'store']);
Route::post('/universes/{id}/tick', [UniverseController::class, 'tick']);

// Simulation Domain Routes (CQRS — v1.0.1)
Route::prefix('simulation')->group(function () {
    Route::post('/experiments', [SimulationController::class, 'runExperiment']); // Chạy N ticks
    Route::get('/universes/{universeId}/snapshot', [SimulationController::class, 'getSnapshot']); // Đọc Observable State
});
