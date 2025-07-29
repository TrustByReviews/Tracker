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
        // Convertir password_reset_otps a UUID
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->uuid('id')->change();
        });
        
        // Convertir weekly_reports a UUID
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->uuid('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir password_reset_otps a auto-increment
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->id()->change();
        });
        
        // Revertir weekly_reports a auto-increment
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->id()->change();
        });
    }
}; 