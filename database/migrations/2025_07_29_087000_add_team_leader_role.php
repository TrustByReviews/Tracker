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
        // First, update the roles table to include team_leader
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('value', ['admin', 'developer', 'team_leader'])->default('developer')->change();
        });
        
        // Insert the team_leader role
        DB::table('roles')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'team_leader',
            'value' => 'team_leader',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove team_leader role
        DB::table('roles')->where('name', 'team_leader')->delete();
        
        // Revert the enum change
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('value', ['admin', 'developer'])->default('developer')->change();
        });
    }
}; 