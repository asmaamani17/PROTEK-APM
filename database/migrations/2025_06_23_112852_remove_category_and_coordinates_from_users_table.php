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
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            
            // Check if columns exist before adding them to drop list
            if (Schema::hasColumn('users', 'category')) {
                $columnsToDrop[] = 'category';
            }
            if (Schema::hasColumn('users', 'lat')) {
                $columnsToDrop[] = 'lat';
            }
            if (Schema::hasColumn('users', 'lng')) {
                $columnsToDrop[] = 'lng';
            }
            
            // Drop all columns at once if there are any to drop
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the columns back if rolling back
            if (!Schema::hasColumn('users', 'category')) {
                $table->string('category')->nullable()->after('daerah');
            }
            if (!Schema::hasColumn('users', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('category');
            }
            if (!Schema::hasColumn('users', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }
        });
    }
};
