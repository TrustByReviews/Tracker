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
        // Expandir enum status en tasks para incluir nuevos estados
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('to do', 'in progress', 'ready for test', 'in review', 'done'))");
        
        // Actualizar tareas existentes que estén en 'done' para mantener consistencia
        DB::statement("UPDATE tasks SET status = 'done' WHERE status = 'done'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a estados originales
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('to do', 'in progress', 'done'))");
    }
}; 