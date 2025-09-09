<?php
// api/index.php - Final Version as of Sep 08, 2025

// Show all errors for debugging purposes.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the helper files for database and response functions.
require __DIR__ . '/response.php';
require __DIR__ . '/db.php';

// --- ROUTING LOGIC ---
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// FIX: Set the correct base path for your Droplet setup.
$base = '/contact_manager/api';
if ($base && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

// Helper function to read and validate the incoming JSON body.
function read_json() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) err('Invalid or missing JSON body', 400);
    return $data;
}


/* -------------------- API ROUTES -------------------- */

// GET /health → A simple check to see if the API is responding.
if ($path === '/health' && $method === 'GET') {
    ok(['status' => 'ok', 'time' => date('c')]);
}

// GET /dbping → Verifies that the PHP script can connect to the database.
if ($path === '/dbping' && $method === 'GET') {
    try {
        $row = db()->query('SELECT NOW() AS now')->fetch();
        ok(['db' => 'ok', 'time' => $row['now']]);
    } catch (Throwable $e) {
        err('DB connection failed', 500, ['details' => $e->getMessage()]);
    }
}

/* -------------------- AUTHENTICATION ROUTES -------------------- */

// POST /users → Register a new user.
if ($path === '/users' && $method === 'POST') {
    $in = read_json();

    // The JSON body must contain 'username' and 'password'.
    if (empty($in['username']) || empty($in['password'])) {
        err('username and password required', 422);
    }

    // Optional fields (default to empty string if not provided).
    $first   = isset($in['firstName']) ? trim((string)$in['firstName']) : '';
    $last    = isset($in['lastName'])  ? trim((string)$in['lastName'])  : '';

    // Check if the username already exists in the database.
    // FIX: Query uses the correct 'username' column.
    $dup = db()->prepare('SELECT 1 FROM users WHERE username = ?');
    $dup->execute([$in['username']]);
    if ($dup->fetch()) err('Username already exists', 409);

    // Create a secure hash of the user's password.
    $hash = password_hash($in['password'], PASSWORD_DEFAULT);

    // Prepare the INSERT statement.
    // FIX: Column names (first_name, last_name, username, password) now perfectly match your database schema.
    $stmt = db()->prepare(
        'INSERT INTO users (first_name, last_name, username, password) VALUES (?,?,?,?)'
    );
    $stmt->execute([$first, $last, $in['username'], $hash]);

    // Return a success response with the new user's data.
    ok([
        'id'        => (int)db()->lastInsertId(),
        'username'  => $in['username'],
        'firstName' => $first,
        'lastName'  => $last
    ], 201); // 201 Created status code.
}

// POST /login → Authenticate a user and log them in.
if ($path === '/login' && $method === 'POST') {
    $in = read_json();
    if (empty($in['username']) || empty($in['password'])) {
        err('username and password required', 422);
    }

    // Find the user by their username.
    // FIX: Query uses the correct column names to match your database schema.
    $stmt = db()->prepare(
        'SELECT id, first_name, last_name, username, password FROM users WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$in['username']]);
    $user = $stmt->fetch();
    if (!$user) err('Invalid credentials', 401); // Use a generic error for security.

    // Verify the submitted password against the hash stored in the database.
    if (!password_verify($in['password'], (string)$user['password'])) {
        err('Invalid credentials', 401);
    }

    // If successful, return the user's data.
    // FIX: Uses correct snake_case keys from the database result ($user['first_name'])
    // and maps them to camelCase keys for the JSON response ('firstName').
    ok([
        'userId'    => (int)$user['id'],
        'username'  => $user['username'],
        'firstName' => $user['first_name'],
        'lastName'  => $user['last_name']
    ]);
}


/* -------------------- FALLBACK ROUTE -------------------- */

// If no other route was matched, return a 404 Not Found error.
err('Not found', 404);