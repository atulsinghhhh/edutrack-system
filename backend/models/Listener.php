<?php
// Include database configuration
$db_config_path = __DIR__ . '/../config/databases.php';
if (!file_exists($db_config_path)) {
    error_log("Database configuration file not found at: " . $db_config_path);
    throw new Exception("Database configuration file not found");
}

require_once $db_config_path;

class Listener
{
    private $conn;
    private $table_name = "listeners";

    public $id;
    public $user_id;
    public $name;
    public $photo;
    public $specialization;
    public $is_available;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAvailableListeners()
    {
        // First, let's check all listeners without any conditions
        $check_query = "SELECT l.*, u.name as user_name, u.status as user_status 
                       FROM " . $this->table_name . " l
                       JOIN users u ON l.user_id = u.id";

        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->execute();
        $all_listeners = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("All listeners in database: " . print_r($all_listeners, true));

        // Now get available listeners
        $query = "SELECT l.id, l.photo, l.specialization, l.is_available, u.name 
                 FROM " . $this->table_name . " l
                 JOIN users u ON l.user_id = u.id
                 WHERE u.status = 'Active'
                 ORDER BY l.id ASC";

        error_log("SQL Query for available listeners: " . $query);

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // Return the results instead of the statement
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListenerById($id)
    {
        $query = "SELECT l.*, u.name AS listener_name 
                 FROM " . $this->table_name . " l
                 JOIN users u ON l.user_id = u.id
                 WHERE l.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt;
    }

    public function updateAvailability($id, $is_available)
    {
        $query = "UPDATE " . $this->table_name . "
                 SET is_available = :is_available
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":is_available", $is_available);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
