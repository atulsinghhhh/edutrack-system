<?php
header('Content-Type: application/json');
require_once '../config/cors.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);


$rawData = file_get_contents('php://input');
error_log("Registration attempt with data: " . $rawData);

$data = json_decode($rawData, true);

if (!isset($data['name'], $data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and password are required']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}


if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters long']);
    exit;
}

$host = 'localhost';
$dbname = 'dropoutstud';
$username = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        error_log("Registration failed: Email already exists for user ID: " . $existingUser['id']);
        http_response_code(400);
        echo json_encode([
            'error' => 'Email already exists',
            'details' => [
                'message' => 'This email is already registered',
                'suggestion' => 'Please use a different email or try to log in instead'
            ]
        ]);
        exit;
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    error_log("Password hashed successfully");

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword
    ]);


    $userId = $pdo->lastInsertId();
    error_log("User registered successfully with ID: " . $userId);

    echo json_encode([
        'message' => 'Registration successful',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email
        ]
    ]);
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
