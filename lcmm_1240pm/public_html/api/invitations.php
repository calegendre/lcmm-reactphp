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

// Handle listing all invitations
function handle_list_invitations() {
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    $result = $conn->query("
        SELECT i.*, c.email AS created_by_email, u.email AS used_by_email
        FROM invitations i
        JOIN users c ON i.created_by = c.id
        LEFT JOIN users u ON i.used_by = u.id
        ORDER BY i.created_at DESC
    ");
    
    $invitations = [];
    while ($row = $result->fetch_assoc()) {
        $invitations[] = $row;
    }
    
    send_json_response($invitations);
}

// Handle creating an invitation
function handle_create_invitation($user_id) {
    // Get request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
    
    $email = $data['email'] ?? null; // Optional email
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Generate a unique invitation code
    $code = bin2hex(random_bytes(16));
    
    // Create invitation
    $stmt = $conn->prepare("INSERT INTO invitations (code, email, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $code, $email, $user_id);
    
    if (!$stmt->execute()) {
        send_error('Error creating invitation: ' . $conn->error, 500);
    }
    
    $invitation_id = $conn->insert_id;
    
    // Get created invitation
    $stmt = $conn->prepare("
        SELECT i.*, u.email AS created_by_email
        FROM invitations i
        JOIN users u ON i.created_by = u.id
        WHERE i.id = ?
    ");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    
    $invitation = $stmt->get_result()->fetch_assoc();
    
    // Log the activity
    log_activity($user_id, 'create_invitation', "Created invitation code: $code" . ($email ? " for $email" : ""));
    
    send_json_response($invitation, 201);
}

// Handle deleting an invitation
function handle_delete_invitation($user_id) {
    $invitation_id = $_GET['id'] ?? '';
    
    if (empty($invitation_id)) {
        send_error('Invitation ID is required', 400);
    }
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Check if invitation exists and is unused
    $stmt = $conn->prepare("SELECT id, code, used FROM invitations WHERE id = ?");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('Invitation not found', 404);
    }
    
    $invitation = $result->fetch_assoc();
    
    // Prevent deleting used invitations
    if ($invitation['used']) {
        send_error('Cannot delete used invitations', 400);
    }
    
    // Delete invitation
    $stmt = $conn->prepare("DELETE FROM invitations WHERE id = ?");
    $stmt->bind_param("i", $invitation_id);
    
    if (!$stmt->execute()) {
        send_error('Error deleting invitation: ' . $conn->error, 500);
    }
    
    // Log the activity
    log_activity($user_id, 'delete_invitation', "Deleted invitation code: {$invitation['code']}");
    
    send_json_response(['message' => 'Invitation deleted successfully']);
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
    
    // Only admins can manage invitations
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
        handle_list_invitations();
        break;
        
    case 'create':
        handle_create_invitation($user_id);
        break;
        
    case 'delete':
        handle_delete_invitation($user_id);
        break;
        
    default:
        send_error('Invalid action', 400);
}
?>