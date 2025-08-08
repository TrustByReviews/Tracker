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
        // Para PostgreSQL, vamos a usar una aproximación más directa
        DB::statement("ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_value_check");
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_value_check CHECK (value IN ('admin', 'team_leader', 'developer', 'qa'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios para PostgreSQL
        DB::statement("ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_value_check");
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_value_check CHECK (value IN ('admin', 'team_leader', 'developer'))");
    }
}; 