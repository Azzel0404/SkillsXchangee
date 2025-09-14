<?php
/**
 * MySQL Connection Test Script
 * Run this script to test your MySQL database connection
 */

// Database configuration (update these with your Railway MySQL details)
$host = 'shuttle.proxy.rlwy.net';  // From your Railway public connection
$port = '14460';
$database = 'railway';
$username = 'root';
$password = 'lncQUGzAqadIdRckNFrZLgrIlgpKJPOx';  // From your Railway variables

echo "Testing MySQL Connection...\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "MySQL Version: " . $version['version'] . "\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch();
        echo "Total users: " . $count['count'] . "\n";
        
        // Check for test user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $testUser = $stmt->fetch();
        
        if ($testUser) {
            echo "✅ Test user found:\n";
            echo "  - ID: " . $testUser['id'] . "\n";
            echo "  - Name: " . $testUser['name'] . "\n";
            echo "  - Email: " . $testUser['email'] . "\n";
            echo "  - Created: " . $testUser['created_at'] . "\n";
        } else {
            echo "❌ Test user not found (run: php artisan db:seed)\n";
        }
    } else {
        echo "❌ Users table does not exist (run: php artisan migrate)\n";
    }
    
    echo "\n🎉 Database connection test completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check if your Railway MySQL service is running\n";
    echo "2. Verify the connection details in Railway dashboard\n";
    echo "3. Make sure you're using the correct public network connection\n";
    echo "4. Check if your IP is whitelisted (if required)\n";
}
