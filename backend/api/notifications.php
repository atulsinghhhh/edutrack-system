<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/databases.php';
include_once '../models/Notification.php';

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $stmt = $notification->getUserNotifications($user_id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                $notifications_arr = array();
                $notifications_arr["records"] = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $notification_item = array(
                        "id" => $id,
                        "title" => $title,
                        "message" => $message,
                        "type" => $type,
                        "created_at" => $created_at,
                        "is_read" => $is_read
                    );
                    array_push($notifications_arr["records"], $notification_item);
                }

                http_response_code(200);
                echo json_encode($notifications_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No notifications found."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User ID is required."));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (isset($data->notification_id)) {
            if ($notification->markAsRead($data->notification_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Notification marked as read."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to mark notification as read."));
            }
        } else {
            $notification->user_id = $data->user_id;
            $notification->title = $data->title;
            $notification->message = $data->message;
            $notification->type = $data->type;

            if ($notification->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Notification was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create notification."));
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
