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
        // Solo expandir enum status en users para que coincida con projects
        // No tocamos work_time por ahora para evitar conflictos
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive', 'paused', 'completed'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir enum status en users
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
        DB::statement("ALTER TABLE users ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive'))");
    }
}; 