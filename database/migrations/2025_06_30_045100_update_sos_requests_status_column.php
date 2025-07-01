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
        Schema::table('sos_requests', function (Blueprint $table) {
            // Update status column to use new status values
            $table->string('status')
                ->default('mohon_bantuan')
                ->comment('Possible values: mohon_bantuan, dalam_tindakan, sedang_diselamatkan, bantauan_selesai, cancelled')
                ->change();
                
            // Add check constraint for status values (MySQL specific)
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE sos_requests 
                    ADD CONSTRAINT chk_status 
                    CHECK (status IN ('mohon_bantuan', 'dalam_tindakan', 'sedang_diselamatkan', 'bantauan_selesai', 'cancelled'))");
            }
            
            // Add index for better performance on status queries
            $table->index('status');
            $table->index('responded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sos_requests', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['status']);
            $table->dropIndex(['responded_by']);
            
            // Revert status column to original state if needed
            $table->string('status')->default('mohon_bantuan')->change();
        });
    }
};
