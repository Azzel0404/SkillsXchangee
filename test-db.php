<?php
// Simple database connection test
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '5432';
    $dbname = $_ENV['DB_DATABASE'] ?? 'test';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "PostgreSQL connection successful!\n";
    echo "Database: $dbname\n";
    echo "Host: $host:$port\n";
    
} catch (PDOException $e) {
    echo "PostgreSQL connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
