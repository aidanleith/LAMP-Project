<?php
// api/response.php
//
// Provides helper functions for sending consistent JSON responses
// with correct HTTP status codes. All routes in index.php should
// use these helpers instead of echo/print.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Handle preflight OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

/**
 * Send a successful JSON response
 *
 * @param mixed $data  The data to return (array, object, string, etc.)
 * @param int   $code  HTTP status code (default 200 OK)
 */
function ok($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send an error JSON response
 *
 * @param string $msg   Error message
 * @param int    $code  HTTP status code (default 400 Bad Request)
 * @param array  $extra Extra fields to include in response
 */
function err(string $msg, int $code = 400, array $extra = []) {
    http_response_code($code);
    echo json_encode(array_merge(['error' => $msg], $extra), JSON_UNESCAPED_SLASHES);
    exit;
}
