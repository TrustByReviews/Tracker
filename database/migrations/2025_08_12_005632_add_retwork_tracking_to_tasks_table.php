<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Campos para rastrear el tiempo de re-trabajo
            $table->integer('original_time_seconds')->nullable()->after('total_time_seconds');
            $table->integer('retwork_time_seconds')->nullable()->after('original_time_seconds');
            $table->timestamp('original_work_finished_at')->nullable()->after('work_finished_at');
            $table->timestamp('retwork_started_at')->nullable()->after('original_work_finished_at');
            $table->boolean('has_been_returned')->default(false)->after('retwork_started_at');
            $table->integer('return_count')->default(0)->after('has_been_returned');
            
            // Campos para rastrear el motivo de devolución
            $table->string('last_returned_by')->nullable()->after('return_count'); // 'qa' o 'team_leader'
            $table->timestamp('last_returned_at')->nullable()->after('last_returned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'original_time_seconds',
                'retwork_time_seconds', 
                'original_work_finished_at',
                'retwork_started_at',
                'has_been_returned',
                'return_count',
                'last_returned_by',
                'last_returned_at'
            ]);
        });
    }
};
