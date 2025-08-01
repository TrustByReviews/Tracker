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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->enum('type', ['temporary', 'permanent'])->default('temporary');
            $table->timestamp('expires_at')->nullable(); // Para permisos temporales
            $table->text('reason')->nullable(); // Razón por la que se otorgó el permiso
            $table->uuid('granted_by')->nullable(); // Quién otorgó el permiso
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas
            $table->index(['user_id', 'permission_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
}; 