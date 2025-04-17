<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/databases.php';
include_once '../models/Student.php';

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);


$query = "SELECT 
            COUNT(*) as total_students,
            SUM(CASE WHEN dropout_risk >= 70 THEN 1 ELSE 0 END) as high_risk_students,
            AVG(dropout_risk) as average_risk,
            SUM(CASE WHEN intervention_status = 'Completed' THEN 1 ELSE 0 END) as completed_interventions,
            SUM(CASE WHEN intervention_status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_interventions
          FROM students";

$stmt = $db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);


$query = "SELECT 
            CASE 
                WHEN dropout_risk < 30 THEN 'Low'
                WHEN dropout_risk < 70 THEN 'Medium'
                ELSE 'High'
            END as risk_level,
            COUNT(*) as count
          FROM students
          GROUP BY risk_level";

$stmt = $db->prepare($query);
$stmt->execute();
$risk_distribution = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $risk_distribution[] = $row;
}


$query = "SELECT id, name, dropout_risk, intervention_status 
          FROM students 
          WHERE dropout_risk >= 70 
          ORDER BY dropout_risk DESC 
          LIMIT 5";

$stmt = $db->prepare($query);
$stmt->execute();
$recent_high_risk = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_high_risk[] = $row;
}


$query = "SELECT intervention_status, COUNT(*) as count 
          FROM students 
          GROUP BY intervention_status";

$stmt = $db->prepare($query);
$stmt->execute();
$intervention_distribution = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $intervention_distribution[] = $row;
}

$query = "SELECT 
            COALESCE(gender, 'Unknown') as gender,
            COUNT(*) as total_students,
            SUM(CASE WHEN dropout_risk >= 70 THEN 1 ELSE 0 END) as dropout_count,
            CASE 
                WHEN COUNT(*) > 0 THEN (SUM(CASE WHEN dropout_risk >= 70 THEN 1 ELSE 0 END) / COUNT(*)) * 100 
                ELSE 0 
            END as dropout_rate
          FROM students
          GROUP BY gender";

$stmt = $db->prepare($query);
$stmt->execute();
$gender_stats = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gender_stats[] = $row;
}

if (empty($gender_stats)) {
    $gender_stats = [
        ['gender' => 'Male', 'total_students' => 0, 'dropout_count' => 0, 'dropout_rate' => 0],
        ['gender' => 'Female', 'total_students' => 0, 'dropout_count' => 0, 'dropout_rate' => 0]
    ];
}

$query = "SELECT 
            COALESCE(location_type, 'Unknown') as location_type,
            COUNT(*) as total_students,
            SUM(CASE WHEN dropout_risk >= 70 THEN 1 ELSE 0 END) as dropout_count,
            CASE 
                WHEN COUNT(*) > 0 THEN (SUM(CASE WHEN dropout_risk >= 70 THEN 1 ELSE 0 END) / COUNT(*)) * 100 
                ELSE 0 
            END as dropout_rate
          FROM students
          GROUP BY location_type";

$stmt = $db->prepare($query);
$stmt->execute();
$location_stats = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $location_stats[] = $row;
}

if (empty($location_stats)) {
    $location_stats = [
        ['location_type' => 'Urban', 'total_students' => 0, 'dropout_count' => 0, 'dropout_rate' => 0],
        ['location_type' => 'Rural', 'total_students' => 0, 'dropout_count' => 0, 'dropout_rate' => 0]
    ];
}


$query = "SELECT 
            COALESCE(YEAR(date_flagged), YEAR(CURDATE())) as year,
            COUNT(*) as predicted_cases,
            CASE 
                WHEN COUNT(*) > 0 THEN AVG(dropout_risk)
                ELSE 0 
            END as average_risk
          FROM students
          WHERE date_flagged IS NOT NULL
          GROUP BY YEAR(date_flagged)
          ORDER BY year";

$stmt = $db->prepare($query);
$stmt->execute();
$annual_predictions = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $annual_predictions[] = $row;
}
// If no prediction data exists, provide default values for current and next year
if (empty($annual_predictions)) {
    $current_year = date('Y');
    $annual_predictions = [
        ['year' => $current_year, 'predicted_cases' => 0, 'average_risk' => 0],
        ['year' => $current_year + 1, 'predicted_cases' => 0, 'average_risk' => 0]
    ];
}

$query = "SELECT 
            id,
            name as student_name,
            school,
            risk_factors,
            date_flagged
          FROM students
          WHERE date_flagged >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          ORDER BY date_flagged DESC
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute();
$new_cases = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $new_cases[] = $row;
}


$query = "SELECT 
            s.school_id,
            s.school_name,
            s.current_rate,
            s.previous_rate,
            CASE 
                WHEN s.current_rate > s.previous_rate THEN 'increasing'
                WHEN s.current_rate < s.previous_rate THEN 'decreasing'
                ELSE 'stable'
            END as trend
          FROM (
              SELECT 
                  school_id,
                  school_name,
                  AVG(CASE WHEN date_flagged >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN dropout_risk ELSE 0 END) as current_rate,
                  AVG(CASE WHEN date_flagged < DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN dropout_risk ELSE 0 END) as previous_rate
              FROM students
              GROUP BY school_id, school_name
          ) s
          WHERE s.current_rate > s.previous_rate
          ORDER BY (s.current_rate - s.previous_rate) DESC
          LIMIT 5";

$stmt = $db->prepare($query);
$stmt->execute();
$school_trends = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $school_trends[] = $row;
}

$query = "SELECT 
            s.id,
            s.name as student_name,
            s.school,
            s.dropout_risk as risk_level,
            DATEDIFF(CURDATE(), s.date_flagged) as days_since_flagged,
            GROUP_CONCAT(i.type) as required_actions
          FROM students s
          LEFT JOIN interventions i ON s.id = i.student_id
          WHERE s.dropout_risk >= 80
          AND (s.intervention_status = 'Pending' OR s.intervention_status = 'In Progress')
          GROUP BY s.id, s.name, s.school, s.dropout_risk, s.date_flagged
          ORDER BY s.dropout_risk DESC, days_since_flagged DESC
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute();
$urgent_interventions = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['required_actions'] = explode(',', $row['required_actions']);
    $urgent_interventions[] = $row;
}

$dashboard_data = array(
    "statistics" => $stats,
    "risk_distribution" => $risk_distribution,
    "recent_high_risk" => $recent_high_risk,
    "intervention_distribution" => $intervention_distribution,
    "gender_stats" => $gender_stats,
    "location_stats" => $location_stats,
    "annual_predictions" => $annual_predictions,
    "new_cases" => $new_cases,
    "school_trends" => $school_trends,
    "urgent_interventions" => $urgent_interventions
);

http_response_code(200);
echo json_encode($dashboard_data);
