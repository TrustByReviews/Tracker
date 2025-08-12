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
        Schema::table('sprints', function (Blueprint $table) {
            // Información básica mejorada
            $table->text('description')->nullable()->after('name');
            $table->enum('sprint_type', ['regular', 'release', 'hotfix'])->default('regular')->after('status');
            
            // Fechas de planificación y ejecución
            $table->date('planned_start_date')->nullable()->after('sprint_type');
            $table->date('planned_end_date')->nullable()->after('planned_start_date');
            $table->date('actual_start_date')->nullable()->after('planned_end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->integer('duration_days')->nullable()->after('actual_end_date');
            
            // Objetivos y alcance básico
            $table->text('sprint_objective')->nullable()->after('duration_days');
            $table->json('user_stories_included')->nullable()->after('sprint_objective');
            $table->json('assigned_tasks')->nullable()->after('user_stories_included');
            $table->text('acceptance_criteria')->nullable()->after('assigned_tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'sprint_type',
                'planned_start_date',
                'planned_end_date',
                'actual_start_date',
                'actual_end_date',
                'duration_days',
                'sprint_objective',
                'user_stories_included',
                'assigned_tasks',
                'acceptance_criteria'
            ]);
        });
    }
};
