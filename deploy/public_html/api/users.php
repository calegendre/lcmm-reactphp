
<?php
// users.php - User management endpoints
require_once '../config.php';

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
        handle_list_users($request_method);
        break;
        
    case 'get':
        handle_get_user($request_method);
        break;
        
    case 'update':
        handle_update_user($request_method);
        break;
        
    case 'delete':
        handle_delete_user($request_method);
        break;
    
    case 'activity':
        handle_user_activity($request_method);
        break;
        
    default:
        send_error('Invalid action', 400);
}

function handle_list_users($method) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_get_user($method) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_update_user($method) {
    if ($method !== 'PUT') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_delete_user($method) {
    if ($method !== 'DELETE') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_user_activity($method) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
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

// Helper function to validate JWT
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
