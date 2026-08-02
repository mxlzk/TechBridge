<?php
session_start();
include __DIR__ . '/../config.php';

// Handle form submission for editing a device
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceId = $_POST['device_id'] ?? '';
    $existingImage = $_POST['existing_image'] ?? '';

    // Validate the device ID
    if (!ctype_digit($deviceId)) {
        $_SESSION['error'] = 'Invalid device identifier.';
        header('Location: devicemanagementview.php');
        exit();
    }

    // Validate and sanitize input fields
    $device_name = trim($_POST['device_name'] ?? '');
    $device_os = trim($_POST['device_os'] ?? '');
    $device_type = trim($_POST['device_type'] ?? '');
    $device_color = trim($_POST['device_color'] ?? '');
    $device_storage = trim($_POST['device_storage'] ?? '');
    $device_specs = trim($_POST['device_specs'] ?? '');
    $device_price = trim($_POST['device_price'] ?? '');
    $device_status = trim($_POST['device_status'] ?? '');

    // Check for empty fields and validate price 
    if ($device_name === '' || $device_os === '' || $device_type === '' || $device_color === '' || $device_storage === '' || $device_specs === '' || $device_price === '' || $device_status === '' || !is_numeric($device_price)) {
        $_SESSION['error'] = 'Please fill in all required fields with valid values.';
        header('Location: editdevice.php?device_id=' . urlencode($deviceId));
        exit();
    }

    $imagePath = $existingImage;
    if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['device_image']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Image upload failed.';
            header('Location: editdevice.php?device_id=' . urlencode($deviceId));
            exit();
        }

        $uploadDirectory = 'deviceimages/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $imageExtension = strtolower(pathinfo($_FILES['device_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($imageExtension, $allowedExtensions, true)) {
            $_SESSION['error'] = 'Only JPG, JPEG, PNG, and WEBP files are allowed.';
            header('Location: editdevice.php?device_id=' . urlencode($deviceId));
            exit();
        }

        $imageName = uniqid('device_', true) . '.' . $imageExtension;
        $imagePath = $uploadDirectory . $imageName;

        if (!move_uploaded_file($_FILES['device_image']['tmp_name'], $imagePath)) {
            $_SESSION['error'] = 'Failed to upload image.';
            header('Location: editdevice.php?device_id=' . urlencode($deviceId));
            exit();
        }
    }

    // Update the device in the table
    $sql = 'UPDATE devices SET device_name = ?, device_os = ?, device_type = ?, device_color = ?, device_storage = ?, device_specs = ?, device_price = ?, device_image = ? , device_status = ? WHERE device_id = ?';
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = 'Database preparation failed.';
        header('Location: editdevice.php?device_id=' . urlencode($deviceId));
        exit();
    }

    $stmt->bind_param('ssssssdssi', $device_name, $device_os, $device_type, $device_color, $device_storage, $device_specs, $device_price, $imagePath, $device_status, $deviceId);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Device updated successfully.';
        header('Location: devicemanagementview.php');
        exit();
    }

    $_SESSION['error'] = 'Failed to update device.';
    header('Location: editdevice.php?device_id=' . urlencode($deviceId));
    exit();
}

// Validate the device_id from the query string
$deviceId = $_GET['device_id'] ?? '';
if (!ctype_digit($deviceId)) {
    header('Location: devicemanagementview.php');
    exit();
}

// Fetch the existing device details to pre-fill the form
$sql = 'SELECT * FROM devices WHERE device_id = ? LIMIT 1';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: devicemanagementview.php');
    exit();
}

// Bind the device ID parameter and execute the query
$stmt->bind_param('i', $deviceId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $device = $result->fetch_assoc();
} else {
    header('Location: devicemanagementview.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit device</title>
    <style>
        .page-wrapper {
            padding: 40px 0;
        }

        .edit-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: #212529;
        }

        .section-subtitle {
            color: #6c757d;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #212529;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .image-preview-container {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            height: 100%;
        }

        .device-preview-image {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .upload-note {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 10px;
        }

        .info-badge {
            display: inline-block;
            background: #eef2f7;
            color: #495057;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 10px;
        }

        .btn-action {
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>

    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>

            <div class="edit-card">
                <div class="mb-4">
                    <h2 class="section-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Device </h2>
                    <p class="section-subtitle"><i class="fa-solid fa-circle-info me-2"></i> Update and manage device information </p>
                    <div class="info-badge"><i class="fa-solid fa-pen-to-square me-2"></i>Editing: <?= htmlspecialchars($device['device_name']); ?> </div>
                </div>

                <form action="editdevice.php" method="POST" enctype="multipart/form-data"> <input type="hidden" name="device_id" value="<?= htmlspecialchars($device['device_id']); ?>">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($device['device_image']); ?>">
                    <div class="row g-5">

                        <div class="col-lg-4">
                            <div class="image-preview-container"> <img src="<?= htmlspecialchars($device['device_image']); ?>" alt="<?= htmlspecialchars($device['device_name']); ?>" class="device-preview-image">
                                <div class="mt-4"> <label for="device_image" class="form-label"><i class="fa-solid fa-image me-2"></i>Replace Device Image </label> <input type="file" class="form-control" id="device_image" name="device_image" accept=".jpg,.jpeg,.png,.webp">
                                    <div class="upload-note"><i class="fa-solid fa-circle-info me-2"></i>Supported formats: JPG, PNG, WEBP </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="device_name" class="form-label"><i class="fa-solid fa-laptop me-2"></i>Device Name </label>
                                    <input type="text" class="form-control" id="device_name" name="device_name" value="<?= htmlspecialchars($device['device_name']); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="device_os" class="form-label"><i class="fa-brands fa-apple me-2"></i>Operating System </label>
                                    <select class="form-select" id="device_os" name="device_os" required>
                                        <option disabled selected>Select OS</option>
                                        <?php $osOptions = ['iOS','Android','Windows','Other'];
                                        foreach ($osOptions as $os): ?>
                                            <option value="<?= $os; ?>" <?= ($device['device_os'] === $os) ? 'selected' : ''; ?>> <?= $os; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="device_type" class="form-label"><i class="fa-solid fa-mobile-screen me-2"></i>Device Type </label>
                                    <select class="form-select" id="device_type" name="device_type" required>
                                        <option disabled selected>Select Type</option>
                                        <?php $deviceTypes = ['Smartphone', 'Tablet', 'Laptop', 'Desktop', 'Wearable', 'Other'];
                                        foreach ($deviceTypes as $type): ?>
                                            <option value="<?= $type; ?>" <?= ($device['device_type'] === $type) ? 'selected' : ''; ?>> <?= $type; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="device_color" class="form-label"><i class="fa-solid fa-palette me-2"></i>Device Color </label>
                                    <input type="text" class="form-control" id="device_color" name="device_color" value="<?= htmlspecialchars($device['device_color']); ?>" required>
                                </div> 
                                
                                <div class="col-md-6"> 
                                    <label for="device_storage" class="form-label"><i class="fa-solid fa-database me-2"></i>Storage Capacity </label> 
                                    <select class="form-select" id="device_storage" name="device_storage" required>
                                        <option disabled selected>Select Storage</option>
                                        <?php $deviceStorages = ['64GB', '128GB', '256GB', '512GB', '1TB', 'Other'];
                                        foreach ($deviceStorages as $storage): ?>
                                            <option value="<?= $storage; ?>" <?= ($device['device_storage'] === $storage) ? 'selected' : ''; ?>> <?= $storage; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> 
                                
                                <div class="col-12"> 
                                    <label for="device_specs" class="form-label"><i class="fa-solid fa-microchip me-2"></i>Device Specifications </label> 
                                    <textarea class="form-control" id="device_specs" name="device_specs" rows="5" required><?= htmlspecialchars($device['device_specs']); ?></textarea> 
                                </div> 
                                
                                <div class="col-md-6"> 
                                    <label for="device_price" class="form-label"><i class="fa-solid fa-money-bill me-2"></i>Device Price (RM) </label> 
                                    <input type="number" step="0.01" class="form-control" id="device_price" name="device_price" value="<?= htmlspecialchars($device['device_price']); ?>" required> 
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa-solid fa-toggle-on me-2"></i>Device Status</label>
                                    <select name="device_status" class="form-select" required>
                                        <option disabled selected>Select Device Status</option>
                                        <?php $deviceStatus = ['Available', 'Unavailable'];
                                        foreach ($deviceStatus as $status): ?>
                                            <option value="<?= $status; ?>" <?= ($device['device_status'] === $status) ? 'selected' : ''; ?>> <?= $status; ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-center gap-3 mt-5">
                                <button type="submit" class="btn btn-primary btn-action"><i class="fa-solid fa-floppy-disk me-2"></i>Update Device </button>
                                <a href="devicemanagementview.php" class="btn btn-outline-secondary btn-action"><i class="fa-solid fa-reply me-2"></i>Return </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> 
    <?php include 'adminfooter.php'; ?>
    <script>

    </script>
</body>
</html>