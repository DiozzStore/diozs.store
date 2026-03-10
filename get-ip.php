<?php
// get-ip.php - Simple IP only (no external API calls)
header('Content-Type: application/json');

function get_client_ip(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    
    foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = $_SERVER[$k];
            if ($k === 'HTTP_X_FORWARDED_FOR') {
                $ip = explode(',', $ip)[0];
            }
            return trim($ip);
        }
    }
    
    return '0.0.0.0';
}

// Just return IP - no external API calls
echo json_encode([
    'ip' => get_client_ip(),
    'country' => '',
    'country_name' => '',
    'city' => '',
]);
?>
