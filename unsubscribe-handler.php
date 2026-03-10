<?php
declare(strict_types=1);

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function get_client_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = $_SERVER[$k];
            if ($k === 'HTTP_X_FORWARDED_FOR') $ip = explode(',', $ip)[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

function get_user_agent(): string {
    return isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : 'Unknown';
}

function get_disposable_domains(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    
    $file = __DIR__ . '/disposable-email-domains.txt';
    if (!file_exists($file)) return [];
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $domains = [];
    foreach ($lines as $domain) {
        $domain = strtolower(trim($domain));
        if (!empty($domain) && strpos($domain, '#') !== 0) {
            $domains[$domain] = true;
        }
    }
    return $cache = $domains;
}

function validate_email_syntax(string $email): array {
    $email = trim($email);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Invalid email format.', 'check' => 'Syntax Validator'];
    }
    
    if (strlen($email) > 254) {
        return ['valid' => false, 'message' => 'Email is too long (max 254 characters).', 'check' => 'Syntax Validator'];
    }
    
    $local = explode('@', $email)[0];
    if (strlen($local) > 64) {
        return ['valid' => false, 'message' => 'Local part too long (max 64 characters).', 'check' => 'Syntax Validator'];
    }
    
    if (strpos($email, '..') !== false) {
        return ['valid' => false, 'message' => 'Email contains consecutive dots.', 'check' => 'Syntax Validator'];
    }
    
    if ($local[0] === '.' || $local[strlen($local)-1] === '.') {
        return ['valid' => false, 'message' => 'Email local part starts or ends with dot.', 'check' => 'Syntax Validator'];
    }
    
    return ['valid' => true, 'message' => 'Syntax is valid.', 'check' => 'Syntax Validator'];
}

function check_disposable_email(string $email): array {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return ['valid' => false, 'message' => 'Invalid email format.', 'check' => 'Disposable Email Checker'];
    }
    
    $domain = strtolower(trim($parts[1]));
    $disposable_domains = get_disposable_domains();
    
    if (isset($disposable_domains[$domain])) {
        return ['valid' => false, 'message' => 'Temporary/disposable email detected.', 'check' => 'Disposable Email Checker'];
    }
    
    return ['valid' => true, 'message' => 'Not a disposable email.', 'check' => 'Disposable Email Checker'];
}

function check_mx_records(string $email): array {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return ['valid' => false, 'message' => 'Invalid email format.', 'check' => 'MX Record Checker'];
    }
    
    $domain = strtolower(trim($parts[1]));
    
    if (!checkdnsrr($domain, 'ANY')) {
        return ['valid' => false, 'message' => 'Domain does not have valid DNS records.', 'check' => 'MX Record Checker'];
    }
    
    $mxhosts = [];
    if (!getmxrr($domain, $mxhosts)) {
        if (!checkdnsrr($domain, 'A') && !checkdnsrr($domain, 'AAAA')) {
            return ['valid' => false, 'message' => 'Domain has no valid mail server (MX records).', 'check' => 'MX Record Checker'];
        }
    }
    
    return ['valid' => true, 'message' => 'Domain has valid MX records.', 'check' => 'MX Record Checker'];
}

function detect_provider(string $email): array {
    $parts = explode('@', $email);
    $domain = strtolower(trim($parts[1]));
    
    $providers = [
        'gmail.com' => 'Gmail',
        'yahoo.com' => 'Yahoo',
        'hotmail.com' => 'Hotmail',
        'outlook.com' => 'Outlook',
        'aol.com' => 'AOL',
        'protonmail.com' => 'ProtonMail',
        'icloud.com' => 'iCloud',
        'mail.com' => 'Mail.com',
    ];
    
    $provider = $providers[$domain] ?? 'Business/Custom Domain';
    
    return ['valid' => true, 'message' => 'Provider: ' . $provider, 'check' => 'Provider Detection'];
}

function validate_email_comprehensive(string $email): array {
    $checks = [];
    
    $syntax_check = validate_email_syntax($email);
    $checks[] = $syntax_check;
    if (!$syntax_check['valid']) {
        return ['valid' => false, 'checks' => $checks];
    }
    
    $disposable_check = check_disposable_email($email);
    $checks[] = $disposable_check;
    if (!$disposable_check['valid']) {
        return ['valid' => false, 'checks' => $checks];
    }
    
    $mx_check = check_mx_records($email);
    $checks[] = $mx_check;
    if (!$mx_check['valid']) {
        return ['valid' => false, 'checks' => $checks];
    }
    
    $provider_check = detect_provider($email);
    $checks[] = $provider_check;
    
    return ['valid' => true, 'checks' => $checks];
}

// Remove duplicate email from CSV (keep only latest entry)
function remove_duplicate_from_csv(string $csv_file, string $email): void {
    if (!file_exists($csv_file)) return;
    
    $rows = [];
    $handle = fopen($csv_file, 'r');
    $header = fgetcsv($handle);
    
    // Read all rows except the one with matching email
    while (($row = fgetcsv($handle)) !== false) {
        if (isset($row[1]) && strtolower($row[1]) !== strtolower($email)) {
            $rows[] = $row;
        }
    }
    fclose($handle);
    
    // Rewrite CSV without the old email entry
    $handle = fopen($csv_file, 'w');
    fputcsv($handle, $header);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);
}

// Parse input - try JSON first, then form data
$input = file_get_contents('php://input');
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($input)) {
        $json = json_decode($input, true);
        if ($json && is_array($json)) {
            $data = $json;
        }
    }
    
    // Fallback to $_POST if no JSON
    if (empty($data)) {
        $data = $_POST;
    }
}

$email = isset($data['email']) ? trim($data['email']) : null;

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

$validation = validate_email_comprehensive($email);

if (!$validation['valid']) {
    http_response_code(400);
    
    $failed_message = 'Email validation failed.';
    foreach ($validation['checks'] as $check) {
        if (!$check['valid']) {
            $failed_message = $check['message'];
            break;
        }
    }
    
    echo json_encode(['success' => false, 'message' => $failed_message, 'checks' => $validation['checks']]);
    exit;
}

$file = __DIR__ . '/unsubscribed-emails.txt';
$csv_file = __DIR__ . '/onesignal-import.csv';

try {
    // Generate unique external_id
    $external_id = 'user_' . bin2hex(random_bytes(8));
    
    // Extract phone number, ensuring it starts with + or is empty
    $phone = isset($data['phone_number']) ? trim($data['phone_number']) : '';
    if (!empty($phone) && strpos($phone, '+') !== 0) {
        $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
    }
    
    // Get timestamp
    $timestamp = date('Y-m-d H:i:s');
    $server_ip = get_client_ip();
    $ua = get_user_agent();
    
    // Use IP from client if available, otherwise use server-detected IP
    $ip = isset($data['ip']) && !empty($data['ip']) ? trim($data['ip']) : $server_ip;
    
    // Data for OneSignal format
    $onesignal_data = [
        'external_id' => $external_id,
        'email' => $email,
        'phone_number' => $phone,
        'country' => isset($data['country']) ? trim($data['country']) : '',
        'country_name' => isset($data['country_name']) ? trim($data['country_name']) : '',
        'city' => isset($data['city']) ? trim($data['city']) : '',
        'language' => isset($data['language']) ? trim($data['language']) : 'en',
        'timezone_id' => isset($data['timezone_id']) ? trim($data['timezone_id']) : 'UTC',
        'subscribed' => 'yes',
        'ip' => $ip,
        'user_agent' => $ua,
        'source' => isset($data['source']) ? trim($data['source']) : 'web',
        'device_type' => isset($data['device_type']) ? trim($data['device_type']) : '',
        'browser_name' => isset($data['browser_name']) ? trim($data['browser_name']) : '',
        'latitude' => isset($data['latitude']) ? trim($data['latitude']) : '',
        'longitude' => isset($data['longitude']) ? trim($data['longitude']) : '',
        'timestamp' => $timestamp,
    ];
    
    // Remove old entry if email already exists (prevents duplicates)
    remove_duplicate_from_csv($csv_file, $email);
    
    // Append to text log (keep original format for compatibility)
    $log_entry = $email . ' | ' . $timestamp . ' | IP: ' . $ip . ' | Country: ' . $onesignal_data['country'] . ' | UA: ' . $ua . PHP_EOL;
    file_put_contents($file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Append to CSV for OneSignal import
    $csv_exists = file_exists($csv_file);
    
    // OneSignal CSV format with IP and Country
    $csv_line = implode(',', [
        '"' . str_replace('"', '""', $onesignal_data['external_id']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['email']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['phone_number']) . '"',
        '"' . $onesignal_data['subscribed'] . '"',
        '"' . str_replace('"', '""', $onesignal_data['timezone_id']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['country']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['country_name']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['city']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['language']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['ip']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['device_type']) . '"',
        '"' . str_replace('"', '""', $onesignal_data['browser_name']) . '"',
        '"FALSE"',
    ]) . PHP_EOL;
    
    // Add header if file doesn't exist
    if (!$csv_exists) {
        $header = 'external_id,email,phone_number,subscribed,timezone_id,country,country_name,city,language,ip,device_type,browser_name,suppressed' . PHP_EOL;
        file_put_contents($csv_file, $header, LOCK_EX);
    }
    
    // Append data
    file_put_contents($csv_file, $csv_line, FILE_APPEND | LOCK_EX);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Information recorded successfully.',
        'external_id' => $external_id,
        'checks' => $validation['checks']
    ]);
    exit;

} catch (Exception $e) {
    error_log('Unsubscribe error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
    exit;
}
?>
