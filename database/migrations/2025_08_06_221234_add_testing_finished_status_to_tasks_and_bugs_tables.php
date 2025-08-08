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
        // Agregar 'testing_finished' al enum de qa_status en tasks
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_qa_status_check");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_qa_status_check CHECK (qa_status IN ('ready_for_test', 'testing', 'testing_paused', 'testing_finished', 'approved', 'rejected'))");
        
        // Agregar 'testing_finished' al enum de qa_status en bugs
        DB::statement("ALTER TABLE bugs DROP CONSTRAINT IF EXISTS bugs_qa_status_check");
        DB::statement("ALTER TABLE bugs ADD CONSTRAINT bugs_qa_status_check CHECK (qa_status IN ('ready_for_test', 'testing', 'testing_paused', 'testing_finished', 'approved', 'rejected'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en tasks
        DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_qa_status_check");
        DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_qa_status_check CHECK (qa_status IN ('ready_for_test', 'testing', 'testing_paused', 'approved', 'rejected'))");
        
        // Revertir cambios en bugs
        DB::statement("ALTER TABLE bugs DROP CONSTRAINT IF EXISTS bugs_qa_status_check");
        DB::statement("ALTER TABLE bugs ADD CONSTRAINT bugs_qa_status_check CHECK (qa_status IN ('ready_for_test', 'testing', 'testing_paused', 'approved', 'rejected'))");
    }
};
