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
            $table->boolean('team_leader_final_approval')->default(false)->after('qa_reviewed_at');
            $table->timestamp('team_leader_final_approval_at')->nullable()->after('team_leader_final_approval');
            $table->text('team_leader_final_notes')->nullable()->after('team_leader_final_approval_at');
            $table->boolean('team_leader_requested_changes')->default(false)->after('team_leader_final_notes');
            $table->timestamp('team_leader_requested_changes_at')->nullable()->after('team_leader_requested_changes');
            $table->text('team_leader_change_notes')->nullable()->after('team_leader_requested_changes_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'team_leader_final_approval',
                'team_leader_final_approval_at',
                'team_leader_final_notes',
                'team_leader_requested_changes',
                'team_leader_requested_changes_at',
                'team_leader_change_notes',
            ]);
        });
    }
};
