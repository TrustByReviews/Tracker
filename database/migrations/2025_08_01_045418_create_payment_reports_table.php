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
        Schema::create('payment_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Desarrollador
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->integer('completed_tasks_count')->default(0);
            $table->integer('in_progress_tasks_count')->default(0);
            $table->json('task_details')->nullable(); // Detalles de las tareas
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'week_start_date']);
            $table->index(['week_start_date', 'week_end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reports');
    }
};
