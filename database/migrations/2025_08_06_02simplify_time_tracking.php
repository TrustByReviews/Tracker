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
        // 1. Estandarizar tabla tasks
        Schema::table('tasks', function (Blueprint $table) {
            // Agregar work_finished_at si no existe
            if (!Schema::hasColumn('tasks', 'work_finished_at')) {
                $table->timestamp('work_finished_at')->nullable();
            }
        });

        // 2. Estandarizar tabla bugs
        Schema::table('bugs', function (Blueprint $table) {
            // Agregar work_finished_at si no existe
            if (!Schema::hasColumn('bugs', 'work_finished_at')) {
                $table->timestamp('work_finished_at')->nullable();
            }
            
            // Eliminar current_session_start (será reemplazado por work_started_at)
            if (Schema::hasColumn('bugs', 'current_session_start')) {
                $table->dropColumn('current_session_start');
            }
        });

        // 3. Estandarizar tabla bug_time_logs
        Schema::table('bug_time_logs', function (Blueprint $table) {
            // Agregar duration si no existe
            if (!Schema::hasColumn('bug_time_logs', 'duration')) {
                $table->integer('duration')->nullable(); // duración en segundos
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios si es necesario
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'work_finished_at')) {
                $table->dropColumn('work_finished_at');
            }
        });

        Schema::table('bugs', function (Blueprint $table) {
            if (Schema::hasColumn('bugs', 'work_finished_at')) {
                $table->dropColumn('work_finished_at');
            }
            
            if (!Schema::hasColumn('bugs', 'current_session_start')) {
                $table->timestamp('current_session_start')->nullable();
            }
        });

        Schema::table('bug_time_logs', function (Blueprint $table) {
            if (Schema::hasColumn('bug_time_logs', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
}; 