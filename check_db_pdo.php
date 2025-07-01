<?php

// Load environment variables from .env file
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, '"\'');
        
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Database configuration
$host = getenv('DB_HOST', true) ?: '127.0.0.1';
$port = getenv('DB_PORT', true) ?: '3306';
$database = getenv('DB_DATABASE', true) ?: 'forge';
$username = getenv('DB_USERNAME', true) ?: 'forge';
$password = getenv('DB_PASSWORD', true) ?: '';

// Create connection
$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
$options = [
    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new \PDO($dsn, $username, $password, $options);
    echo "Connected to database successfully!\n\n";
    
    // Check if rescue_cases table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'rescue_cases'");
    if ($stmt->rowCount() === 0) {
        die("Error: The 'rescue_cases' table does not exist.\n");
    }
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE rescue_cases");
    $columns = $stmt->fetchAll();
    
    echo "Rescue Cases Table Structure:\n";
    echo str_repeat("-", 100) . "\n";
    echo sprintf("%-20s | %-20s | %-10s | %-10s | %-20s\n", 
        'Field', 'Type', 'Null', 'Key', 'Default');
    echo str_repeat("-", 100) . "\n";
    
    foreach ($columns as $column) {
        echo sprintf("%-20s | %-20s | %-10s | %-10s | %-20s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'] ?: '',
            $column['Default'] ?? 'NULL');
    }
    
    // Get current status values
    $stmt = $pdo->query("SELECT DISTINCT status FROM rescue_cases");
    $statusValues = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    echo "\nCurrent Status Values in rescue_cases table:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($statusValues as $status) {
        echo "- $status\n";
    }
    
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}
