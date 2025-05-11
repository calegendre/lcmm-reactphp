<?php
// Database configuration
define('DB_HOST', 'localhost');  // Will be changed to actual MySQL host
define('DB_NAME', 'legendrecloud_lcmm');
define('DB_USER', 'legendrecloud_lcmmuser');
define('DB_PASS', 'Royal&Downloader*2025*');

// API configuration
define('SONARR_URL', 'http://plex.legendre.cloud:8989');
define('SONARR_API_KEY', 'bae6e0f4548846e3b71290ce6817d081');
define('SONARR_AUTH_USER', 'admin');
define('SONARR_AUTH_PASS', 'Downloader2023*');

define('RADARR_URL', 'http://plex.legendre.cloud:7878');
define('RADARR_API_KEY', '0d3448b9b1364cfeadbbab6fc50d966e');
define('RADARR_AUTH_USER', 'admin');
define('RADARR_AUTH_PASS', 'Downloader2023*');

// Application settings
define('ADMIN_EMAIL', 'cl@legendremedia.com');
define('ADMIN_PASSWORD', 'X9k#vP2$mL8qZ3nT');
define('APP_SECRET', 'legendrecloud_secure_key_2025'); // For session security

// Headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection function
function get_db_connection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return null;
    }
    
    return $conn;
}

// API response helpers
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

function send_error($message, $status_code = 400) {
    send_json_response(['error' => $message], $status_code);
}

// Function to make API calls to Sonarr/Radarr
function make_api_request($url, $api_key, $auth_user, $auth_pass, $method = 'GET', $data = null) {
    $curl = curl_init();
    
    $headers = [
        'X-Api-Key: ' . $api_key,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERPWD => $auth_user . ':' . $auth_pass
    ]);
    
    if ($method === 'POST' || $method === 'PUT' && $data !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($err) {
        error_log("cURL Error: " . $err);
        return ['error' => $err, 'status_code' => $status_code];
    }
    
    return [
        'data' => json_decode($response, true),
        'status_code' => $status_code
    ];
}