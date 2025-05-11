<?php
// additions.php - API endpoint for viewing additions (added content)
// This will be a subset of the activity logs filtered to only show added content

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
    header('Content-Type: application/json');
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
    
    // Base64 url decode
    $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $header)), true);
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
    $payload = json_decode($payload_json, true);
    
    $valid_signature = hash_hmac('sha256', "$header.$payload", APP_SECRET, true);
    $valid_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($valid_signature));
    
    if ($signature !== $valid_signature) {
        throw new Exception('Invalid token signature');
    }
    
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        throw new Exception('Token has expired');
    }
    
    return $payload;
}

// Handle getting all additions (filtered activity logs)
function handle_list_additions($user_id) {
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Get all activity logs related to adding content
    $sql = "
        SELECT a.*, u.email AS user_email 
        FROM activity_logs a
        JOIN users u ON a.user_id = u.id
        WHERE a.action IN ('add_series', 'add_movie')
        ORDER BY a.created_at DESC
        LIMIT 500
    ";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        send_error('Error fetching additions: ' . $conn->error, 500);
    }
    
    $additions = [];
    while ($row = $result->fetch_assoc()) {
        $additions[] = $row;
    }
    
    // Log that admin viewed additions
    log_activity($user_id, 'view_additions', 'Viewed content additions');
    
    send_json_response($additions);
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
$auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    send_error('Unauthorized: Missing or invalid token', 401);
}

$token = $matches[1];

try {
    $payload = validate_jwt($token);
    $user_id = $payload['user_id'];
    $role = $payload['role'];
    
    // Only admins can access additions
    if ($role !== 'admin') {
        send_error('Forbidden: Admin access required', 403);
    }
} catch (Exception $e) {
    send_error('Unauthorized: ' . $e->getMessage(), 401);
}

// Parse request
$request_method = $_SERVER['REQUEST_METHOD'];

// Only support GET requests
if ($request_method !== 'GET') {
    send_error('Method not allowed', 405);
}

handle_list_additions($user_id);
?>