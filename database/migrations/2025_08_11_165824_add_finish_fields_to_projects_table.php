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
        Schema::table('projects', function (Blueprint $table) {
            // Campos de finalización del proyecto
            $table->text('achievements')->nullable()->after('task_board_url');
            $table->text('difficulties')->nullable()->after('achievements');
            $table->text('lessons_learned')->nullable()->after('difficulties');
            $table->text('final_documentation')->nullable()->after('lessons_learned');
            $table->string('termination_reason')->nullable()->after('final_documentation');
            $table->text('custom_reason')->nullable()->after('termination_reason');
            $table->json('final_attachments')->nullable()->after('custom_reason');
            $table->boolean('is_finished')->default(false)->after('final_attachments');
            $table->timestamp('finished_at')->nullable()->after('is_finished');
            $table->uuid('finished_by')->nullable()->after('finished_at');
            
            // Índices para mejorar el rendimiento
            $table->index('is_finished');
            $table->index('finished_at');
            $table->index('finished_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['is_finished']);
            $table->dropIndex(['finished_at']);
            $table->dropIndex(['finished_by']);
            
            $table->dropColumn([
                'achievements',
                'difficulties',
                'lessons_learned',
                'final_documentation',
                'termination_reason',
                'custom_reason',
                'final_attachments',
                'is_finished',
                'finished_at',
                'finished_by'
            ]);
        });
    }
};
