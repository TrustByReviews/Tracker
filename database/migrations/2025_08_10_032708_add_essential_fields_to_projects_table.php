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
            // Información General Básica
            $table->text('objectives')->nullable()->after('description');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('objectives');
            $table->enum('category', ['web', 'mobile', 'backend', 'iot', 'other'])->default('web')->after('priority');
            $table->enum('development_type', ['new', 'maintenance', 'improvement'])->default('new')->after('category');
            
            // Planificación Básica
            $table->date('planned_start_date')->nullable()->after('development_type');
            $table->date('planned_end_date')->nullable()->after('planned_start_date');
            $table->date('actual_start_date')->nullable()->after('planned_end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->enum('methodology', ['scrum', 'kanban', 'waterfall', 'hybrid'])->default('scrum')->after('actual_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'objectives',
                'priority',
                'category',
                'development_type',
                'planned_start_date',
                'planned_end_date',
                'actual_start_date',
                'actual_end_date',
                'methodology'
            ]);
        });
    }
};
