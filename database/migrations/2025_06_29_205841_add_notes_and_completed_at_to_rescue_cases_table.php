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
        Schema::table('rescue_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('rescue_cases', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rescue_cases', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->dropColumn(['notes', 'completed_at']);
        });
    }
};
