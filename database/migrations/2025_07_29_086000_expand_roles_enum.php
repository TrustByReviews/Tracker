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
        // Expandir enum value en roles para incluir team_leader
        DB::statement("ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_value_check");
        DB::statement("ALTER TABLE roles ALTER COLUMN value TYPE VARCHAR");
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_value_check CHECK (value IN ('admin', 'developer', 'team_leader'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a valores originales
        DB::statement("ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_value_check");
        DB::statement("ALTER TABLE roles ALTER COLUMN value TYPE VARCHAR");
        DB::statement("ALTER TABLE roles ADD CONSTRAINT roles_value_check CHECK (value IN ('admin', 'developer'))");
    }
}; 