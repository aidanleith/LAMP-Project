
<?php
// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit;
}

require_once __DIR__ . '/db.php';

// Basic routing example
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

switch ($method) {
	case 'GET':
		// Example: return API status
		echo json_encode(['status' => 'API is running']);
		break;
	case 'POST':
		// Example: handle POST request
		$input = json_decode(file_get_contents('php://input'), true);
		echo json_encode(['received' => $input]);
		break;
	default:
		http_response_code(405);
		echo json_encode(['error' => 'Method Not Allowed']);
		break;
}
