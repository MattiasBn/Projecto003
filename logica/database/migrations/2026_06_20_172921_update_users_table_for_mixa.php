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
        // Redes Sociais / Auth
        if (!Schema::hasColumn('users', 'google_id')) $table->string('google_id')->nullable()->after('id');
        if (!Schema::hasColumn('users', 'facebook_id')) $table->string('facebook_id')->nullable()->after('google_id');
        if (!Schema::hasColumn('users', 'provider')) $table->string('provider')->nullable()->after('facebook_id');
        
        // Perfil
        if (!Schema::hasColumn('users', 'avatar')) $table->string('avatar')->nullable();
        if (!Schema::hasColumn('users', 'bio')) $table->text('bio')->nullable();
        
        // Localização
        if (!Schema::hasColumn('users', 'provincia')) $table->string('provincia')->nullable();
        if (!Schema::hasColumn('users', 'municipio')) $table->string('municipio')->nullable();
        if (!Schema::hasColumn('users', 'bairro')) $table->string('bairro')->nullable();
        if (!Schema::hasColumn('users', 'latitude')) $table->decimal('latitude', 10, 8)->nullable();
        if (!Schema::hasColumn('users', 'longitude')) $table->decimal('longitude', 11, 8)->nullable();
        
        // Métricas e Estados
        if (!Schema::hasColumn('users', 'score_credibilidade')) $table->integer('score_credibilidade')->default(100);
        if (!Schema::hasColumn('users', 'total_vendas')) $table->integer('total_vendas')->default(0);
        if (!Schema::hasColumn('users', 'verificado')) $table->boolean('verificado')->default(false);
        if (!Schema::hasColumn('users', 'activo')) $table->boolean('activo')->default(true);
        if (!Schema::hasColumn('users', 'bloqueado')) $table->boolean('bloqueado')->default(false);
        if (!Schema::hasColumn('users', 'fcm_token')) $table->text('fcm_token')->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'google_id', 'facebook_id', 'provider', 'avatar', 'bio', 
            'provincia', 'municipio', 'bairro', 'latitude', 'longitude', 
            'score_credibilidade', 'total_vendas', 'verificado', 'activo', 'bloqueado', 'fcm_token'
        ]);
    });
}
};
