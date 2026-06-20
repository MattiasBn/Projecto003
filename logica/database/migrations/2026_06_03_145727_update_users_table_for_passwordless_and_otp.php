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
        // 1. Modificar a tabela existente 'users' para torná-la flexível
        Schema::table('users', function (Blueprint $table) {
            // Tornamos o name, email e password anuláveis para o fluxo sem senha
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            
            // Adiciona o campo para controlo de verificação do telefone por OTP
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
        });

        // 2. Criar a nova tabela para os códigos temporários de SMS/WhatsApp
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->index();
            $table->string('code');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverte a criação da tabela temporária
        Schema::dropIfExists('phone_verifications');

        // Nota: O rollback de campos modificados para "nullable(false)" exigiria 
        // garantir que não existem registos nulos na base de dados antes de reverter.
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_verified_at');
        });
    }
};