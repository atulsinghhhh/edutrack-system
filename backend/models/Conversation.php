<?php
// Include database configuration
$db_config_path = __DIR__ . '/../config/databases.php';
if (!file_exists($db_config_path)) {
    error_log("Database configuration file not found at: " . $db_config_path);
    throw new Exception("Database configuration file not found");
}

require_once $db_config_path;

class Conversation
{
    private $conn;
    private $table_name = "conversations";

    public $id;
    public $user_id;
    public $listener_id;
    public $problem;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, listener_id, problem, status)
                     VALUES
                     (:user_id, :listener_id, :problem, :status)";

            $stmt = $this->conn->prepare($query);

            $this->status = "Pending";

            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":listener_id", $this->listener_id);
            $stmt->bindParam(":problem", $this->problem);
            $stmt->bindParam(":status", $this->status);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            error_log("Failed to execute conversation creation query");
            return false;
        } catch (Exception $e) {
            error_log("Error in Conversation::create(): " . $e->getMessage());
            return false;
        }
    }

    public function getConversation($id)
    {
        $query = "SELECT c.*, u.name as user_name, l.name as listener_name
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.id
                 JOIN listeners l ON c.listener_id = l.id
                 WHERE c.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt;
    }

    public function getUserConversations($user_id)
    {
        $query = "SELECT c.*, u.name as user_name, l.name as listener_name
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.id
                 JOIN listeners l ON c.listener_id = l.id
                 WHERE c.user_id = :user_id
                 ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($id, $status)
    {
        $query = "UPDATE " . $this->table_name . "
                 SET status = :status
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
