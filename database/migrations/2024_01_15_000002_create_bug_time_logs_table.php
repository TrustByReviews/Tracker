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
        Schema::create('bug_time_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bug_id');
            $table->uuid('user_id');
            
            // Tiempo de la sesión
            $table->timestamp('started_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            
            // Notas y auto-pausa
            $table->text('notes')->nullable();
            $table->boolean('auto_paused')->default(false);
            $table->string('auto_pause_reason')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['bug_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
            $table->index('started_at');
            
            // Foreign keys
            $table->foreign('bug_id')->references('id')->on('bugs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_time_logs');
    }
}; 