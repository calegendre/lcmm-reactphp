
<?php
// auth.php - Authentication endpoints

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Method not allowed', 405);
}

// Get the request body
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Determine the auth action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handle_login($data);
        break;
        
    case 'register':
        handle_register($data);
        break;
        
    case 'verify-token':
        verify_token($data);
        break;
        
    default:
        send_error('Invalid auth action', 400);
}

function handle_login($data) {
    if (!isset($data['email']) || !isset($data['password'])) {
        send_error('Email and password are required');
    }
    
    $email = $data['email'];
    $password = $data['password'];
    
    $conn = get_db_connection();
    if (!$conn) {
        send_error('Database connection error', 500);
    }
    
    // Prepare the query to find the user
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
    
    // Generate a JWT token
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ];
    
    $token = generate_jwt($payload);
    
    // Log the activity
    log_activity($user['id'], 'login', 'User logged in');
    
    send_json_response([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
}

function handle_register($data) {
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

function verify_token($data) {
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

// Helper functions

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
