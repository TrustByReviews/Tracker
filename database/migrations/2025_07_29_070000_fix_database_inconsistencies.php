<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar campo project_id a tasks para relación directa
        if (!Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->uuid('project_id')->nullable()->after('sprint_id');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            });
            
            // Poblar project_id basado en sprint_id
            DB::statement("
                UPDATE tasks 
                SET project_id = (
                    SELECT project_id 
                    FROM sprints 
                    WHERE sprints.id = tasks.sprint_id
                )
            ");
        }
        
        // 2. Agregar default a story_points en tasks
        DB::statement("UPDATE tasks SET story_points = 0 WHERE story_points IS NULL");
        DB::statement("ALTER TABLE tasks ALTER COLUMN story_points SET DEFAULT 0");
        
        // 3. Corregir default en roles (cambiar 'user' a 'developer')
        DB::statement("UPDATE roles SET value = 'developer' WHERE value = 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Remover default de story_points
        DB::statement("ALTER TABLE tasks ALTER COLUMN story_points DROP DEFAULT");
        
        // 2. Remover campo project_id de tasks
        if (Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
    }
}; 