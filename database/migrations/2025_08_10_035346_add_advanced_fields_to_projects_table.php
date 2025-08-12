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
        Schema::table('projects', function (Blueprint $table) {
            // Tecnología y Arquitectura
            $table->json('technologies')->nullable()->after('methodology');
            $table->json('programming_languages')->nullable()->after('technologies');
            $table->json('frameworks')->nullable()->after('programming_languages');
            $table->string('database_type')->nullable()->after('frameworks');
            $table->enum('architecture', ['monolithic', 'microservices', 'serverless', 'hybrid'])->nullable()->after('database_type');
            $table->json('external_integrations')->nullable()->after('architecture');
            
            // Equipo y Stakeholders
            $table->string('project_owner')->nullable()->after('external_integrations');
            $table->string('product_owner')->nullable()->after('project_owner');
            $table->json('stakeholders')->nullable()->after('product_owner');
            
            // Planificación Avanzada
            $table->json('milestones')->nullable()->after('stakeholders');
            $table->integer('estimated_velocity')->nullable()->after('milestones'); // puntos por sprint
            $table->string('current_sprint')->nullable()->after('estimated_velocity');
            
            // Presupuesto y Recursos
            $table->decimal('estimated_budget', 15, 2)->nullable()->after('current_sprint');
            $table->decimal('used_budget', 15, 2)->nullable()->after('estimated_budget');
            $table->json('assigned_resources')->nullable()->after('used_budget');
            
            // Seguimiento y Métricas
            $table->decimal('progress_percentage', 5, 2)->default(0.00)->after('assigned_resources');
            $table->json('identified_risks')->nullable()->after('progress_percentage');
            $table->integer('open_issues')->default(0)->after('identified_risks');
            $table->string('documentation_url')->nullable()->after('open_issues');
            $table->string('repository_url')->nullable()->after('documentation_url');
            $table->string('task_board_url')->nullable()->after('repository_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'technologies',
                'programming_languages',
                'frameworks',
                'database_type',
                'architecture',
                'external_integrations',
                'project_owner',
                'product_owner',
                'stakeholders',
                'milestones',
                'estimated_velocity',
                'current_sprint',
                'estimated_budget',
                'used_budget',
                'assigned_resources',
                'progress_percentage',
                'identified_risks',
                'open_issues',
                'documentation_url',
                'repository_url',
                'task_board_url'
            ]);
        });
    }
};
