<?php
// searchContacts.php

header("Content-Type: application/json");

// Database connection settings
$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get search query from request
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;

// Search in contacts table (using firstName, lastName, and email)
$results = [];

// The SQL query now filters by user_id
$sql = "SELECT id, first_name, last_name, phone_number, email FROM contacts WHERE user_id = {$userId} AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR phone_number LIKE '%$search%')";
$res = $conn->query($sql);

// If query executed successfully, fetch rows into $results
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $row['firstName'] = $row['first_name'];
        $row['lastName'] = $row['last_name'];
        $row['phoneNumber'] = $row['phone_number'];
        unset($row['first_name'], $row['last_name'], $row['phone_number']);
        $results[] = $row;
    }
}

echo json_encode($results);

$conn->close();
?>