<?php

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: /TechBridge/loginaccount.php");
    exit();
}

// Check if the user has the required role
if (isset($requiredRole)) {

    // Ensure $requiredRole is an array for consistent checking
    if (!is_array($requiredRole)) {
        $requiredRole = [$requiredRole];
    }

    // If the user's role is not in the required roles array, redirect them
    if (!in_array($_SESSION['role'], $requiredRole)) {
        if ($_SESSION['role'] === "admin") {
            $destination = BASE_URL . "/AdminView/admindashboard.php";
            header("Location: " . $destination);
        } else if ($_SESSION['role'] === "user") {
            $destination = BASE_URL . "/UserView/userdashboard.php";
            header("Location: " . $destination);
        } else if ($_SESSION['role'] === "supplier") {
            $destination = BASE_URL . "/SupplierView/supplierdashboard.php";
            header("Location: " . $destination);
        }
        exit();
    }
}

?>