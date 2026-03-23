<?php
// Database connection
$host = getenv('DB_HOST') ?: 'centerbeam.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: 29134;
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'rrQdhfcRK1RwIGZUvpADAbCzoiCABBjj';
$database = getenv('DB_NAME') ?: 'railway';

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$migrated = 0;
$errors = 0;

// Read CSV files
$csv_files = ['disposable-email-domains.txt', 'disposable-emails.txt'];

foreach ($csv_files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file<br>";
        continue;
    }

    $handle = fopen($file, 'r');
    while (($line = fgets($handle)) !== false) {
        $email = trim($line);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("INSERT IGNORE INTO unsubscribed (email, country, phone) VALUES (?, 'Unknown', '')");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                if ($stmt->execute()) {
                    $migrated++;
                } else {
                    $errors++;
                }
                $stmt->close();
            }
        }
    }
    fclose($handle);
}

echo "Migration Complete!<br>";
echo "Records migrated: $migrated<br>";
echo "Errors: $errors<br>";
echo "<br><a href='view-unsubscribed.php'>View all unsubscribed</a>";

$conn->close();
?>

