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
        // First, add a temporary column to store the new status values
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->string('status_temp')->nullable()->after('status');
        });

        // Map old status to new status
        $statusMap = [
            'mohon_bantuan' => 'mohon_bantuan',
            'dalam_tindakan' => 'dalam_tindakan',
            'sedang_diselamatkan' => 'sedang_diselamatkan',
            'bantuan_selesai' => 'bantuan_selesai'
        ];

        // Update the temporary column with new status values
        foreach ($statusMap as $oldStatus => $newStatus) {
            DB::table('rescue_cases')
                ->where('status', $oldStatus)
                ->update(['status_temp' => $newStatus]);
        }

        // Remove the old status column
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Add the new status column with the updated enum values
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->enum('status', [
                'tiada_bantuan', 
                'mohon_bantuan', 
                'dalam_tindakan', 
                'sedang_diselamatkan', 
                'bantuan_selesai'
            ])->default('tiada_bantuan')->after('lng');
        });

        // Copy values from temporary column to the new status column
        DB::table('rescue_cases')
            ->whereNotNull('status_temp')
            ->update(['status' => DB::raw('status_temp')]);
            
        // Set default status for any records that might have null status_temp
        DB::table('rescue_cases')
            ->whereNull('status')
            ->update(['status' => 'tiada_bantuan']);

        // Remove the temporary column
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->dropColumn('status_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration - we can't reliably convert back to the old status values
        // You would need to implement a proper rollback strategy if needed
    }
};
