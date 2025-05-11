
<?php
// radarr.php - Radarr API proxy endpoints
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
} catch (Exception $e) {
    send_error('Unauthorized: ' . $e->getMessage(), 401);
}

// Parse request
$request_method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'movies':
        handle_movies($request_method, $user_id);
        break;
        
    case 'rootfolders':
        handle_rootfolders($request_method, $user_id);
        break;
        
    case 'search':
        handle_search($request_method, $user_id);
        break;
        
    case 'add':
        handle_add($request_method, $user_id);
        break;
        
    default:
        send_error('Invalid action', 400);
}

function handle_movies($method, $user_id) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
    // Get all movies from Radarr
    $response = make_api_request(
        RADARR_URL . '/api/v3/movie', 
        RADARR_API_KEY,
        RADARR_AUTH_USER,
        RADARR_AUTH_PASS
    );
    
    if (isset($response['error'])) {
        send_error('Error fetching movies from Radarr: ' . $response['error'], $response['status_code']);
    }
    
    // Log the activity
    log_activity($user_id, 'view_movies', 'Viewed movie library');
    
    send_json_response($response['data']);
}

function handle_rootfolders($method, $user_id) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
    // Get root folders from Radarr
    $response = make_api_request(
        RADARR_URL . '/api/v3/rootfolder', 
        RADARR_API_KEY,
        RADARR_AUTH_USER,
        RADARR_AUTH_PASS
    );
    
    if (isset($response['error'])) {
        send_error('Error fetching root folders from Radarr: ' . $response['error'], $response['status_code']);
    }
    
    send_json_response($response['data']);
}

function handle_search($method, $user_id) {
    if ($method !== 'GET') {
        send_error('Method not allowed', 405);
    }
    
    $term = $_GET['term'] ?? '';
    
    if (empty($term)) {
        send_error('Search term is required', 400);
    }
    
    // Search for movies on Radarr
    $response = make_api_request(
        RADARR_URL . '/api/v3/movie/lookup?term=' . urlencode($term), 
        RADARR_API_KEY,
        RADARR_AUTH_USER,
        RADARR_AUTH_PASS
    );
    
    if (isset($response['error'])) {
        send_error('Error searching movies: ' . $response['error'], $response['status_code']);
    }
    
    // Get existing movies to check if results are already in library
    $existing_response = make_api_request(
        RADARR_URL . '/api/v3/movie', 
        RADARR_API_KEY,
        RADARR_AUTH_USER,
        RADARR_AUTH_PASS
    );
    
    if (!isset($response['error']) && isset($existing_response['data'])) {
        $existing_tmdb_ids = array_map(function($movie) {
            return $movie['tmdbId'];
        }, $existing_response['data']);
        
        // Mark movies that are already in the library
        foreach ($response['data'] as &$movie) {
            $movie['inLibrary'] = in_array($movie['tmdbId'], $existing_tmdb_ids);
        }
    }
    
    // Log the search activity
    log_activity($user_id, 'search_movies', "Searched for movie: $term");
    
    send_json_response($response['data']);
}

function handle_add($method, $user_id) {
    if ($method !== 'POST') {
        send_error('Method not allowed', 405);
    }
    
    // Get request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);
    
    if (!isset($data['tmdbId']) || !isset($data['title']) || !isset($data['rootFolderPath'])) {
        send_error('Missing required fields (tmdbId, title, rootFolderPath)', 400);
    }
    
    // Add movie to Radarr
    $response = make_api_request(
        RADARR_URL . '/api/v3/movie', 
        RADARR_API_KEY,
        RADARR_AUTH_USER,
        RADARR_AUTH_PASS,
        'POST',
        $data
    );
    
    if (isset($response['error'])) {
        send_error('Error adding movie: ' . $response['error'], $response['status_code']);
    }
    
    // Log the activity
    log_activity($user_id, 'add_movie', "Added movie: {$data['title']}");
    
    send_json_response($response['data'], 201);
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
