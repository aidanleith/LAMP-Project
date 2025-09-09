<?php
// api/db.php

$DB_HOST = 'localhost';       // usually 'localhost'
$DB_NAME = 'COP4331_lamp_group_16';         // your database name
$DB_USER = 'group16';    // ask your DB teammate for this
$DB_PASS = 'welovegroup16';    // ask your DB teammate for this

function db(): PDO {
    static $pdo = null;
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    if ($pdo === null) {
        $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed',
                'details' => $e->getMessage()
            ]);
            exit;
        }
    }
    return $pdo;
}
