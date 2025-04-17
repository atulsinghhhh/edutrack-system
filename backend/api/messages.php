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

try {
    $database = new Database();
    $db = $database->getConnection();

    // Test database connection
    $db->query("SELECT 1");
    error_log("Database connection successful");

    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Received {$method} request");

    switch ($method) {
        case 'GET':
            // Get messages for a conversation
            if (!isset($_GET['conversation_id'])) {
                error_log("Missing conversation_id in GET request");
                http_response_code(400);
                echo json_encode(array("message" => "Conversation ID is required."));
                break;
            }

            $conversation_id = $_GET['conversation_id'];
            error_log("Fetching messages for conversation_id: " . $conversation_id);

            $query = "SELECT m.*, u.name as sender_name 
                     FROM messages m 
                     JOIN users u ON m.sender_id = u.id 
                     WHERE m.conversation_id = :conversation_id 
                     ORDER BY m.created_at ASC";

            $stmt = $db->prepare($query);
            $stmt->bindParam(":conversation_id", $conversation_id);
            $stmt->execute();

            $messages = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $messages[] = array(
                    "id" => (int)$row['id'],
                    "text" => $row['message'],
                    "sender_id" => (int)$row['sender_id'],
                    "sender_name" => $row['sender_name'],
                    "created_at" => $row['created_at']
                );
            }

            error_log("Found " . count($messages) . " messages for conversation " . $conversation_id);
            http_response_code(200);
            echo json_encode($messages);
            break;

        case 'POST':
            // Create a new message
            $data = json_decode(file_get_contents("php://input"));
            error_log("Received POST data: " . print_r($data, true));

            if (!isset($data->conversation_id) || !isset($data->sender_id) || !isset($data->message)) {
                error_log("Missing required fields in message data");
                http_response_code(400);
                echo json_encode(array(
                    "message" => "Unable to create message. Data is incomplete.",
                    "received" => $data
                ));
                break;
            }

            // First, verify the conversation exists and get the listener_id
            $verify_query = "SELECT c.listener_id, l.user_id 
                           FROM conversations c 
                           JOIN listeners l ON c.listener_id = l.id 
                           WHERE c.id = :conversation_id";
            $verify_stmt = $db->prepare($verify_query);
            $verify_stmt->bindParam(":conversation_id", $data->conversation_id);
            $verify_stmt->execute();
            $conversation = $verify_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$conversation) {
                error_log("Conversation not found: " . $data->conversation_id);
                http_response_code(404);
                echo json_encode(array("message" => "Conversation not found."));
                break;
            }

            // Insert the message
            $query = "INSERT INTO messages (conversation_id, sender_id, message) 
                     VALUES (:conversation_id, :sender_id, :message)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(":conversation_id", $data->conversation_id);
            $stmt->bindParam(":sender_id", $data->sender_id);
            $stmt->bindParam(":message", $data->message);

            if ($stmt->execute()) {
                error_log("Message inserted successfully");

                // Update conversation status to Active if it was Pending
                $update_query = "UPDATE conversations 
                               SET status = 'Active' 
                               WHERE id = :conversation_id AND status = 'Pending'";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(":conversation_id", $data->conversation_id);
                $update_stmt->execute();

                // If the sender is the user, create an auto-reply from the listener using Gemini API
                if ($data->sender_id != $conversation['user_id']) {
                    // Get the user's message
                    $user_message = $data->message;

                    // Prepare the prompt for Gemini
                    $prompt = "You are a supportive listener. The user said: \"$user_message\". Please provide a helpful and empathetic response.";

                    // Call Gemini API
                    $gemini_api_key = "AIzaSyC-P95_mC1blxA6NEU7WKlAdfj-2lMBxms"; // Replace with your actual API key
                    $gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_api_key;

                    $gemini_data = array(
                        "contents" => array(
                            array(
                                "parts" => array(
                                    array(
                                        "text" => $prompt
                                    )
                                )
                            )
                        )
                    );

                    $ch = curl_init($gemini_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($gemini_data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

                    $gemini_response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($http_code == 200) {
                        $response_data = json_decode($gemini_response, true);
                        if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
                            $auto_reply = $response_data['candidates'][0]['content']['parts'][0]['text'];
                        } else {
                            $auto_reply = "I'm here to listen and support you. How are you feeling today?";
                        }
                    } else {
                        error_log("Gemini API error: " . $gemini_response);
                        $auto_reply = "I'm here to listen and support you. How are you feeling today?";
                    }

                    // Insert the Gemini-generated response
                    $listener_message_query = "INSERT INTO messages (conversation_id, sender_id, message) 
                                            VALUES (:conversation_id, :listener_user_id, :message)";
                    $listener_stmt = $db->prepare($listener_message_query);
                    $listener_stmt->bindParam(":conversation_id", $data->conversation_id);
                    $listener_stmt->bindParam(":listener_user_id", $conversation['user_id']);
                    $listener_stmt->bindParam(":message", $auto_reply);
                    $listener_stmt->execute();
                    error_log("Gemini-generated response sent from listener with user_id: " . $conversation['user_id']);
                }

                http_response_code(201);
                echo json_encode(array(
                    "message" => "Message created successfully.",
                    "id" => $db->lastInsertId()
                ));
            } else {
                error_log("Error creating message: " . print_r($stmt->errorInfo(), true));
                http_response_code(503);
                echo json_encode(array(
                    "message" => "Unable to create message.",
                    "error" => $stmt->errorInfo()
                ));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
    }
} catch (PDOException $e) {
    error_log("Database error in messages.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Database error occurred.",
        "error" => $e->getMessage()
    ));
} catch (Exception $e) {
    error_log("Error in messages.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Unable to process request.",
        "error" => $e->getMessage()
    ));
}
