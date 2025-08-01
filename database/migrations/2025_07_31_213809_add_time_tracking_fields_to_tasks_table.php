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
            // Campos para tracking de tiempo
            $table->integer('total_time_seconds')->default(0)->after('actual_hours');
            $table->timestamp('work_started_at')->nullable()->after('total_time_seconds');
            $table->boolean('is_working')->default(false)->after('work_started_at');
            
            // Campos para aprobación de tareas
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('is_working');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->uuid('reviewed_by')->nullable()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            
            // Campos adicionales
            $table->uuid('assigned_by')->nullable()->after('reviewed_at');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            
            // Foreign keys
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes para performance
            $table->index(['is_working']);
            $table->index(['approval_status']);
            $table->index(['user_id', 'is_working']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['assigned_by']);
            
            // Drop indexes
            $table->dropIndex(['is_working']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['user_id', 'is_working']);
            
            // Drop columns
            $table->dropColumn([
                'total_time_seconds',
                'work_started_at',
                'is_working',
                'approval_status',
                'rejection_reason',
                'reviewed_by',
                'reviewed_at',
                'assigned_by',
                'assigned_at'
            ]);
        });
    }
};
