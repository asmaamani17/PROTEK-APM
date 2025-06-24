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
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // If both columns exist, we need to migrate data first
            if (Schema::hasColumn('vulnerable_groups', 'serial_number') && 
                Schema::hasColumn('vulnerable_groups', 'user_id')) {
                
                // First, get all vulnerable groups with serial_number that exist in users table
                $validMappings = \DB::table('vulnerable_groups')
                    ->join('users', 'vulnerable_groups.serial_number', '=', 'users.id')
                    ->select('vulnerable_groups.id as vg_id', 'users.id as user_id')
                    ->get();
                
                // Update only the valid mappings
                foreach ($validMappings as $mapping) {
                    \DB::table('vulnerable_groups')
                        ->where('id', $mapping->vg_id)
                        ->update(['user_id' => $mapping->user_id]);
                }
                
                // Now we can safely drop the serial_number column
                $table->dropColumn('serial_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // Add back the serial_number column first
            if (!Schema::hasColumn('vulnerable_groups', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('user_id');
            }
            
            // Then update the serial_number with user_id values
            // We'll do this in a separate statement to ensure the column exists first
            if (Schema::hasColumn('vulnerable_groups', 'serial_number')) {
                // Copy data back from user_id to serial_number for valid user IDs
                $validMappings = \DB::table('vulnerable_groups')
                    ->whereNotNull('user_id')
                    ->get(['id', 'user_id']);
                
                foreach ($validMappings as $mapping) {
                    \DB::table('vulnerable_groups')
                        ->where('id', $mapping->id)
                        ->update(['serial_number' => $mapping->user_id]);
                }
            }
        });
    }
};
