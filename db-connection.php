<?php
// Railway MySQL Connection
$db_host = getenv('MYSQL_HOST') ?: 'mysql.railway.internal';
$db_port = getenv('MYSQL_PORT') ?: 3306;
$db_user = getenv('MYSQL_USER') ?: 'root';
$db_password = getenv('rrQdhfcRKlRwIGZUvpADAbCzoicAbBJj') ?: '';
$db_name = getenv('MYSQL_DATABASE') ?: 'railway';

// Create connection using mysqli_connect
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name, $db_port);

// Check connection
if (!$conn) {
    die("DATABASE CONNECTION FAILED: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8mb4");

// Create table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS unsubscribed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    country VARCHAR(100),
    city VARCHAR(100),
    ip_address VARCHAR(45),
    device VARCHAR(100),
    browser VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $create_table)) {
    die("ERROR CREATING TABLE: " . mysqli_error($conn));
}

?>
