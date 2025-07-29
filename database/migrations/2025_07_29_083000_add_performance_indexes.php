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
        // Índices para users
        if (!Schema::hasIndex('users', 'users_status_work_time_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['status', 'work_time']);
            });
        }
        
        if (!Schema::hasIndex('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email');
            });
        }

        // Índices para projects
        if (!Schema::hasIndex('projects', 'projects_status_created_by_index')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->index(['status', 'created_by']);
            });
        }
        
        if (!Schema::hasIndex('projects', 'projects_created_by_index')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->index('created_by');
            });
        }

        // Índices para tasks
        if (!Schema::hasIndex('tasks', 'tasks_status_priority_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['status', 'priority']);
            });
        }
        
        if (!Schema::hasIndex('tasks', 'tasks_user_id_status_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
            });
        }
        
        if (!Schema::hasIndex('tasks', 'tasks_project_id_status_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['project_id', 'status']);
            });
        }
        
        if (!Schema::hasIndex('tasks', 'tasks_sprint_id_status_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['sprint_id', 'status']);
            });
        }
        
        if (!Schema::hasIndex('tasks', 'tasks_category_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index('category');
            });
        }

        // Índices para sprints
        if (!Schema::hasIndex('sprints', 'sprints_project_id_start_date_index')) {
            Schema::table('sprints', function (Blueprint $table) {
                $table->index(['project_id', 'start_date']);
            });
        }
        
        if (!Schema::hasIndex('sprints', 'sprints_start_date_end_date_index')) {
            Schema::table('sprints', function (Blueprint $table) {
                $table->index(['start_date', 'end_date']);
            });
        }

        // Índices para password_reset_otps
        if (!Schema::hasIndex('password_reset_otps', 'password_reset_otps_email_used_index')) {
            Schema::table('password_reset_otps', function (Blueprint $table) {
                $table->index(['email', 'used']);
            });
        }
        
        if (!Schema::hasIndex('password_reset_otps', 'password_reset_otps_expires_at_index')) {
            Schema::table('password_reset_otps', function (Blueprint $table) {
                $table->index('expires_at');
            });
        }

        // Índices para weekly_reports
        if (!Schema::hasIndex('weekly_reports', 'weekly_reports_start_date_end_date_index')) {
            Schema::table('weekly_reports', function (Blueprint $table) {
                $table->index(['start_date', 'end_date']);
            });
        }
        
        if (!Schema::hasIndex('weekly_reports', 'weekly_reports_created_at_index')) {
            Schema::table('weekly_reports', function (Blueprint $table) {
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices de users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status', 'work_time']);
            $table->dropIndex(['email']);
        });

        // Remover índices de projects
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_by']);
            $table->dropIndex(['created_by']);
        });

        // Remover índices de tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['project_id', 'status']);
            $table->dropIndex(['sprint_id', 'status']);
            $table->dropIndex(['category']);
        });

        // Remover índices de sprints
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'start_date']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        // Remover índices de password_reset_otps
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->dropIndex(['email', 'used']);
            $table->dropIndex(['expires_at']);
        });

        // Remover índices de weekly_reports
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['created_at']);
        });
    }
}; 