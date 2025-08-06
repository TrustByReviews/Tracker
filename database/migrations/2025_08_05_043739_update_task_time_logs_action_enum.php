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
        // Actualizar el enum para incluir 'resume_auto_paused'
        DB::statement("ALTER TABLE task_time_logs DROP CONSTRAINT IF EXISTS task_time_logs_action_check");
        DB::statement("ALTER TABLE task_time_logs ADD CONSTRAINT task_time_logs_action_check CHECK (action IN ('start', 'pause', 'resume', 'finish', 'resume_auto_paused'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el enum a su estado original
        DB::statement("ALTER TABLE task_time_logs DROP CONSTRAINT IF EXISTS task_time_logs_action_check");
        DB::statement("ALTER TABLE task_time_logs ADD CONSTRAINT task_time_logs_action_check CHECK (action IN ('start', 'pause', 'resume', 'finish'))");
    }
};
