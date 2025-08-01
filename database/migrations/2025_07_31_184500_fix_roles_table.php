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
        // Primero eliminar la restricción existente
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('value');
        });

        // Agregar la columna con los valores correctos
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('value', ['admin', 'team_leader', 'developer'])->default('developer')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('value');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->enum('value', ['admin', 'developer'])->default('user')->after('name');
        });
    }
}; 