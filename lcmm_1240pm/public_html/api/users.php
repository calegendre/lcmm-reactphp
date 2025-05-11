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

// Handle listing all users
function handle_list_users() {
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    $result = $conn->query("SELECT id, email, role, created_at FROM users");
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    send_json_response($users);
}

// Handle getting a specific user
function handle_get_user() {
    $user_id = $_GET['id'] ?? '';
    
    if (empty($user_id)) {
        send_error('User ID is required', 400);
    }
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    $stmt = $conn->prepare("SELECT id, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('User not found', 404);
    }
    
    $user = $result->fetch_assoc();
    
    send_json_response($user);
}

// Handle updating a user
function handle_update_user() {
    // Get request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
    
    if (!isset($data['id'])) {
        send_error('User ID is required', 400);
    }
    
    $user_id = $data['id'];
    $role = $data['role'] ?? null;
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('User not found', 404);
    }
    
    $user = $result->fetch_assoc();
    
    // Only allow updating role
    if ($role !== null && in_array($role, ['user', 'admin'])) {
        // Prevent updating the last admin user to a regular user
        if ($user['role'] === 'admin' && $role === 'user') {
            $result = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
            $admin_count = $result->fetch_assoc()['admin_count'];
            
            if ($admin_count <= 1) {
                send_error('Cannot demote the last admin user', 400);
            }
        }
        
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $user_id);
        
        if (!$stmt->execute()) {
            send_error('Error updating user: ' . $conn->error, 500);
        }
        
        // Get updated user
        $stmt = $conn->prepare("SELECT id, email, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $updated_user = $stmt->get_result()->fetch_assoc();
        
        send_json_response($updated_user);
    } else {
        send_error('Invalid or missing role value', 400);
    }
}

// Handle deleting a user
function handle_delete_user($admin_user_id) {
    $user_id = $_GET['id'] ?? '';
    
    if (empty($user_id)) {
        send_error('User ID is required', 400);
    }
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Check if user exists and is not an admin
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('User not found', 404);
    }
    
    $user = $result->fetch_assoc();
    
    // Do not allow deleting admin users through API
    if ($user['role'] === 'admin') {
        send_error('Admin users cannot be deleted through the API', 400);
    }
    
    // Do not allow users to delete themselves
    if ($user_id == $admin_user_id) {
        send_error('Users cannot delete themselves', 400);
    }
    
    // Delete user's activity logs
    $stmt = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        send_error('Error deleting user: ' . $conn->error, 500);
    }
    
    send_json_response(['message' => 'User deleted successfully']);
}

// Handle getting user activity
function handle_user_activity() {
    $user_id = $_GET['id'] ?? '';
    
    if (empty($user_id)) {
        send_error('User ID is required', 400);
    }
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        send_error('User not found', 404);
    }
    
    // Get user activity logs
    $stmt = $conn->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    send_json_response($activities);
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
    $role = $payload['role'];
    
    // Only admins can access user management
    if ($role !== 'admin') {
        send_error('Forbidden: Admin access required', 403);
    }
} catch (Exception $e) {
    send_error('Unauthorized: ' . $e->getMessage(), 401);
}

// Parse request
$request_method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        handle_list_users();
        break;
        
    case 'get':
        handle_get_user();
        break;
        
    case 'update':
        handle_update_user();
        break;
        
    case 'delete':
        handle_delete_user($user_id);
        break;
    
    case 'activity':
        handle_user_activity();
        break;
        
    default:
        send_error('Invalid action', 400);
}
?>