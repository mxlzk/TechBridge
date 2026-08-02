<?php

// Database configuration
$conn = new mysqli("localhost", "root", "", "techbridge");

// Check database connection for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

define('BASE_URL', '/TechBridge');
define('LOGIN_PAGE', BASE_URL . '/loginaccount.php');

?>