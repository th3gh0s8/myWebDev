<?php
ob_start(); // Start output buffering at the very beginning

// Enable full error reporting (to be logged)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Keep this 1 for debugging, change to 0 in production
ini_set('log_errors', 1);

$conn = null; // Initialize $conn

try {
    $servername = "localhost";

    $username = "root";
    $password = "Pasindu@12236";
    $dbname = "washio";
    $port = 3307;

    // $username = "pw_washio";
    // $password = "washio-2025-09-27";
    // $dbname = "pw_washio_db";
    // $port = 3306;

    $db_connection_error_message = 'Connection not attempted or failed before error property set.';

    $link = mysqli_init();
    if (!$link) {
        $db_connection_error_message = 'mysqli_init failed';
        // error_log already happens if we throw, but good for clarity if needed
        throw new Exception($db_connection_error_message);
    }

    if (!mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
        error_log("Database Connection warning in db.php: Failed to set MYSQLI_OPT_CONNECT_TIMEOUT");
        // Not throwing an exception here as it's a warning, connection might still succeed.
    }

    if (@mysqli_real_connect($link, $servername, $username, $password, $dbname, $port)) {
        $conn = $link;
    } else {
        $db_connection_error_message = mysqli_connect_error();
        // error_log already happens if we throw
        throw new Exception("DB Connection Error: " . $db_connection_error_message);
    }

    if (!$conn->set_charset("utf8mb4")) {
        // Log this error, but don't necessarily terminate if connection itself was fine.
        error_log("Error loading character set utf8mb4 in db.php: " . $conn->error);
        // Depending on requirements, you might throw an Exception here too.
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
        error_log("Error creating xuser table: " . $conn->error);
    }

    // If script reaches here, $conn is presumably set up successfully.
    // db.php's job is to provide $conn, not to output success JSON itself.

} catch (Exception $e) {
    error_log("Exception in db.php: " . $e->getMessage() . " (Details: Server=$servername, User=$username, DB=$dbname, Port=$port)");

    // Ensure no prior output interferes with JSON
    if (ob_get_level() > 0) {
        ob_end_clean(); // Clean any previous output buffer
    }

    // Set JSON header if not already sent
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'PHP DB Connection Critical: ' . $e->getMessage(),
        'source' => 'db.php'
    ]);
    exit; // Stop script execution after sending JSON error
}
// No ob_end_flush() here, as db.php is included.
// The script that includes it (e.g., request_otp.php) will handle the final output.
?>
