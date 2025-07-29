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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description');
            $table->enum('status', ['to do', 'in progress', 'done'])->default('to do');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('category', ['frontend', 'backend', 'full stack', 'design', 'deployment', 'fixes'])
                ->default('full stack');
            $table->integer('story_points');

            $table->uuid('sprint_id');
            $table->foreign('sprint_id')->references('id')->on('sprints')->onDelete('cascade');

            $table->uuid('user_id')->nullable(true);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->date('estimated_start')->nullable(true);
            $table->date('estimated_finish')->nullable(true);
            $table->integer('estimated_hours')->nullable(true);
            $table->date('actual_start')->nullable(true);
            $table->date('actual_finish')->nullable(true);
            $table->integer('actual_hours')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
