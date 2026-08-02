<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate and sanitize input fields
    $device_name = trim($_POST['device_name'] ?? '');
    $device_os = trim($_POST['device_os'] ?? '');
    $device_type = trim($_POST['device_type'] ?? '');
    $device_color = trim($_POST['device_color'] ?? '');
    $device_storage = trim($_POST['device_storage'] ?? '');
    $device_specs = trim($_POST['device_specs'] ?? '');
    $device_price = trim($_POST['device_price'] ?? '');

    // Validate required fields
    if (empty($device_name) || empty($device_os) || empty($device_type) || empty($device_color) || empty($device_storage) || empty($device_specs) || empty($device_price)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: adddevice.php");
        exit();
    }

    // Validate device price
    if (!is_numeric($device_price) || $device_price < 0) {
        $_SESSION['error'] = "Please enter a valid device price.";
        header("Location: adddevice.php");
        exit();
    }

    // Validate and sanitize uploaded image

    if (!isset($_FILES['device_image']) || $_FILES['device_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Please upload a valid device image.";
        header("Location: adddevice.php");
        exit();
    }

    // Validate File Size
    $maxFileSize = 5 * 1024 * 1024;

    if ($_FILES['device_image']['size'] > $maxFileSize) {
        $_SESSION['error'] = "Image size must not exceed 5MB.";
        header("Location: adddevice.php");
        exit();
    }

    // Validate MIME Type
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['device_image']['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        $_SESSION['error'] = "Only JPG, PNG, and WEBP images are allowed.";
        header("Location: adddevice.php");
        exit();
    }

    $uploadDirectory = __DIR__ . '/deviceimages/';
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    $imageExtension = pathinfo($_FILES['device_image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('device_', true) . '.' . $imageExtension;
    $imageFullPath = $uploadDirectory . $imageName;

    if (!move_uploaded_file($_FILES['device_image']['tmp_name'], $imageFullPath)) {
        $_SESSION['error'] = "Failed to upload image.";
        header("Location: adddevice.php");
        exit();
    }

    $databaseImagePath = 'deviceimages/' . $imageName;
    $sql = "INSERT INTO devices (device_name, device_os, device_type, device_color, device_storage, device_specs, device_price, device_image, device_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        unlink($imageFullPath);
        $_SESSION['error'] = "Database preparation failed.";
        header("Location: adddevice.php");
        exit();
    }

    $stmt->bind_param("ssssssdss", $device_name, $device_os, $device_type, $device_color, $device_storage, $device_specs, $device_price, $databaseImagePath, $device_status);

    $device_status = "Available";

    if ($stmt->execute()) {
        $_SESSION['success'] = "Device ID $deviceId added successfully.";
        header("Location: devicemanagementview.php");
        exit();
    }

    unlink($imageFullPath);
    $_SESSION['error'] = "Failed to add device.";
    header("Location: adddevice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Device</title>
    <style>
        .page-container {
            padding: 40px 20px;
        }

        .form-card {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #6c757d;
            margin-bottom: 35px;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            min-height: 50px;
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn {
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
        }

        .upload-note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 6px;
        }

        .form-control[type="file"] {
            padding: 10px;
        }

        .form-control[type="file"]::file-selector-button {
            margin-right: 15px;
            border: none;
            height: 40px;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="page-container">
        <div class="form-card">
            <?php include '../includes/session_messages.php'; ?>

            <!-- Heading -->
            <div class="mb-4 text-center">
                <h2 class="page-title">Add New Device</h2>
                <p class="page-subtitle">Create a new device for TechBridge</p>
            </div>

            <form action="adddevice.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="device_name" class="form-label">Device Name</label>
                        <input type="text" class="form-control" id="device_name" name="device_name" value="<?= htmlspecialchars($_POST['device_name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="device_os" class="form-label">Operating System</label>
                        <select class="form-select" id="device_os" name="device_os" required>
                            <option value="">Operating System</option>
                            <?php $deviceOS = ['Android', 'iOS', 'Windows', 'macOS', 'Linux'];
                            foreach ($deviceOS as $os): ?>
                            <option value="<?= $os ?>" <?= isset($_POST['device_os']) && $_POST['device_os'] === $os ? 'selected' : '' ?>>
                                <?= $os ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="device_type" class="form-label">Device Type</label>
                        <select class="form-select" id="device_type" name="device_type" required>
                            <option value="">Device Type</option>
                            <?php $deviceTypes = ['Smartphone', 'Tablet', 'Laptop', 'Desktop', 'Wearable', 'Others'];
                            foreach ($deviceTypes as $type): ?>
                                <option value="<?= $type ?>" <?= (($_POST['device_type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="device_color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="device_color" name="device_color" value="<?= htmlspecialchars($_POST['device_color'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="device_storage" class="form-label">Storage</label>
                        <select name="device_storage" id="device_storage" class="form-select" required>
                            <option value="">Select Storage</option>
                            <?php $storages = ['64GB', '128GB', '256GB', '512GB', '1TB'];
                            foreach ($storages as $storage): ?>
                            <option value="<?= $storage ?>" <?= isset($_POST['device_storage']) && $_POST['device_storage'] === $storage ? 'selected' : '' ?>>
                                <?= $storage ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="device_price" class="form-label">Device Price</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="device_price" name="device_price" value="<?= htmlspecialchars($_POST['device_price'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Specifications -->
                <div class="mb-3">
                    <label for="device_specs" class="form-label">Specifications</label>
                    <textarea class="form-control" id="device_specs" name="device_specs" required><?= htmlspecialchars($_POST['device_specs'] ?? '') ?></textarea>
                </div>

                <!-- device Image -->
                <div class="mb-4 text-center">
                    <label for="device_image" class="form-label d-block">Device Image</label>
                    <input type="file" class="form-control" id="device_image" name="device_image" accept=".jpg,.jpeg,.png,.webp" required>
                    <div class="upload-note mt-2">Supported formats: JPG, PNG, WEBP (Max 5MB)</div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-center gap-3">
                    <button type="submit" class="btn btn-primary">Add device</button>
                    <a href="devicemanagementview.php" class="btn btn-outline-secondary">Return</a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'adminfooter.php'; ?>
</body>

</html>