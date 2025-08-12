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
            // Fase 3: Retrospectiva y Mejoras
            $table->json('achievements')->nullable()->comment('Logros del sprint');
            $table->json('problems')->nullable()->comment('Problemas identificados');
            $table->json('actions_to_take')->nullable()->comment('Acciones a tomar');
            $table->text('retrospective_notes')->nullable()->comment('Notas de la retrospectiva');
            $table->json('lessons_learned')->nullable()->comment('Lecciones aprendidas');
            $table->json('improvement_areas')->nullable()->comment('Áreas de mejora');
            $table->json('team_feedback')->nullable()->comment('Feedback del equipo');
            $table->json('stakeholder_feedback')->nullable()->comment('Feedback de stakeholders');
            $table->decimal('team_satisfaction_score', 3, 1)->nullable()->comment('Puntuación de satisfacción del equipo (1-10)');
            $table->decimal('stakeholder_satisfaction_score', 3, 1)->nullable()->comment('Puntuación de satisfacción de stakeholders (1-10)');
            $table->json('process_improvements')->nullable()->comment('Mejoras de proceso identificadas');
            $table->json('tool_improvements')->nullable()->comment('Mejoras de herramientas identificadas');
            $table->json('communication_improvements')->nullable()->comment('Mejoras de comunicación identificadas');
            $table->json('technical_debt_added')->nullable()->comment('Deuda técnica agregada');
            $table->json('technical_debt_resolved')->nullable()->comment('Deuda técnica resuelta');
            $table->decimal('technical_debt_ratio', 5, 2)->nullable()->comment('Ratio de deuda técnica');
            $table->json('knowledge_shared')->nullable()->comment('Conocimiento compartido');
            $table->json('skills_developed')->nullable()->comment('Habilidades desarrolladas');
            $table->json('mentoring_sessions')->nullable()->comment('Sesiones de mentoring');
            $table->integer('team_velocity_trend')->nullable()->comment('Tendencia de velocidad del equipo');
            $table->decimal('sprint_efficiency_score', 5, 2)->nullable()->comment('Puntuación de eficiencia del sprint (0-100)');
            $table->json('sprint_goals_achieved')->nullable()->comment('Objetivos del sprint logrados');
            $table->json('sprint_goals_partially_achieved')->nullable()->comment('Objetivos del sprint parcialmente logrados');
            $table->json('sprint_goals_not_achieved')->nullable()->comment('Objetivos del sprint no logrados');
            $table->decimal('goal_achievement_rate', 5, 2)->nullable()->comment('Tasa de logro de objetivos (%)');
            $table->text('next_sprint_recommendations')->nullable()->comment('Recomendaciones para el próximo sprint');
            $table->json('sprint_ceremony_effectiveness')->nullable()->comment('Efectividad de las ceremonias del sprint');
            $table->decimal('overall_sprint_rating', 3, 1)->nullable()->comment('Calificación general del sprint (1-10)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn([
                'achievements',
                'problems',
                'actions_to_take',
                'retrospective_notes',
                'lessons_learned',
                'improvement_areas',
                'team_feedback',
                'stakeholder_feedback',
                'team_satisfaction_score',
                'stakeholder_satisfaction_score',
                'process_improvements',
                'tool_improvements',
                'communication_improvements',
                'technical_debt_added',
                'technical_debt_resolved',
                'technical_debt_ratio',
                'knowledge_shared',
                'skills_developed',
                'mentoring_sessions',
                'team_velocity_trend',
                'sprint_efficiency_score',
                'sprint_goals_achieved',
                'sprint_goals_partially_achieved',
                'sprint_goals_not_achieved',
                'goal_achievement_rate',
                'next_sprint_recommendations',
                'sprint_ceremony_effectiveness',
                'overall_sprint_rating'
            ]);
        });
    }
};
