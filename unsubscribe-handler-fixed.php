<?php
declare(strict_types=1);

// ✅ CONNECT TO DATABASE
require 'db.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate email
$email = isset($data['email']) ? trim($data['email']) : null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

// Escape and clean data
$email = mysqli_real_escape_string($conn, $email);
$phone = mysqli_real_escape_string($conn, isset($data['phone_number']) ? trim($data['phone_number']) : '');
$country = mysqli_real_escape_string($conn, $data['country_name'] ?? '');
$city = mysqli_real_escape_string($conn, $data['city'] ?? '');
$ip = mysqli_real_escape_string($conn, $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
$device = mysqli_real_escape_string($conn, $data['device_type'] ?? '');
$browser = mysqli_real_escape_string($conn, $data['browser_name'] ?? '');

try {
    // 🔥 SAVE TO RAILWAY MYSQL DATABASE
    $query = "INSERT INTO unsubscribed 
              (email, phone, country, city, ip_address, device, browser, created_at)
              VALUES ('$email', '$phone', '$country', '$city', '$ip', '$device', '$browser', NOW())
              ON DUPLICATE KEY UPDATE 
              phone = '$phone',
              country = '$country',
              city = '$city',
              ip_address = '$ip',
              device = '$device',
              browser = '$browser'";

    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Your information has been recorded'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
