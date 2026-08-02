<?php
session_start();
// Start the session and include the database configuration
require_once __DIR__ . '/../config.php';

// Check if the device_id is set and is a valid number
if (isset($_GET['device_id']) && is_numeric($_GET['device_id'])) {
    $device_id = $_GET['device_id'];

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM devices WHERE device_id = ?");
    $stmt->bind_param("i", $device_id);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $_SESSION['success'] = "Device deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting device: " . $stmt->error;
    }
    // Close the statement
    $stmt->close();

} else {
    // If device_id is not set or is not a valid number, set an error message
    $_SESSION['error'] = "Invalid device ID specified.";
}

// Close the database connection
$conn->close();
header("Location: devicemanagementview.php");
exit();
?>