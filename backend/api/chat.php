<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->message)) {

    $message = strtolower(trim($data->message));

    $responses = [
        'hello' => 'Hello! How can I help you today?',
        'hi' => 'Hi there! What can I do for you?',
        'help' => 'I can help you with information about dropout prevention, student support, and educational resources. What would you like to know?',
        'dropout' => 'Dropout prevention is important. We offer various support programs and resources to help students stay in school. Would you like to know more about specific programs?',
        'support' => 'We provide academic support, counseling services, and mentorship programs. Which type of support are you interested in?',
        'resources' => 'We have various resources including tutoring, study materials, and counseling services. What specific resources are you looking for?'
    ];

    $response = "I'm here to help with dropout prevention and student support. Could you please provide more details about what you need help with?";

    foreach ($responses as $key => $value) {
        if (strpos($message, $key) !== false) {
            $response = $value;
            break;
        }
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "response" => $response
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Message is required"
    ]);
}
