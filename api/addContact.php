<?php
// addContact.php

header('Content-Type: application/json');

// Database connection settings
$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16"); 	


// Get POST data
$data = json_decode(file_get_contents('php://input'), true);


if (
    !isset($data['firstName']) ||
    !isset($data['lastName']) ||
    !isset($data['phoneNumber']) ||
    !isset($data['email']) 
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Connects the name to the database
$first_name = trim($data['firstName']);
$last_name = trim($data['lastName']);
$phone_number = trim($data['phoneNumber']);
$email = trim($data['email']);

try {

    //Create a new PDO connect
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare(
        "INSERT INTO contacts (first_name, last_name, phone_number, email) VALUES (:first_name, :last_name, :phone_number, :email)"
    );
    $stmt->execute([
        ':first_name'   => $first_name,
        ':last_name'    => $last_name,
        ':phone_number' => $phone_number,
        ':email'        => $email
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>