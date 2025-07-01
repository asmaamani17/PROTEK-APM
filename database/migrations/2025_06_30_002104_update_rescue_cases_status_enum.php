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
        // First, update existing status values to the new format
        DB::table('rescue_cases')
            ->where('status', 'new')
            ->update(['status' => 'mohon_bantuan']);
            
        DB::table('rescue_cases')
            ->where('status', 'assigned')
            ->update(['status' => 'dalam_tindakan']);
            
        DB::table('rescue_cases')
            ->where('status', 'rescued')
            ->update(['status' => 'bantuan_selesai']);
            
        DB::table('rescue_cases')
            ->where('status', 'not_found')
            ->update(['status' => 'tidak_ditemui']);
            
        // Now alter the column to use the new enum values
        DB::statement("ALTER TABLE rescue_cases MODIFY COLUMN status ENUM('tiada_bantuan', 'mohon_bantuan', 'dalam_tindakan', 'sedang_diselamatkan', 'bantuan_selesai', 'tidak_ditemui') NOT NULL DEFAULT 'tiada_bantuan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status values back to the original format
        DB::table('rescue_cases')
            ->whereIn('status', ['mohon_bantuan', 'dalam_tindakan', 'sedang_diselamatkan'])
            ->update(['status' => 'new']);
            
        DB::table('rescue_cases')
            ->where('status', 'bantuan_selesai')
            ->update(['status' => 'rescued']);
            
        DB::table('rescue_cases')
            ->where('status', 'tidak_ditemui')
            ->update(['status' => 'not_found']);
            
        // Revert the column to the original enum values
        DB::statement("ALTER TABLE rescue_cases MODIFY COLUMN status ENUM('new', 'assigned', 'rescued', 'not_found') NOT NULL DEFAULT 'new'");
    }
};
