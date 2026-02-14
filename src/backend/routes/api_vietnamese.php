<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VietnameseHeroController;

// Vietnamese Heroes API
Route::prefix('vietnamese-heroes')->group(function () {
    Route::get('/', [VietnameseHeroController::class, 'index']);
    Route::get('/search', [VietnameseHeroController::class, 'search']);
    Route::get('/statistics', [VietnameseHeroController::class, 'statistics']);
    Route::get('/dimension-distribution', [VietnameseHeroController::class, 'dimensionDistribution']);
    Route::get('/top/{dimension}', [VietnameseHeroController::class, 'topByDimension']);
    Route::get('/era/{era}/profile', [VietnameseHeroController::class, 'eraProfile']);
    Route::get('/{id}', [VietnameseHeroController::class, 'show']);
    Route::get('/{id}/events', [VietnameseHeroController::class, 'events']);
});
