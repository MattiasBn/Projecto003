<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProdutoController;
use App\Models\ActivityLog;


Route::apiResource('users', UserController::class);
Route::apiResource('produtos', ProdutoController::class);


//  rotas de Logs
Route::get('/users/{user}/logs', [ActivityLogController::class, 'logsUser']);
Route::get('/produtos/{produto}/logs', [ActivityLogController::class, 'logsProduto']);
Route::get('/logs/{log}', [ActivityLogController::class, 'show']);
 