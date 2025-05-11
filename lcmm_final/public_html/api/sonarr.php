<?php
// Enable error reporting during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'legendrecloud_lcmm');
define('DB_USER', 'legendrecloud_lcmmuser');
define('DB_PASS', 'Royal&Downloader*2025*');
define('APP_SECRET', 'legendrecloud_secure_key_2025');

// Sonarr API configuration
define('SONARR_URL', 'http://plex.legendre.cloud:8989');
define('SONARR_API_KEY', 'bae6e0f4548846e3b71290ce6817d081');
define('SONARR_AUTH_USER', 'admin');
define('SONARR_AUTH_PASS', 'Downloader2023*');

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

// JWT validation function
function validate_jwt($token) {
    $parts = explode('.', $token);
    
    if (count($parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    
    list($header, $payload, $signature) = $parts;
    
    $valid_signature = base64_encode(hash_hmac('sha256', "$header.$payload", APP_SECRET, true));
    
    if ($signature !== $valid_signature) {
        throw new Exception('Invalid token signature');
    }
    
    $payload = json_decode(base64_decode($payload), true);
    
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        throw new Exception('Token has expired');
    }
    
    return $payload;
}

// Function to make API calls to Sonarr
function make_api_request($url, $method = 'GET', $data = null) {
    $curl = curl_init();
    
    $headers = [
        'X-Api-Key: ' . SONARR_API_KEY,
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
        CURLOPT_USERPWD => SONARR_AUTH_USER . ':' . SONARR_AUTH_PASS
    ]);
    
    if (($method === 'POST' || $method === 'PUT') && $data !== null) {
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

// Helper function to log activity
function log_activity($user_id, $action, $details = '', $ip_address = null) {
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $conn = get_db_connection();
    if (!$conn) {
        error_log('Unable to log activity: database connection error');
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
    
    return $stmt->execute();
}

// Handle getting all series
function handle_series($user_id) {
    // Get all series from Sonarr
    $response = make_api_request(SONARR_URL . '/api/v3/series');
    
    if (isset($response['error'])) {
        send_error('Error fetching series from Sonarr: ' . $response['error'], $response['status_code']);
    }
    
    // Log the activity
    log_activity($user_id, 'view_series', 'Viewed TV series library');
    
    send_json_response($response['data']);
}

// Handle getting root folders
function handle_rootfolders() {
    // Get root folders from Sonarr
    $response = make_api_request(SONARR_URL . '/api/v3/rootfolder');
    
    if (isset($response['error'])) {
        send_error('Error fetching root folders from Sonarr: ' . $response['error'], $response['status_code']);
    }
    
    send_json_response($response['data']);
}

// Handle searching for series
function handle_search($user_id) {
    $term = $_GET['term'] ?? '';
    
    if (empty($term)) {
        send_error('Search term is required', 400);
    }
    
    // Search for series on Sonarr
    $response = make_api_request(SONARR_URL . '/api/v3/series/lookup?term=' . urlencode($term));
    
    if (isset($response['error'])) {
        send_error('Error searching series: ' . $response['error'], $response['status_code']);
    }
    
    // Get existing series to check if results are already in library
    $existing_response = make_api_request(SONARR_URL . '/api/v3/series');
    
    if (!isset($response['error']) && isset($existing_response['data'])) {
        $existing_tvdb_ids = array_map(function($series) {
            return $series['tvdbId'];
        }, $existing_response['data']);
        
        // Mark series that are already in the library
        foreach ($response['data'] as &$series) {
            $series['inLibrary'] = in_array($series['tvdbId'], $existing_tvdb_ids);
        }
    }
    
    // Log the search activity
    log_activity($user_id, 'search_series', "Searched for TV series: $term");
    
    send_json_response($response['data']);
}

// Handle adding series
function handle_add($user_id) {
    // Get request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
    
    if (!isset($data['tvdbId']) || !isset($data['title']) || !isset($data['rootFolderPath'])) {
        send_error('Missing required fields (tvdbId, title, rootFolderPath)', 400);
    }
    
    // Add series to Sonarr
    $response = make_api_request(
        SONARR_URL . '/api/v3/series', 
        'POST',
        $data
    );
    
    if (isset($response['error'])) {
        send_error('Error adding series: ' . $response['error'], $response['status_code']);
    }
    
    // Log the activity
    log_activity($user_id, 'add_series', "Added TV series: {$data['title']}");
    
    send_json_response($response['data'], 201);
}

// Main code - Process the request
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JWT from authorization header
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    send_error('Unauthorized: Missing or invalid token', 401);
}

$token = $matches[1];

try {
    $payload = validate_jwt($token);
    $user_id = $payload['user_id'];
} catch (Exception $e) {
    send_error('Unauthorized: ' . $e->getMessage(), 401);
}

// Parse request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'series':
        handle_series($user_id);
        break;
        
    case 'rootfolders':
        handle_rootfolders();
        break;
        
    case 'search':
        handle_search($user_id);
        break;
        
    case 'add':
        handle_add($user_id);
        break;
        
    default:
        send_error('Invalid action', 400);
}
?>