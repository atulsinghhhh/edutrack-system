<?php
// Use __DIR__ for reliable file path resolution
require_once __DIR__ . '/config/databases.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Create users table
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user', 'listener') NOT NULL DEFAULT 'user',
        status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
        token VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $db->exec($query);

    // Create notifications table
    $query = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('success', 'error', 'info', 'warning') NOT NULL DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    $db->exec($query);

    // Create indexes for notifications
    $db->exec("CREATE INDEX IF NOT EXISTS idx_notification_user ON notifications(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_notification_read ON notifications(is_read)");

    // Create listeners table
    $query = "CREATE TABLE IF NOT EXISTS listeners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        photo VARCHAR(255),
        specialization VARCHAR(100) NOT NULL,
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    $db->exec($query);

    // Check if test listener exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => 'test.listener@example.com']);

    if ($stmt->rowCount() == 0) {
        // Insert test listener user
        $query = "INSERT INTO users (name, email, password, role, status) 
                 VALUES ('Test Listener', 'test.listener@example.com', :password, 'listener', 'Active')";
        $stmt = $db->prepare($query);
        $password_hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->execute(['password' => $password_hash]);

        $listener_user_id = $db->lastInsertId();

        // Insert listener data
        $query = "INSERT INTO listeners (user_id, name, specialization, is_available) 
                 VALUES (:user_id, 'Test Listener', 'Mental Health Support', TRUE)";
        $stmt = $db->prepare($query);
        $stmt->execute(['user_id' => $listener_user_id]);

        echo "Test listener created successfully!";
    } else {
        echo "Test listener already exists!";
    }

    // Verify the data
    $stmt = $db->query("SELECT COUNT(*) as count FROM listeners");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo " Total listeners in database: " . $count;

    echo json_encode(["message" => "Database tables created successfully"]);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
