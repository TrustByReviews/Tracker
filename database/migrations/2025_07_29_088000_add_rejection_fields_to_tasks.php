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
            $table->text('rejection_reason')->nullable()->after('actual_hours');
            $table->uuid('rejected_by')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->uuid('approved_by')->nullable()->after('rejected_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Foreign keys
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['rejection_reason', 'rejected_by', 'rejected_at', 'approved_by', 'approved_at']);
        });
    }
}; 