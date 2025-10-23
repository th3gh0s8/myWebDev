<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL
    )";
    $db->exec($sql);

    echo "Database and table created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}