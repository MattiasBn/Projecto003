<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registe quaisquer serviços de aplicação.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicialize quaisquer serviços de aplicação.
     */
    public function boot(): void
    {

            User::observe(UserObserver::class);
           Produto::observe(ProdutoObserver::class);

        // Para testes: intercepta a geração do link e devolve o token puro no JSON
    ResetPassword::createUrlUsing(function ($user, string $token) {
        return url("/reset-password-form?token={$token}&email={$user->email}");
    });
    }
}
