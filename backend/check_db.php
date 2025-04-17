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

    // Test connection
    $db->query("SELECT 1");
    echo json_encode(["message" => "Database connection successful"]);

    // Check if contacts table exists
    $stmt = $db->query("SHOW TABLES LIKE 'contacts'");
    if ($stmt->rowCount() > 0) {
        echo json_encode(["message" => "Contacts table exists"]);

        // Show table structure
        $structure = $db->query("DESCRIBE contacts");
        $columns = $structure->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["table_structure" => $columns]);
    } else {
        echo json_encode(["error" => "Contacts table does not exist"]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
