<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar la columna value de enum a string para permitir 'client'
        Schema::table('roles', function (Blueprint $table) {
            $table->string('value')->default('developer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a enum (esto puede fallar si hay valores 'client')
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('value', ['admin', 'team_leader', 'developer'])->default('developer')->change();
        });
    }
};
