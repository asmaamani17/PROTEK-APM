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
        // First, modify the existing columns if needed
        Schema::table('rescue_cases', function (Blueprint $table) {
            // Check and add new columns if they don't exist
            if (!Schema::hasColumn('rescue_cases', 'victim_name')) {
                $table->string('victim_name')->after('victim_id')->nullable();
            }
            
            if (!Schema::hasColumn('rescue_cases', 'rescuer_name')) {
                $table->string('rescuer_name')->after('rescuer_id')->nullable();
            }
            
            if (!Schema::hasColumn('rescue_cases', 'district')) {
                $table->string('district')->after('lng')->nullable();
            }
            
            // Update status column type and default value
            $table->string('status', 30)->default('tiada_bantuan')->change();
            
            // Add check constraint for status values using raw SQL if it doesn't exist
            $constraintExists = collect(DB::select("SHOW CREATE TABLE rescue_cases"))->first();
            
            if (!str_contains($constraintExists->{'Create Table'}, 'rescue_cases_status_check')) {
                DB::statement(
                    "ALTER TABLE `rescue_cases` ADD CONSTRAINT `rescue_cases_status_check` 
                    CHECK (`status` IN ('tiada_bantuan', 'mohon_bantuan', 'dalam_tindakan', 'sedang_diselamatkan', 'bantuan_selesai', 'tidak_ditemui'))"
                );
            }
            
            // Add timestamps for tracking if they don't exist
            if (!Schema::hasColumn('rescue_cases', 'rescue_started_at')) {
                $table->timestamp('rescue_started_at')->nullable()->after('updated_at');
            }
            
            if (!Schema::hasColumn('rescue_cases', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('rescue_started_at');
            }
            
            if (!Schema::hasColumn('rescue_cases', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('completed_at');
            }
            
            // Add notes if it doesn't exist
            if (!Schema::hasColumn('rescue_cases', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rescue_cases', function (Blueprint $table) {
            // Drop added columns
            $table->dropColumn([
                'victim_name',
                'rescuer_name',
                'district',
                'rescue_started_at',
                'completed_at',
                'requested_at',
                'notes'
            ]);
            
            // Revert status column to original values
            $table->string('status', 20)->default('new')->change();
            
            // Remove the check constraint using raw SQL
            \DB::statement("ALTER TABLE `rescue_cases` DROP CHECK IF EXISTS `rescue_cases_status_check`");
        });
    }
};
