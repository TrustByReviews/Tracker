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
        Schema::table('tasks', function (Blueprint $table) {
            // Campos para archivos adjuntos
            $table->json('attachments')->nullable()->after('description');
            $table->text('long_description')->nullable()->after('description');
            
            // Campos para mejor organización
            $table->string('tags')->nullable()->after('category');
            $table->text('acceptance_criteria')->nullable()->after('long_description');
            $table->text('technical_notes')->nullable()->after('acceptance_criteria');
            
            // Campos para mejor seguimiento
            $table->integer('estimated_minutes')->nullable()->after('estimated_hours');
            $table->integer('actual_minutes')->nullable()->after('actual_hours');
            $table->string('complexity_level')->default('medium')->after('priority'); // low, medium, high, critical
            $table->string('task_type')->default('feature')->after('complexity_level'); // feature, bug, improvement, documentation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'attachments',
                'long_description',
                'tags',
                'acceptance_criteria',
                'technical_notes',
                'estimated_minutes',
                'actual_minutes',
                'complexity_level',
                'task_type'
            ]);
        });
    }
}; 