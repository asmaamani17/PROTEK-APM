<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Load environment variables
$app->loadEnvironmentFrom('.env');

$kernel = $app->make(Illware\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    // Get database connection
    $db = $app->make('db');
    
    // Get rescue cases
    $cases = $db->table('rescue_cases')
        ->select('id', 'status', 'victim_name', 'rescuer_id', 'rescue_started_at', 'completed_at')
        ->get();
    
    echo "Found " . count($cases) . " rescue cases:\n\n";
    
    foreach ($cases as $case) {
        echo "ID: " . $case->id . "\n";
        echo "Status: " . $case->status . "\n";
        echo "Victim: " . ($case->victim_name ?? 'N/A') . "\n";
        echo "Rescuer ID: " . ($case->rescuer_id ?? 'N/A') . "\n";
        echo "Rescue Started At: " . ($case->rescue_started_at ?? 'N/A') . "\n";
        echo "Completed At: " . ($case->completed_at ?? 'N/A') . "\n";
        echo str_repeat("-", 40) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
