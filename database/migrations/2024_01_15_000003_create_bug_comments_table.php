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
        Schema::create('bug_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bug_id');
            $table->uuid('user_id');
            
            // Contenido del comentario
            $table->longText('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->enum('comment_type', ['general', 'resolution', 'verification', 'reproduction', 'internal'])->default('general');
            
            $table->timestamps();
            
            // Índices
            $table->index(['bug_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('comment_type');
            
            // Foreign keys
            $table->foreign('bug_id')->references('id')->on('bugs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_comments');
    }
}; 