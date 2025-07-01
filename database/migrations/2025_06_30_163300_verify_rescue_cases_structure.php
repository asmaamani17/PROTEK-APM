<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyRescueCasesStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration will output the structure of the rescue_cases table
        if (Schema::hasTable('rescue_cases')) {
            $columns = DB::select('SHOW COLUMNS FROM rescue_cases');
            
            echo "\nRescue Cases Table Structure:\n";
            echo str_repeat("-", 100) . "\n";
            echo sprintf("%-20s | %-20s | %-10s | %-10s | %-20s\n", 
                'Field', 'Type', 'Null', 'Key', 'Default');
            echo str_repeat("-", 100) . "\n";
            
            foreach ($columns as $column) {
                echo sprintf("%-20s | %-20s | %-10s | %-10s | %-20s\n",
                    $column->Field,
                    $column->Type,
                    $column->Null,
                    $column->Key ?: '',
                    $column->Default ?? 'NULL');
            }
            
            echo "\n";
        } else {
            echo "Error: rescue_cases table does not exist.\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a read-only migration, nothing to rollback
    }
}
