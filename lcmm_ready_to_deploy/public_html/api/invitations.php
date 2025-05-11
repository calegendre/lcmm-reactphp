
<?php
// invitations.php - Invitation management endpoints
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
        handle_list_invitations($request_method);
        break;
        
    case 'create':
        handle_create_invitation($request_method, $user_id);
        break;
        
    case 'delete':
        handle_delete_invitation($request_method);
        break;
        
    default:
        send_error('Invalid action', 400);
}

function handle_list_invitations($method) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_create_invitation($method, $user_id) {
    if ($method !== 'POST') {
        send_error('Method not allowed', 405);
    }
    
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

function handle_delete_invitation($method) {
    if ($method !== 'DELETE') {
        send_error('Method not allowed', 405);
    }
    
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
