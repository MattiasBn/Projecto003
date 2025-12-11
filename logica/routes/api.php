<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProdutoController;

Route::apiResource('users', UserController::class);
Route::apiResource('produtos', ProdutoController::class);
