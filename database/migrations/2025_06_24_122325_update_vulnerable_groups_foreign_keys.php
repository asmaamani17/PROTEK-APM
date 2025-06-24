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
        // First, ensure the serial_number column has the correct data type to match the users.id
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // Make sure the column exists and is the right type
            if (Schema::hasColumn('vulnerable_groups', 'serial_number')) {
                // Change the data type to match users.id (assuming it's a bigint)
                $table->unsignedBigInteger('serial_number')->change();
                
                // Add the foreign key constraint
                $table->foreign('serial_number')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            }
            
            // Remove the user_id column if it exists
            if (Schema::hasColumn('vulnerable_groups', 'user_id')) {
                // First, drop the foreign key constraint if it exists
                $table->dropForeign(['user_id']);
                // Then drop the column
                $table->dropColumn('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['serial_number']);
            
            // Change the column back to string
            $table->string('serial_number')->change();
            
            // Add back the user_id column
            if (!Schema::hasColumn('vulnerable_groups', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                
                // Add the foreign key constraint
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            }
        });
    }
};
