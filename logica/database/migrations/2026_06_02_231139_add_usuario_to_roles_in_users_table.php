<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Atualiza o ENUM para incluir 'usuario' mantendo o padrão antigo
            $table->enum('role', ['administrador', 'gerente', 'funcionario', 'usuario'])
                  ->default('usuario')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverte para o estado anterior se necessário
            $table->enum('role', ['administrador', 'gerente', 'funcionario'])
                  ->default('funcionario')
                  ->change();
        });
    }
};
