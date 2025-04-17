<?php
class Notification
{
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $user_id;
    public $title;
    public $message;
    public $type;
    public $created_at;
    public $is_read;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getUserNotifications($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function markAsRead($notification_id)
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_read = 1 
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $notification_id);

        return $stmt->execute();
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, title, message, type, created_at, is_read)
                VALUES
                (:user_id, :title, :message, :type, NOW(), 0)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":type", $this->type);

        return $stmt->execute();
    }
}
