<?php
// Test PostgreSQL connection
require_once 'vendor/autoload.php';

// Database connection details from Render
$host = 'dpg-d31ck93uibrs73afcftg-a.singapore-postgres.render.com';
$port = '5432';
$dbname = 'skillsxchangee';
$username = 'skillsxchangee_user';
$password = 'vd0ea08TTtFCWmtbZidWJpVDFRIOGeoi';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ PostgreSQL connection successful!\n";
    echo "Database: $dbname\n";
    echo "Host: $host:$port\n";
    echo "Username: $username\n\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: $version\n\n";
    
    // List tables
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found. Run migrations first.\n";
    } else {
        echo "Tables found:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ PostgreSQL connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
