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
            $table->boolean('team_leader_final_approval')->default(false);
            $table->timestamp('team_leader_final_approval_at')->nullable();
            $table->text('team_leader_final_notes')->nullable();
            $table->boolean('team_leader_requested_changes')->default(false);
            $table->timestamp('team_leader_requested_changes_at')->nullable();
            $table->text('team_leader_change_notes')->nullable();
            $table->uuid('team_leader_reviewed_by')->nullable();
            $table->foreign('team_leader_reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropForeign(['team_leader_reviewed_by']);
            $table->dropColumn([
                'team_leader_final_approval',
                'team_leader_final_approval_at',
                'team_leader_final_notes',
                'team_leader_requested_changes',
                'team_leader_requested_changes_at',
                'team_leader_change_notes',
                'team_leader_reviewed_by',
            ]);
        });
    }
};
