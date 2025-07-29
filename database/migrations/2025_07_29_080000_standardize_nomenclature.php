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
        // Renombrar create_by a created_by en la tabla projects
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('create_by', 'created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el cambio
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('created_by', 'create_by');
        });
    }
}; 