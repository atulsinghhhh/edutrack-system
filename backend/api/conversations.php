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
        header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

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
include_once '../models/Conversation.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Test database connection
    $db->query("SELECT 1");
    error_log("Database connection successful");

    $conversation = new Conversation($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conversation->getConversation($_GET['id']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    http_response_code(200);
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Conversation not found."));
                }
            } else if (isset($_GET['user_id'])) {
                $stmt = $conversation->getUserConversations($_GET['user_id']);
                $conversations = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $conversations[] = $row;
                }

                http_response_code(200);
                echo json_encode($conversations);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to get conversations. User ID is required."));
            }
            break;

        case 'POST':
            try {
                $data = json_decode(file_get_contents("php://input"));
                error_log("Received conversation data: " . print_r($data, true));

                if (!isset($data->user_id) || !isset($data->listener_id) || !isset($data->problem)) {
                    error_log("Missing required fields in conversation data");
                    http_response_code(400);
                    echo json_encode(array(
                        "message" => "Unable to create conversation. Data is incomplete.",
                        "received" => $data
                    ));
                    break;
                }

                $conversation->user_id = $data->user_id;
                $conversation->listener_id = $data->listener_id;
                $conversation->problem = $data->problem;

                if ($conversation->create()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Conversation created successfully.",
                        "conversation_id" => $conversation->id
                    ));
                } else {
                    error_log("Failed to create conversation in database");
                    http_response_code(503);
                    echo json_encode(array(
                        "message" => "Unable to create conversation. Please try again later."
                    ));
                }
            } catch (Exception $e) {
                error_log("Error in conversations.php POST: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(array(
                    "message" => "An error occurred while creating the conversation.",
                    "error" => $e->getMessage()
                ));
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));

            if (isset($data->id) && isset($data->status)) {
                if ($conversation->updateStatus($data->id, $data->status)) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Conversation status updated."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to update conversation status."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to update conversation. Data is incomplete."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
    }
} catch (PDOException $e) {
    error_log("Database error in conversations.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Database error occurred.",
        "error" => $e->getMessage()
    ));
} catch (Exception $e) {
    error_log("Error in conversations.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "An error occurred while processing the request.",
        "error" => $e->getMessage()
    ));
}
