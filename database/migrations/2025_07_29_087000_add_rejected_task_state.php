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
        // Expandir enum status en tasks para incluir estado "rejected"
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('to do', 'in progress', 'ready for test', 'in review', 'rejected', 'done'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a estados anteriores
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
        DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('to do', 'in progress', 'ready for test', 'in review', 'done'))");
    }
}; 