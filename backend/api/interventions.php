<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/databases.php';
include_once '../models/Student.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = "SELECT 
                    s.id as student_id,
                    s.name as student_name,
                    s.dropout_risk,
                    s.intervention_status,
                    i.id as intervention_id,
                    i.type,
                    i.description,
                    i.start_date,
                    i.end_date,
                    i.status as intervention_status,
                    i.effectiveness
                 FROM students s
                 LEFT JOIN interventions i ON s.id = i.student_id
                 WHERE s.dropout_risk >= 70
                 ORDER BY s.dropout_risk DESC";

        $stmt = $db->prepare($query);
        $stmt->execute();

        $interventions = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $interventions[] = $row;
        }

        http_response_code(200);
        echo json_encode($interventions);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->student_id) &&
            !empty($data->type) &&
            !empty($data->description)
        ) {
            $query = "INSERT INTO interventions 
                     SET 
                        student_id = :student_id,
                        type = :type,
                        description = :description,
                        start_date = CURRENT_DATE,
                        status = 'In Progress'";

            $stmt = $db->prepare($query);

            $stmt->bindParam(":student_id", $data->student_id);
            $stmt->bindParam(":type", $data->type);
            $stmt->bindParam(":description", $data->description);

            if ($stmt->execute()) {
                $update_query = "UPDATE students 
                               SET intervention_status = 'In Progress' 
                               WHERE id = :student_id";

                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(":student_id", $data->student_id);
                $update_stmt->execute();

                http_response_code(201);
                echo json_encode(array("message" => "Intervention was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create intervention."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create intervention. Data is incomplete."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->intervention_id) &&
            !empty($data->status) &&
            !empty($data->effectiveness)
        ) {
            $query = "UPDATE interventions 
                     SET 
                        status = :status,
                        effectiveness = :effectiveness,
                        end_date = CASE 
                            WHEN :status = 'Completed' THEN CURRENT_DATE 
                            ELSE NULL 
                        END
                     WHERE id = :intervention_id";

            $stmt = $db->prepare($query);

            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":effectiveness", $data->effectiveness);
            $stmt->bindParam(":intervention_id", $data->intervention_id);

            if ($stmt->execute()) {
                if ($data->status == 'Completed') {
                    $check_query = "SELECT COUNT(*) as count 
                                  FROM interventions 
                                  WHERE student_id = (
                                      SELECT student_id 
                                      FROM interventions 
                                      WHERE id = :intervention_id
                                  ) AND status != 'Completed'";

                    $check_stmt = $db->prepare($check_query);
                    $check_stmt->bindParam(":intervention_id", $data->intervention_id);
                    $check_stmt->execute();
                    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result['count'] == 0) {
                        $update_query = "UPDATE students 
                                       SET intervention_status = 'Completed' 
                                       WHERE id = (
                                           SELECT student_id 
                                           FROM interventions 
                                           WHERE id = :intervention_id
                                       )";

                        $update_stmt = $db->prepare($update_query);
                        $update_stmt->bindParam(":intervention_id", $data->intervention_id);
                        $update_stmt->execute();
                    }
                }

                http_response_code(200);
                echo json_encode(array("message" => "Intervention was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update intervention."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update intervention. Data is incomplete."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
