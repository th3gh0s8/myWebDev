<?php
// database.php - Database configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your DB username
define('DB_PASS', '');      // Change this to your DB password
define('DB_NAME', 'web_form'); // Change this to your DB name

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the registrations table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20),
        company_name VARCHAR(100),
        company_address TEXT,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createTable);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>