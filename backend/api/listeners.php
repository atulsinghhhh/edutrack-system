<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

// Include database configuration
$db_config_path = __DIR__ . '/../config/databases.php';
if (!file_exists($db_config_path)) {
    error_log("Database configuration file not found at: " . $db_config_path);
    http_response_code(500);
    echo json_encode(array(
        "message" => "Server configuration error",
        "error" => "Database configuration file not found"
    ));
    exit;
}

include_once $db_config_path;
include_once '../models/Listener.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Test database connection
    $db->query("SELECT 1");
    error_log("Database connection successful");

    $listener = new Listener($db);
    $listeners = $listener->getAvailableListeners();

    error_log("Found " . count($listeners) . " available listeners: " . print_r($listeners, true));

    http_response_code(200);
    echo json_encode($listeners);
} catch (PDOException $e) {
    error_log("Database error in listeners.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Database error occurred.",
        "error" => $e->getMessage()
    ));
} catch (Exception $e) {
    error_log("Error in listeners.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Unable to fetch listeners.",
        "error" => $e->getMessage()
    ));
}
