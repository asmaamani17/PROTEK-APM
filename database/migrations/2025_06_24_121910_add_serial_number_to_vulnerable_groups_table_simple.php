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
            if (!Schema::hasColumn('vulnerable_groups', 'serial_number')) {
                // Add the column as nullable first
                $table->string('serial_number')->after('id')->nullable();
            }
        });
        
        // Populate with unique values
        $vulnerableGroups = \DB::table('vulnerable_groups')->get();
        foreach ($vulnerableGroups as $index => $group) {
            \DB::table('vulnerable_groups')
                ->where('id', $group->id)
                ->update(['serial_number' => (string)($index + 1)]);
        }
        
        // Now make it required and unique using raw SQL
        if (Schema::hasColumn('vulnerable_groups', 'serial_number')) {
            \DB::statement('ALTER TABLE vulnerable_groups MODIFY serial_number VARCHAR(255) NOT NULL');
            \DB::statement('ALTER TABLE vulnerable_groups ADD UNIQUE (serial_number)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            if (Schema::hasColumn('vulnerable_groups', 'serial_number')) {
                // Drop the unique constraint first
                $table->dropUnique(['serial_number']);
                // Then drop the column
                $table->dropColumn('serial_number');
            }
        });
    }
};
