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
        // Drop foreign key using raw SQL if it exists
        try {
            \DB::statement('ALTER TABLE vulnerable_groups DROP FOREIGN KEY vulnerable_groups_serial_number_foreign');
        } catch (\Exception $e) {}

        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // Change serial_number back to string, required and unique
            $table->string('serial_number')->unique()->change();

            // Add user_id column as unsignedBigInteger nullable (if not exists)
            if (!Schema::hasColumn('vulnerable_groups', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vulnerable_groups', function (Blueprint $table) {
            // Remove user_id column
            if (Schema::hasColumn('vulnerable_groups', 'user_id')) {
                $table->dropColumn('user_id');
            }
            // Optionally, you could revert serial_number to unsignedBigInteger again here
        });
    }
};
