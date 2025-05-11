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

// JWT functions
function generate_jwt($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64_encode($header);
    
    $payload = json_encode($payload);
    $payload = base64_encode($payload);
    
    $signature = hash_hmac('sha256', "$header.$payload", APP_SECRET, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

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

// Handle login
function handle_login() {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        send_error('Email and password are required');
    }
    
    $email = $data['email'];
    $password = $data['password'];
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Get user by email
    $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('Invalid email or password', 401);
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        send_error('Invalid email or password', 401);
    }
    
    // Generate JWT token
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ];
    
    $token = generate_jwt($payload);
    
    // Log activity
    log_activity($user['id'], 'login', 'User logged in');
    
    // Return token and user info
    send_json_response([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
}

// Handle register
function handle_register() {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['invitation_code'])) {
        send_error('Email, password, and invitation code are required');
    }
    
    $email = $data['email'];
    $password = $data['password'];
    $invitation_code = $data['invitation_code'];
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Verify the invitation code is valid and unused
    $stmt = $conn->prepare("SELECT id, created_by FROM invitations WHERE code = ? AND used = 0");
    $stmt->bind_param("s", $invitation_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('Invalid or already used invitation code', 400);
    }
    
    $invitation = $result->fetch_assoc();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        send_error('Email already in use', 400);
    }
    
    // Create the new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);
    
    if (!$stmt->execute()) {
        send_error('Error creating user: ' . $conn->error, 500);
    }
    
    $user_id = $conn->insert_id;
    
    // Mark invitation as used
    $stmt = $conn->prepare("UPDATE invitations SET used = 1, used_by = ?, used_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $invitation['id']);
    $stmt->execute();
    
    // Log the activity
    log_activity($invitation['created_by'], 'invitation_used', "Invitation $invitation_code used by $email");
    log_activity($user_id, 'registration', 'New user registered');
    
    // Generate a JWT token
    $payload = [
        'user_id' => $user_id,
        'email' => $email,
        'role' => 'user',
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ];
    
    $token = generate_jwt($payload);
    
    send_json_response([
        'token' => $token,
        'user' => [
            'id' => $user_id,
            'email' => $email,
            'role' => 'user'
        ]
    ], 201);
}

// Handle token verification
function verify_token() {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['token'])) {
        send_error('Token is required');
    }
    
    $token = $data['token'];
    
    try {
        $payload = validate_jwt($token);
        
        send_json_response([
            'valid' => true,
            'user' => [
                'id' => $payload['user_id'],
                'email' => $payload['email'],
                'role' => $payload['role']
            ]
        ]);
    } catch (Exception $e) {
        send_json_response([
            'valid' => false,
            'error' => $e->getMessage()
        ], 401);
    }
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Method not allowed', 405);
}

// Determine the auth action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handle_login();
        break;
        
    case 'register':
        handle_register();
        break;
        
    case 'verify-token':
        verify_token();
        break;
        
    default:
        send_error('Invalid auth action', 400);
}
?>