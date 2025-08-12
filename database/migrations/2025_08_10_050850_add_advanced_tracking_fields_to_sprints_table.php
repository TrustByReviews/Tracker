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
            // Métricas de velocidad y progreso
            $table->integer('planned_velocity')->nullable()->after('acceptance_criteria');
            $table->integer('actual_velocity')->nullable()->after('planned_velocity');
            $table->decimal('velocity_deviation', 5, 2)->nullable()->after('actual_velocity');
            $table->decimal('progress_percentage', 5, 2)->default(0)->after('velocity_deviation');
            
            // Bloqueadores y riesgos
            $table->json('blockers')->nullable()->after('progress_percentage');
            $table->json('risks')->nullable()->after('blockers');
            $table->text('blocker_resolution_notes')->nullable()->after('risks');
            
            // Criterios de aceptación avanzados
            $table->json('detailed_acceptance_criteria')->nullable()->after('blocker_resolution_notes');
            $table->json('definition_of_done')->nullable()->after('detailed_acceptance_criteria');
            $table->json('quality_gates')->nullable()->after('definition_of_done');
            
            // Métricas de calidad
            $table->integer('bugs_found')->default(0)->after('quality_gates');
            $table->integer('bugs_resolved')->default(0)->after('bugs_found');
            $table->decimal('bug_resolution_rate', 5, 2)->nullable()->after('bugs_resolved');
            $table->integer('code_reviews_completed')->default(0)->after('bug_resolution_rate');
            $table->integer('code_reviews_pending')->default(0)->after('code_reviews_completed');
            
            // Seguimiento de reuniones
            $table->integer('daily_scrums_held')->default(0)->after('code_reviews_pending');
            $table->integer('daily_scrums_missed')->default(0)->after('daily_scrums_held');
            $table->decimal('daily_scrum_attendance_rate', 5, 2)->nullable()->after('daily_scrums_missed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn([
                'planned_velocity',
                'actual_velocity',
                'velocity_deviation',
                'progress_percentage',
                'blockers',
                'risks',
                'blocker_resolution_notes',
                'detailed_acceptance_criteria',
                'definition_of_done',
                'quality_gates',
                'bugs_found',
                'bugs_resolved',
                'bug_resolution_rate',
                'code_reviews_completed',
                'code_reviews_pending',
                'daily_scrums_held',
                'daily_scrums_missed',
                'daily_scrum_attendance_rate'
            ]);
        });
    }
};
