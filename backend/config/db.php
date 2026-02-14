<?php
// Database Configuration
$host = '127.0.0.1'; // Changed from localhost to force TCP
$db_name = 'e_clearance_db';
$username = 'root';
$password = ''; // Default XAMPP password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If database doesn't exist, try connecting without dbname to create it
    try {
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $conn->exec("USE `$db_name`");
    } catch(PDOException $e2) {
        echo "Connection failed: " . $e2->getMessage();
        die();
    }
}
?>
