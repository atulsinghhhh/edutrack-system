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
        header("Access-Control-Allow-Methods: POST, OPTIONS");

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
        "error" => "Database configuration file not found",
        "path" => $db_config_path
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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get POST data
        $raw_data = file_get_contents("php://input");
        error_log("Raw POST data: " . $raw_data);

        $data = json_decode($raw_data);
        error_log("Decoded POST data: " . print_r($data, true));

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            http_response_code(400);
            echo json_encode(array(
                "message" => "Invalid JSON data",
                "error" => json_last_error_msg()
            ));
            exit;
        }

        // Validate required fields
        if (!isset($data->name) || !isset($data->email) || !isset($data->subject) || !isset($data->message)) {
            error_log("Missing required fields in contact form data");
            http_response_code(400);
            echo json_encode(array(
                "message" => "Unable to submit contact form. All fields are required.",
                "received" => $data
            ));
            exit;
        }

        // Sanitize input
        $name = htmlspecialchars(strip_tags($data->name));
        $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
        $subject = htmlspecialchars(strip_tags($data->subject));
        $message = htmlspecialchars(strip_tags($data->message));

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email format: " . $email);
            http_response_code(400);
            echo json_encode(array("message" => "Invalid email format."));
            exit;
        }

        // Check if contacts table exists
        $table_check = $db->query("SHOW TABLES LIKE 'contacts'");
        if ($table_check->rowCount() == 0) {
            error_log("Contacts table does not exist");
            http_response_code(500);
            echo json_encode(array(
                "message" => "Database configuration error",
                "error" => "Contacts table does not exist"
            ));
            exit;
        }

        // Insert contact form submission
        $query = "INSERT INTO contacts (name, email, subject, message) 
                 VALUES (:name, :email, :subject, :message)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":message", $message);

        if ($stmt->execute()) {
            error_log("Contact form submitted successfully");
            http_response_code(201);
            echo json_encode(array(
                "message" => "Contact form submitted successfully.",
                "id" => $db->lastInsertId()
            ));
        } else {
            $error_info = $stmt->errorInfo();
            error_log("Error submitting contact form: " . print_r($error_info, true));
            http_response_code(503);
            echo json_encode(array(
                "message" => "Unable to submit contact form.",
                "error" => $error_info
            ));
        }
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
    }
} catch (PDOException $e) {
    error_log("Database error in contact.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Database error occurred.",
        "error" => $e->getMessage(),
        "code" => $e->getCode()
    ));
} catch (Exception $e) {
    error_log("Error in contact.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Unable to process request.",
        "error" => $e->getMessage()
    ));
}
