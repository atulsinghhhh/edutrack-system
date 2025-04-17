<?php
require_once __DIR__ . '/../config/Databases.php';

class Message
{
    private $conn;
    private $table_name = "messages";

    public $id;
    public $conversation_id;
    public $sender_id;
    public $message;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                 (conversation_id, sender_id, message)
                 VALUES
                 (:conversation_id, :sender_id, :message)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":conversation_id", $this->conversation_id);
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":message", $this->message);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getConversationMessages($conversation_id)
    {
        $query = "SELECT m.*, u.name as sender_name
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.sender_id = u.id
                 WHERE m.conversation_id = :conversation_id
                 ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":conversation_id", $conversation_id);
        $stmt->execute();

        return $stmt;
    }
}
