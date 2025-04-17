<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/databases.php';
include_once '../models/Student.php';

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['high_risk']) && $_GET['high_risk'] == 'true') {
            $stmt = $student->getHighRiskStudents();
        } else {
            $stmt = $student->getAll();
        }

        $num = $stmt->rowCount();

        if ($num > 0) {
            $students_arr = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $student_item = array(
                    "id" => $id,
                    "name" => $name,
                    "age" => $age,
                    "gender" => $gender,
                    "academic_performance" => $academic_performance,
                    "attendance" => $attendance,
                    "socio_economic_status" => $socio_economic_status,
                    "family_support" => $family_support,
                    "dropout_risk" => $dropout_risk,
                    "intervention_status" => $intervention_status
                );

                array_push($students_arr, $student_item);
            }

            http_response_code(200);
            echo json_encode($students_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No students found."));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->name) &&
            !empty($data->age) &&
            !empty($data->gender) &&
            !empty($data->academic_performance) &&
            !empty($data->attendance) &&
            !empty($data->socio_economic_status) &&
            !empty($data->family_support)
        ) {
            $student->name = $data->name;
            $student->age = $data->age;
            $student->gender = $data->gender;
            $student->academic_performance = $data->academic_performance;
            $student->attendance = $data->attendance;
            $student->socio_economic_status = $data->socio_economic_status;
            $student->family_support = $data->family_support;

            if ($student->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Student was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create student."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create student. Data is incomplete."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id) && !empty($data->intervention_status)) {
            $student->id = $data->id;
            $student->intervention_status = $data->intervention_status;

            if ($student->updateInterventionStatus()) {
                http_response_code(200);
                echo json_encode(array("message" => "Student intervention status was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update student intervention status."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update student. Data is incomplete."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
