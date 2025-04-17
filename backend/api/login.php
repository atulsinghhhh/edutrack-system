<?php
header('Content-Type: application/json');
require_once '../config/cors.php';
require_once '../config/databases.php';
require_once '../models/User.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$rawData = file_get_contents('php://input');
error_log("Login attempt with data: " . $rawData);

$data = json_decode($rawData, true);

if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

try {
    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);
    $user->email = $email;

    $stmt = $user->login();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $row['password'])) {
            // Generate a new token
            $token = bin2hex(random_bytes(32));

            // Update the user's token
            $updateStmt = $db->prepare("UPDATE users SET token = :token WHERE id = :id");
            $updateStmt->execute(['token' => $token, 'id' => $row['id']]);

            // Remove sensitive data before sending response
            unset($row['password']);
            unset($row['token']);

            echo json_encode([
                'token' => $token,
                'user' => $row
            ]);
        } else {
            error_log("Password verification failed for user: " . $email);
            http_response_code(401);
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        error_log("No user found with email: " . $email);
        http_response_code(401);
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}
