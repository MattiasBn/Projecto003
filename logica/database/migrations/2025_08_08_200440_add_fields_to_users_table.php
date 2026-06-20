<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->enum('role', ['administrador', 'gerente', 'funcionario'])->default('funcionario');
            $table->string('telefone')->nullable();
            $table->boolean('confirmado')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
             $table->dropColumn('role');
            $table->dropColumn('telefone');
            $table->dropColumn('confirmado');
        });
    }
};
