<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Modules\Universe\Http\Controllers\UniverseController;

Route::post('/universes', [UniverseController::class, 'store']);
Route::post('/universes/{id}/tick', [UniverseController::class, 'tick']);

