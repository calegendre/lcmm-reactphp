
<?php
require_once '../config.php';

// Basic router for API endpoints
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Parse the request path
$path = parse_url($request_uri, PHP_URL_PATH);
$path_segments = explode('/', trim($path, '/'));

// The API endpoint is typically the second segment after 'api'
$endpoint = $path_segments[1] ?? '';

// Route the request to the appropriate handler
switch ($endpoint) {
    case 'auth':
        require_once 'auth.php';
        break;
        
    case 'sonarr':
        require_once 'sonarr.php';
        break;
        
    case 'radarr':
        require_once 'radarr.php';
        break;
        
    case 'users':
        require_once 'users.php';
        break;
        
    case 'invitations':
        require_once 'invitations.php';
        break;
        
    default:
        // Return API info for root endpoint
        if (empty($endpoint)) {
            send_json_response([
                'name' => 'Legendre Cloud Media Manager API',
                'version' => '1.0.0',
                'status' => 'online'
            ]);
        } else {
            send_error('Invalid API endpoint', 404);
        }
}
