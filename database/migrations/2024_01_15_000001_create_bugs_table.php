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
        Schema::create('bugs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Información básica del bug
            $table->string('title');
            $table->text('description');
            $table->longText('long_description')->nullable();
            $table->enum('status', ['new', 'assigned', 'in progress', 'resolved', 'verified', 'closed', 'reopened'])->default('new');
            $table->enum('importance', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('bug_type', ['frontend', 'backend', 'database', 'api', 'ui_ux', 'performance', 'security', 'other'])->default('other');
            
            // Información del entorno
            $table->string('environment')->nullable(); // development, staging, production
            $table->string('browser_info')->nullable();
            $table->string('os_info')->nullable();
            
            // Detalles del bug
            $table->longText('steps_to_reproduce')->nullable();
            $table->longText('expected_behavior')->nullable();
            $table->longText('actual_behavior')->nullable();
            $table->json('attachments')->nullable();
            $table->string('tags')->nullable();
            
            // Métricas
            $table->enum('reproducibility', ['always', 'sometimes', 'rarely', 'unable'])->default('sometimes');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('priority_score')->default(0);
            
            // Relaciones
            $table->uuid('sprint_id')->nullable();
            $table->uuid('project_id');
            $table->uuid('user_id')->nullable(); // asignado a
            $table->uuid('assigned_by')->nullable(); // quien lo asignó
            $table->timestamp('assigned_at')->nullable();
            
            // Tiempo estimado y real
            $table->integer('estimated_hours')->default(0);
            $table->integer('estimated_minutes')->default(0);
            $table->integer('actual_hours')->default(0);
            $table->integer('actual_minutes')->default(0);
            $table->integer('total_time_seconds')->default(0);
            
            // Seguimiento de trabajo
            $table->timestamp('work_started_at')->nullable();
            $table->boolean('is_working')->default(false);
            $table->timestamp('auto_close_at')->nullable();
            $table->integer('alert_count')->default(0);
            $table->timestamp('last_alert_at')->nullable();
            $table->boolean('auto_paused')->default(false);
            $table->timestamp('auto_paused_at')->nullable();
            $table->string('auto_pause_reason')->nullable();
            
            // Resolución
            $table->longText('resolution_notes')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['project_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['sprint_id', 'status']);
            $table->index(['importance', 'status']);
            $table->index(['bug_type', 'status']);
            $table->index('priority_score');
            
            // Foreign keys
            $table->foreign('sprint_id')->references('id')->on('sprints')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
}; 