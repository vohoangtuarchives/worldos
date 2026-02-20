<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Auth — Sanctum Token (Bearer)
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user',         [AuthController::class, 'user']);
    Route::post('logout',      [AuthController::class, 'logout']);
    Route::post('logout-all',  [AuthController::class, 'logoutAll']);
});

/*
|--------------------------------------------------------------------------
| WORLD OS v4 
|--------------------------------------------------------------------------
| DDD Endpoints
*/
use App\Http\Controllers\Api\V4\Writer\WriterGenesisController;
use App\Http\Controllers\Api\V4\Writer\WriterGodConsoleController;

Route::prefix('v4/writer')->group(function () {
    // 1. Tạo mới Vũ trụ (WorldSeed)
    Route::post('/genesis/universe', [WriterGenesisController::class, 'createUniverse']);
    
    // 2. God Console: Lấy Data và Thao tác Vận mệnh
    Route::prefix('worlds/{worldId}/god-console')->group(function () {
        Route::get('/metrics', [WriterGodConsoleController::class, 'getMetrics']);
        Route::post('/intervene', [WriterGodConsoleController::class, 'applyTension']);
    });
});
