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
        Schema::table('bugs', function (Blueprint $table) {
            // Agregar campos para el workflow de QA en bugs
            $table->enum('qa_status', ['pending', 'ready_for_test', 'testing', 'approved', 'rejected'])->default('pending')->after('status');
            $table->uuid('qa_assigned_to')->nullable()->after('qa_status');
            $table->timestamp('qa_assigned_at')->nullable()->after('qa_assigned_to');
            $table->timestamp('qa_started_at')->nullable()->after('qa_assigned_at');
            $table->timestamp('qa_completed_at')->nullable()->after('qa_started_at');
            $table->text('qa_notes')->nullable()->after('qa_completed_at');
            $table->text('qa_rejection_reason')->nullable()->after('qa_notes');
            $table->uuid('qa_reviewed_by')->nullable()->after('qa_rejection_reason');
            $table->timestamp('qa_reviewed_at')->nullable()->after('qa_reviewed_by');
            
            // Agregar foreign keys
            $table->foreign('qa_assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('qa_reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            // Remover foreign keys
            $table->dropForeign(['qa_assigned_to']);
            $table->dropForeign(['qa_reviewed_by']);
            
            // Remover campos
            $table->dropColumn([
                'qa_status',
                'qa_assigned_to',
                'qa_assigned_at',
                'qa_started_at',
                'qa_completed_at',
                'qa_notes',
                'qa_rejection_reason',
                'qa_reviewed_by',
                'qa_reviewed_at'
            ]);
        });
    }
}; 