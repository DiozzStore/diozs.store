<?php
declare(strict_types=1);

require 'db.php'; // 🔥 THIS connects to Railway MySQL

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

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

// Clean phone
$phone = isset($data['phone_number']) ? trim($data['phone_number']) : '';
if (!empty($phone) && strpos($phone, '+') !== 0) {
    $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
}

// Get IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Optional fields
$country = $data['country'] ?? '';
$city = $data['city'] ?? '';
$device = $data['device_type'] ?? '';
$browser = $data['browser_name'] ?? '';

try {
    // 🔥 SAVE TO DATABASE (THIS IS THE FIX)
    $stmt = $pdo->prepare("
        INSERT INTO unsubscribers 
        (email, phone, ip, country, city, device, browser)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $email,
        $phone,
        $ip,
        $country,
        $city,
        $device,
        $browser
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Saved to database successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
