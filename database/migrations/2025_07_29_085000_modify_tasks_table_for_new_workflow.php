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
            // Remove estimated start/finish dates as they're no longer needed
            $table->dropColumn(['estimated_start', 'estimated_finish']);
            
            // Add new assignment tracking fields
            $table->uuid('assigned_by')->nullable()->after('user_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            
            // Add time tracking fields
            $table->integer('total_time_seconds')->default(0)->after('actual_hours');
            $table->timestamp('work_started_at')->nullable()->after('total_time_seconds');
            $table->boolean('is_working')->default(false)->after('work_started_at');
            
            // Add approval workflow fields
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_working');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->uuid('reviewed_by')->nullable()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            
            // Add foreign key constraints
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Restore estimated dates
            $table->date('estimated_start')->nullable();
            $table->date('estimated_finish')->nullable();
            
            // Remove new fields
            $table->dropForeign(['assigned_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'assigned_by',
                'assigned_at',
                'total_time_seconds',
                'work_started_at',
                'is_working',
                'approval_status',
                'rejection_reason',
                'reviewed_by',
                'reviewed_at'
            ]);
        });
    }
}; 