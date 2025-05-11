<?php
// JWT utility functions that match the frontend implementation

define('APP_SECRET', 'legendrecloud_secure_key_2025');

// Base64 URL encode
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Base64 URL decode
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Validate a JWT token
function validate_jwt($token) {
    try {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($header_b64, $payload_b64, $signature_b64) = $parts;
        
        // Decode the header and payload
        $header = json_decode(base64url_decode($header_b64), true);
        $payload = json_decode(base64url_decode($payload_b64), true);
        
        if (!$header || !$payload) {
            throw new Exception('Invalid token data');
        }
        
        // Verify the signature
        $data = "$header_b64.$payload_b64";
        $signature = base64url_decode($signature_b64);
        $expected_signature = hash_hmac('sha256', $data, APP_SECRET, true);
        
        if (!hash_equals($signature, $expected_signature)) {
            throw new Exception('Invalid signature');
        }
        
        // Check if token has expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        return $payload;
    } catch (Exception $e) {
        error_log('JWT validation error: ' . $e->getMessage());
        throw $e;
    }
}

// Create a new JWT token
function create_jwt($payload) {
    // Set headers
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    
    // Encode header and payload
    $header_b64 = base64url_encode(json_encode($header));
    $payload_b64 = base64url_encode(json_encode($payload));
    
    // Create signature
    $data = "$header_b64.$payload_b64";
    $signature = hash_hmac('sha256', $data, APP_SECRET, true);
    $signature_b64 = base64url_encode($signature);
    
    // Combine parts
    return "$header_b64.$payload_b64.$signature_b64";
}
?>