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
        // Eliminar la restricción de check en la columna value
        DB::statement('ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_value_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear la restricción de check (opcional)
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_value_check CHECK (value IN ('admin', 'team_leader', 'developer', 'client'))");
    }
};
