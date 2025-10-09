<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Pasindu@12236'); // Empty password
define('DB_NAME', 'washio');

try {
    // Create MySQLi connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        // If database doesn't exist, create it
        $temp_conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($temp_conn->connect_error) {
            throw new Exception("Connection failed: " . $temp_conn->connect_error);
        }

        $sql = "CREATE DATABASE IF NOT EXISTS web_form";
        if ($temp_conn->query($sql) === TRUE) {
            $temp_conn->select_db('web_form');
            $conn = $temp_conn;
        } else {
            throw new Exception("Error creating database: " . $temp_conn->error);
        }
    }

    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error loading character set utf8mb4: " . $conn->error);
    }

    // Create the xuser table if it doesn't exist
    $createXUserTable = "CREATE TABLE IF NOT EXISTS xuser (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        mobile VARCHAR(20),
        company_name VARCHAR(255),
        company_address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!$conn->query($createXUserTable)) {
        throw new Exception("Error creating xuser table: " . $conn->error);
    }

    // Also create the registrations table for compatibility
    $createRegistrationsTable = "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20),
        company_name VARCHAR(100),
        company_address TEXT,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($createRegistrationsTable)) {
        throw new Exception("Error creating registrations table: " . $conn->error);
    }

} catch (Exception $e) {
    error_log("Database error in db.php: " . $e->getMessage());
    // For development, show the error, but in production, show a generic message
    $isDev = true; // Set to false in production
    if ($isDev) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please check the error logs.");
    }
}
?>
