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
        // Agregar rol de Team Leader
        DB::table('roles')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Team Leader',
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
        DB::table('roles')->where('value', 'team_leader')->delete();
    }
}; 