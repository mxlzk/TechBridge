<?php 
session_start();
include __DIR__ . '/../config.php';

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $userId = (int) $_GET['user_id'];

    // Prevent users from deleting their own account
    if ($userId == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account while you're logged in.";
        header("Location: usermanagementview.php");
        exit();
    }

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User has been deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting user: " . $stmt->error;
    }
    // Close the statement and database connection
    $stmt->close();
    $conn->close();

    header("Location: usermanagementview.php");
    exit();
} else {
    $_SESSION['error'] = "No user ID specified.";
    header("Location: usermanagementview.php");
    exit();
}

?>