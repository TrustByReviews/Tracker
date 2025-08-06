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
            // Campos para el sistema de cierre automático
            $table->timestamp('auto_close_at')->nullable()->after('work_started_at');
            $table->integer('alert_count')->default(0)->after('auto_close_at');
            $table->timestamp('last_alert_at')->nullable()->after('alert_count');
            $table->boolean('auto_paused')->default(false)->after('last_alert_at');
            $table->timestamp('auto_paused_at')->nullable()->after('auto_paused');
            $table->text('auto_pause_reason')->nullable()->after('auto_paused_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'auto_close_at',
                'alert_count',
                'last_alert_at',
                'auto_paused',
                'auto_paused_at',
                'auto_pause_reason'
            ]);
        });
    }
};
