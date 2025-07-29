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
        // Para PostgreSQL, necesitamos usar SQL raw para modificar el enum
        // Primero actualizamos los valores incorrectos
        DB::statement("UPDATE tasks SET category = 'full stack' WHERE category = 'frontend, backend'");
        
        // Luego modificamos la restricción
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_category_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN category TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_category_check CHECK (category IN ('frontend', 'backend', 'full stack', 'design', 'deployment', 'fixes'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_category_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN category TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_category_check CHECK (category IN ('frontend, backend', 'full stack', 'design', 'deployment', 'fixes'))");
    }
};
