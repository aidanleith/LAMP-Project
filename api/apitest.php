<?php

    // Show all errors, very important for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // --- Your Connection Credentials ---
    $db_host = "localhost";
    $db_user = "group16";
    $db_pass = "welovegroup16";
    $db_name = "COP4331_lamp_group_16";
    // --------------------------------

    echo "<h1>Database Connection Test</h1>";
    echo "<p>Attempting to connect to database with the following credentials:</p>";
    echo "<ul>";
    echo "<li>Host: " . $db_host . "</li>";
    echo "<li>User: " . $db_user . "</li>";
    echo "<li>Password: " . str_repeat('*', strlen($db_pass)) . "</li>"; // Hides password for security
    echo "<li>Database: " . $db_name . "</li>";
    echo "</ul>";
    echo "<hr>";


    // Create connection object
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) 
    {
        // If connection fails, print the error and stop the script
        die("<h2 style='color:red;'>❌ CONNECTION FAILED</h2><p><strong>Error Message:</strong> " . $conn->connect_error . "</p>");
    }

    // If the script reaches this line, the connection was successful
    echo "<h2 style='color:green;'>✅ CONNECTION SUCCESSFUL!</h2>";
    echo "<p>Your PHP script can successfully connect to the MySQL database.</p>";

    // Close the connection
    $conn->close();

?>