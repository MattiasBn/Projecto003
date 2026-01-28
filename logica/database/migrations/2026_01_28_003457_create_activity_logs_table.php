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
        
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->string('entity_type'); // user, produto, etc
            $table->unsignedBigInteger('entity_id');

            $table->string('action'); // created, updated, deleted
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->unsignedBigInteger('performed_by')->nullable(); // quem fez
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
