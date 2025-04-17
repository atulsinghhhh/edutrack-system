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


$query = "SELECT 
            AVG(academic_performance) as avg_academic_performance,
            AVG(attendance) as avg_attendance,
            AVG(socio_economic_status) as avg_socio_economic,
            AVG(family_support) as avg_family_support,
            AVG(dropout_risk) as avg_dropout_risk
          FROM students";

$stmt = $db->prepare($query);
$stmt->execute();
$averages = $stmt->fetch(PDO::FETCH_ASSOC);


$query = "SELECT 
            socio_economic_status,
            COUNT(*) as count,
            AVG(dropout_risk) as avg_risk
          FROM students
          GROUP BY socio_economic_status
          ORDER BY socio_economic_status";

$stmt = $db->prepare($query);
$stmt->execute();
$socio_economic_distribution = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $socio_economic_distribution[] = $row;
}

$query = "SELECT 
            family_support,
            COUNT(*) as count,
            AVG(dropout_risk) as avg_risk
          FROM students
          GROUP BY family_support
          ORDER BY family_support";

$stmt = $db->prepare($query);
$stmt->execute();
$family_support_distribution = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $family_support_distribution[] = $row;
}

$query = "SELECT 
            CASE 
                WHEN academic_performance >= 80 THEN 'Excellent'
                WHEN academic_performance >= 60 THEN 'Good'
                WHEN academic_performance >= 40 THEN 'Fair'
                ELSE 'Poor'
            END as performance_level,
            COUNT(*) as count,
            AVG(dropout_risk) as avg_risk
          FROM students
          GROUP BY performance_level
          ORDER BY 
            CASE performance_level
                WHEN 'Excellent' THEN 1
                WHEN 'Good' THEN 2
                WHEN 'Fair' THEN 3
                ELSE 4
            END";

$stmt = $db->prepare($query);
$stmt->execute();
$academic_performance_impact = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $academic_performance_impact[] = $row;
}


$query = "SELECT 
            CASE 
                WHEN attendance >= 90 THEN 'Excellent'
                WHEN attendance >= 75 THEN 'Good'
                WHEN attendance >= 60 THEN 'Fair'
                ELSE 'Poor'
            END as attendance_level,
            COUNT(*) as count,
            AVG(dropout_risk) as avg_risk
          FROM students
          GROUP BY attendance_level
          ORDER BY 
            CASE attendance_level
                WHEN 'Excellent' THEN 1
                WHEN 'Good' THEN 2
                WHEN 'Fair' THEN 3
                ELSE 4
            END";

$stmt = $db->prepare($query);
$stmt->execute();
$attendance_impact = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $attendance_impact[] = $row;
}

$factors_data = array(
    "averages" => $averages,
    "socio_economic_distribution" => $socio_economic_distribution,
    "family_support_distribution" => $family_support_distribution,
    "academic_performance_impact" => $academic_performance_impact,
    "attendance_impact" => $attendance_impact
);

http_response_code(200);
echo json_encode($factors_data);
