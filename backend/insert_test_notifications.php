<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/config/databases.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get a test user ID
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'admin@example.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["error" => "Test user not found"]);
        exit;
    }

    $user_id = $user['id'];

    // Insert test notifications
    $notifications = [
        [
            'title' => 'Welcome to DropoutStud',
            'message' => 'Thank you for joining our platform. We are here to support you.',
            'type' => 'success'
        ],
        [
            'title' => 'New Message',
            'message' => 'You have received a new message from your counselor.',
            'type' => 'info'
        ],
        [
            'title' => 'Important Update',
            'message' => 'Your intervention plan has been updated. Please review the changes.',
            'type' => 'warning'
        ]
    ];

    $query = "INSERT INTO notifications (user_id, title, message, type) 
              VALUES (:user_id, :title, :message, :type)";

    $stmt = $db->prepare($query);
    $inserted = 0;

    foreach ($notifications as $notification) {
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":title", $notification['title']);
        $stmt->bindParam(":message", $notification['message']);
        $stmt->bindParam(":type", $notification['type']);

        if ($stmt->execute()) {
            $inserted++;
        }
    }

    echo json_encode([
        "message" => "Successfully inserted $inserted test notifications",
        "user_id" => $user_id
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
