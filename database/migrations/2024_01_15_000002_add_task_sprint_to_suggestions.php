<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaskSprintToSuggestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function (Blueprint $table) {
            // Agregar campos para vincular sugerencias con tareas y sprints
            $table->uuid('task_id')->nullable()->after('project_id');
            $table->uuid('sprint_id')->nullable()->after('task_id');
            
            // Agregar foreign keys
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->foreign('sprint_id')->references('id')->on('sprints')->onDelete('set null');
            
            // Agregar índices para optimizar consultas
            $table->index(['task_id', 'status']);
            $table->index(['sprint_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suggestions', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign(['task_id']);
            $table->dropForeign(['sprint_id']);
            
            // Eliminar índices
            $table->dropIndex(['task_id', 'status']);
            $table->dropIndex(['sprint_id', 'status']);
            
            // Eliminar columnas
            $table->dropColumn(['task_id', 'sprint_id']);
        });
    }
}
