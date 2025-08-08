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
        // Agregar columnas de testing a tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('qa_testing_started_at')->nullable();
            $table->timestamp('qa_testing_paused_at')->nullable();
            $table->timestamp('qa_testing_finished_at')->nullable();
        });

        // Agregar columnas de testing a bugs
        Schema::table('bugs', function (Blueprint $table) {
            $table->timestamp('qa_testing_started_at')->nullable();
            $table->timestamp('qa_testing_paused_at')->nullable();
            $table->timestamp('qa_testing_finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover columnas de testing de tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'qa_testing_started_at',
                'qa_testing_paused_at',
                'qa_testing_finished_at'
            ]);
        });

        // Remover columnas de testing de bugs
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn([
                'qa_testing_started_at',
                'qa_testing_paused_at',
                'qa_testing_finished_at'
            ]);
        });
    }
};
