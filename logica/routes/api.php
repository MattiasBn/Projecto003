<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\PontosController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ConversaController;
use App\Http\Controllers\EncontroController;
use App\Http\Controllers\NotificacaoController;

Route::middleware([\App\Http\Middleware\BlockBrowserAccess::class])->group(function () {

    // ─── PÚBLICAS ────────────────────────────────────────────────────
    Route::post('/auth/check',       [UserController::class,  'checkAndAuthenticate']);
    Route::post('/auth/verify-otp',  [UserController::class,  'verifyPhoneOtp']);
    Route::post('/auth/google',      [UserController::class,  'loginWithGoogle']);
    Route::post('/login',            [UserController::class,  'login']);

    // Feed e produtos públicos (sem token)
    Route::get('/feed',              [FeedController::class, 'index']);
    Route::get('/produtos',          [ProdutoController::class, 'index']);
    Route::get('/produtos/{id}',     [ProdutoController::class, 'show']);

    // ─── PROTEGIDAS ──────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [UserController::class, 'logout']);


        // ─── CONVERSAS ───────────────────────────────────────────────
        Route::get('/conversas',                         [ConversaController::class, 'index']);
        Route::post('/conversas',                        [ConversaController::class, 'iniciarOuAbrir']);
        Route::get('/conversas/{id}/mensagens',          [ConversaController::class, 'mensagens']);
        Route::post('/conversas/{id}/mensagens',         [ConversaController::class, 'enviarMensagem']);


            // ─── ENCONTROS ───────────────────────────────────────────────
            Route::post('/conversas/{id}/encontro',          [EncontroController::class, 'marcar']);
            Route::post('/encontros/{id}/iniciar',           [EncontroController::class, 'iniciar']);
            Route::post('/encontros/{id}/localizacao',       [EncontroController::class, 'actualizarLocalizacao']);
            Route::get('/encontros/{id}/estado',             [EncontroController::class, 'estado']);
            Route::post('/encontros/{id}/concluir',          [EncontroController::class, 'concluir']);
            Route::post('/encontros/{id}/cancelar',          [EncontroController::class, 'cancelar']);
            Route::post('/encontros/{id}/avaliar',           [EncontroController::class, 'avaliar']);

        Route::get('/notificacoes',                      [NotificacaoController::class, 'index']);
        Route::put('/notificacoes/{id}/lida',            [NotificacaoController::class, 'marcarLida']);
        Route::put('/notificacoes/todas-lidas',          [NotificacaoController::class, 'marcarTodasLidas']);
        Route::post('/notificacoes/fcm-token',           [NotificacaoController::class, 'guardarFcmToken']);


        // Produtos
        Route::post('/produtos',              [ProdutoController::class, 'store']);
        Route::put('/produtos/{id}',          [ProdutoController::class, 'update']);
        Route::delete('/produtos/{id}',       [ProdutoController::class, 'destroy']);
        Route::post('/produtos/{id}/like',    [ProdutoController::class, 'like']);

        // Pontos
        Route::get('/pontos', [PontosController::class, 'meuSaldo']);

        // Alertas
        Route::get('/alertas',         [AlertaController::class, 'index']);
        Route::post('/alertas',        [AlertaController::class, 'store']);
        Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);

        // Utilizadores
        Route::get('/users',         [UserController::class, 'index']);
        Route::get('/users/{id}',    [UserController::class, 'show']);
        Route::put('/users/{id}',    [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});