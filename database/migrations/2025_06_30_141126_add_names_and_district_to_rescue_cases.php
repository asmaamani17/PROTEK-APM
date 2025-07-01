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
            $table->string('victim_name')->nullable()->after('victim_id');
            $table->string('rescuer_name')->nullable()->after('rescuer_id');
            $table->string('district')->nullable()->after('lng');
        });

        // Update existing records with related user data
        if (Schema::hasTable('rescue_cases') && Schema::hasTable('users')) {
            $cases = DB::table('rescue_cases')->get();
            
            foreach ($cases as $case) {
                $updates = [];
                
                // Get victim name
                $victim = DB::table('users')->where('id', $case->victim_id)->first();
                if ($victim) {
                    $updates['victim_name'] = $victim->name;
                    $updates['district'] = $victim->daerah; // Assuming district is stored in 'daerah' column
                }
                
                // Get rescuer name if exists
                if ($case->rescuer_id) {
                    $rescuer = DB::table('users')->where('id', $case->rescuer_id)->first();
                    if ($rescuer) {
                        $updates['rescuer_name'] = $rescuer->name;
                    }
                }
                
                if (!empty($updates)) {
                    DB::table('rescue_cases')
                        ->where('id', $case->id)
                        ->update($updates);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rescue_cases', function (Blueprint $table) {
            $table->dropColumn(['victim_name', 'rescuer_name', 'district']);
        });
    }
};
