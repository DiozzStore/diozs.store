<?php
// public_html/db-config.php
// Secure DB config using environment variables

// Load from environment variables (set on Render.com or .env)
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'a1761528_diozs';
$DB_USER = getenv('DB_USER') ?: 'a1761528_diozs';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_PORT = getenv('DB_PORT') ?: '3306';

// Use PDO (recommended)
try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // In production, log and show a friendly message instead
    error_log('DB connect error: '.$e->getMessage());
    die('Database connection error.'); // brief for visitors
}
