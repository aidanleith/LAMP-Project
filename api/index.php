<?php
// api/index.php
require __DIR__ . '/response.php';
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If using Apache at /api, set $base = '/api'.
// If running locally with: php -S 127.0.0.1:8000 -t api api/index.php
// then keep $base = ''.
$base = '/contact_manager/api';
if ($base && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

function read_json() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) err('Invalid or missing JSON body', 400);
    return $data;
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

/* -------------------- AUTH -------------------- */

// POST /users → register (login + password required, names optional)
if ($path === '/users' && $method === 'POST') {
    $in = read_json();

    if (empty($in['login']) || empty($in['password'])) {
        err('login and password required', 422);
    }

    // optional fields (empty string if not provided)
    $first  = isset($in['firstName']) ? trim((string)$in['firstName']) : '';
    $last   = isset($in['lastName'])  ? trim((string)$in['lastName'])  : '';

    // check for duplicate login
    $dup = db()->prepare('SELECT 1 FROM users WHERE login = ?');
    $dup->execute([$in['login']]);
    if ($dup->fetch()) err('Login already exists', 409);

    $hash = password_hash($in['password'], PASSWORD_DEFAULT);

    // try inserting into password_hashed, else fall back to password
    try {
        $stmt = db()->prepare(
            'INSERT INTO users (firstName, lastName, login, password_hashed)
             VALUES (?,?,?,?)'
        );
        $stmt->execute([$first, $last, $in['login'], $hash]);
    } catch (Throwable $e) {
        $stmt = db()->prepare(
            'INSERT INTO users (firstName, lastName, login, password)
             VALUES (?,?,?,?)'
        );
        $stmt->execute([$first, $last, $in['login'], $hash]);
    }

    ok([
        'id'        => (int)db()->lastInsertId(),
        'login'     => $in['login'],
        'firstName' => $first,
        'lastName'  => $last
    ], 201);
}

// POST /login → authenticate user
if ($path === '/login' && $method === 'POST') {
    $in = read_json();
    if (empty($in['login']) || empty($in['password'])) {
        err('login and password required', 422);
    }

    $stmt = db()->prepare(
        'SELECT id, firstName, lastName, login, password_hashed, password
         FROM users WHERE login = ? LIMIT 1'
    );
    $stmt->execute([$in['login']]);
    $u = $stmt->fetch();
    if (!$u) err('Invalid credentials', 401);

    $stored = $u['password_hashed'] ?: ($u['password'] ?? '');
    $isHash = password_get_info((string)$stored)['algo'] ? true : false;

    $valid  = $isHash ? password_verify($in['password'], (string)$stored)
                      : hash_equals((string)$stored, (string)$in['password']);

    if (!$valid) err('Invalid credentials', 401);

    ok([
        'userId'    => (int)$u['id'],
        'login'     => $u['login'],
        'firstName' => $u['firstName'],
        'lastName'  => $u['lastName']
    ]);
}

/* -------------------- FALLBACK -------------------- */

err('Not found', 404);
