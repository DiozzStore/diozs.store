<?php
header('Content-Type: application/json');
require 'db-connection.php';

// Get form data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$ip_address = $_SERVER['REMOTE_ADDR'];

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// Escape strings
$email = mysqli_real_escape_string($conn, $email);
$phone = mysqli_real_escape_string($conn, $phone);
$country = mysqli_real_escape_string($conn, $country);
$city = mysqli_real_escape_string($conn, $city);

// Insert into Railway MySQL
$query = "INSERT INTO unsubscribed (email, phone, country, city, ip_address) 
          VALUES ('$email', '$phone', '$country', '$city', '$ip_address')
          ON DUPLICATE KEY UPDATE updated_at = NOW()";

if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true, 'message' => 'Unsubscribed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
