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
        // Drop foreign key constraints first
        Schema::table('rescue_cases', function (Blueprint $table) {
            // Drop foreign key constraint for victim_id
            $table->dropForeign(['victim_id']);
            
            // Drop foreign key constraint for rescuer_id
            $table->dropForeign(['rescuer_id']);
        });

        // Change victim_id to string to store serial_number
        Schema::table('rescue_cases', function (Blueprint $table) {
            // Store the current victim_id values
            $table->string('temp_serial_number')->nullable()->after('victim_id');
        });

        // Update the temp_serial_number with the current victim_id values
        DB::table('rescue_cases')->update([
            'temp_serial_number' => DB::raw('victim_id')
        ]);

        // Now modify the columns
        Schema::table('rescue_cases', function (Blueprint $table) {
            // Change victim_id to string
            $table->string('victim_id')->change();
            
            // Change rescuer_id to string
            $table->string('rescuer_id')->nullable()->change();
        });

        // Copy data from temp_serial_number to victim_id
        DB::table('rescue_cases')->update([
            'victim_id' => DB::raw('temp_serial_number')
        ]);

        // Remove the temporary column
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->dropColumn('temp_serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a complex migration, so the down method is not straightforward
        // You would need to manually restore the previous state if needed
        
        // Note: This is a one-way migration in practice
        // You would need to backup and restore data to properly reverse this
    }
};
