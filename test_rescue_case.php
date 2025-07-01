<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Load environment variables
$app->loadEnvironmentFrom('.env');

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Get database connection
    $db = $app->make('db');
    
    // Get a rescue case
    $case = DB::table('rescue_cases')->first();
    
    if ($case) {
        echo "Found rescue case ID: " . $case->id . "\n";
        echo "Current status: " . $case->status . "\n";
        echo "Victim name: " . ($case->victim_name ?? 'N/A') . "\n";
        echo "Rescuer ID: " . ($case->rescuer_id ?? 'N/A') . "\n";
    } else {
        echo "No rescue cases found in the database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
