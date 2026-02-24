<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
/*
|--------------------------------------------------------------------------
| Dummy Auth — Bypass Sanctum Token (Bearer)
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user',         [AuthController::class, 'user']);
    Route::post('logout',      [AuthController::class, 'logout']);
    Route::post('logout-all',  [AuthController::class, 'logoutAll']);
});
// Real-time Simulation Stream (Moved out of auth:sanctum for easy EventSource access)
Route::get('realtime/stream/{worldId}', [\App\Http\Controllers\Api\RealtimeSimulationController::class, 'stream']);

require __DIR__.'/api_vietnamese.php';
