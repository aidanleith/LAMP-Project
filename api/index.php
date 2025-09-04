<?php
// api/index.php
require __DIR__ . '/response.php';
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If using Apache at /api, set $base = '/api'.
// If running locally with: php -S 127.0.0.1:8000 -t api api/index.php
// then keep $base = ''.
$base = '';
if ($base && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

/* -------------------- ROUTES -------------------- */

// GET /health → simple API check
if ($path === '/health' && $method === 'GET') {
    ok(['status' => 'ok', 'time' => date('c')]);
}

// GET /dbping → verifies DB connection
if ($path === '/dbping' && $method === 'GET') {
    try {
        $row = db()->query('SELECT NOW() AS now')->fetch();
        ok(['db' => 'ok', 'time' => $row['now']]);
    } catch (Throwable $e) {
        err('DB connection failed', 500, ['details' => $e->getMessage()]);
    }
}

// Add more routes here later (e.g. POST /users, POST /login)

// Fallback → 404
err('Not found', 404);
