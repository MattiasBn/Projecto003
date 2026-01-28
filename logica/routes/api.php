<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProdutoController;

Route::apiResource('users', UserController::class);
Route::apiResource('produtos', ProdutoController::class);


//  rotas de Logs
Route::get('/users/{user}/logs', function ($user) {
    return ActivityLog::where('entity_type', 'user')
        ->where('entity_id', $user)
        ->latest()
        ->get();
});

Route::get('/produtos/{produto}/logs', function ($produto) {
    return ActivityLog::where('entity_type', 'produto')
        ->where('entity_id', $produto)
        ->latest()
        ->get();
});

Route::get('/logs/{log}', fn ($log) =>
    ActivityLog::findOrFail($log)
);
