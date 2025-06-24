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
            if (!Schema::hasColumn('users', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }
            // These columns are already added by another migration
            // $table->string('category')->nullable()->after('lng');
            // $table->string('daerah')->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'lat')) {
                $columnsToDrop[] = 'lat';
            }
            if (Schema::hasColumn('users', 'lng')) {
                $columnsToDrop[] = 'lng';
            }
            // Don't drop category and daerah as they were added by another migration
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
