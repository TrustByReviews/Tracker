<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('task_id')->nullable();
            $table->enum('activity_type', ['login', 'logout', 'task_start', 'task_pause', 'task_resume', 'task_finish']);
            $table->timestamp('activity_time_colombia'); // Hora en zona horaria de Colombia
            $table->timestamp('activity_time_utc'); // Hora en UTC para conversiones
            $table->integer('session_duration_minutes')->nullable(); // Duración de la sesión en minutos
            $table->string('time_period')->nullable(); // 'morning', 'afternoon', 'evening', 'night'
            $table->json('metadata')->nullable(); // Datos adicionales como IP, user agent, etc.
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            
            $table->index(['user_id', 'activity_time_colombia']);
            $table->index(['activity_type', 'activity_time_colombia']);
            $table->index(['time_period', 'activity_time_colombia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_activity_logs');
    }
};
