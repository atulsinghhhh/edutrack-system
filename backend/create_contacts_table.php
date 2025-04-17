<?php
header("Content-Type: application/json; charset=UTF-8");

// Include database configuration
$db_config_path = __DIR__ . '/config/databases.php';
if (!file_exists($db_config_path)) {
    echo json_encode([
        "error" => "Database configuration file not found",
        "path" => $db_config_path
    ]);
    exit;
}

include_once $db_config_path;

try {
    $database = new Database();
    $db = $database->getConnection();

    // Create contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('New', 'Read', 'Replied') DEFAULT 'New',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_email (email)
    )";

    $db->exec($sql);
    echo json_encode(["message" => "Contacts table created successfully"]);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
