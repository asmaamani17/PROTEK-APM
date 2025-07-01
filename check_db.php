<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illwarem\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Now we can use the database
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Check database connection
    DB::connection()->getPdo();
    echo "Database connection successful!\n\n";
    
    // Check if the table exists
    if (!Schema::hasTable('rescue_cases')) {
        echo "The 'rescue_cases' table does not exist.\n";
        exit(1);
    }
    
    // Get the columns in the table
    $columns = Schema::getColumnListing('rescue_cases');
    
    echo "Rescue Cases Table Columns:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($columns as $column) {
        $type = DB::getSchemaBuilder()->getColumnType('rescue_cases', $column);
        echo sprintf("%-20s | %-20s\n", $column, $type);
    }
    
    // Get the current status values
    $statusValues = DB::table('rescue_cases')
        ->select('status')
        ->distinct()
        ->pluck('status')
        ->toArray();
    
    echo "\nCurrent Status Values in rescue_cases table:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($statusValues as $status) {
        echo "- $status\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
