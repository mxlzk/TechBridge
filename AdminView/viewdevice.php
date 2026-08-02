<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_GET['device_id']) || !ctype_digit($_GET['device_id'])) {
    header('Location: devicemanagementview.php');
    exit();
}

// Fetch device details securely using prepared statements
$device_id = $_GET['device_id'];
$sql = "SELECT * FROM devices WHERE device_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $device_id);
$stmt->execute();
$result = $stmt->get_result();
$device = $result->fetch_assoc();

// If device not found, redirect back to device management view
if (!$device) {
    header('Location: devicemanagementview.php');
    exit();
}

$isUnavailable = ($device['device_status'] === 'Unavailable');

$deviceIcon = match ($device['device_type']) {
    'Smartphone' => 'fa-solid fa-mobile-screen-button me-2',
    'Tablet' => 'fa-solid fa-tablet-screen-button me-2',
    'Laptop' => 'fa-solid fa-laptop me-2',
    default => 'fa-solid fa-box me-2'
};

$osIcon = match ($device['device_os']) {
    'Android' => 'fa-brands fa-android me-2',
    'iOS' => 'fa-brands fa-apple me-2',
    'Windows' => 'fa-brands fa-windows me-2',
    default => 'fa-solid fa-box me-2'
};

$deviceStatus = match ($device['device_status']) {
    'Available' => 'text-success fa-solid fa-circle-check me-2',
    'Unavailable' => 'text-danger fa-solid fa-ban me-2',
    default => 'text-warning fa-solid fa-circle-question me-2'
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View device</title>
    <style>
        .page-header {
            margin-bottom: 35px;
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }

        .device-wrapper {
            padding: 40px 0;
        }

        .device-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }

        .device-image-container {
            background: #f8f9fa;
            border-radius: 20px;
            overflow: hidden;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .device-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .device-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 18px;
        }

        .device-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 25px;
        }

        .device-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .device-meta .badge {
            background-color: #eef2f7;
            color: #495057;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
        }

        .info-card {
            border-radius: 16px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 6px 35px rgba(0, 0, 0, 0.25);
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }

        .specification-box {
            background: #f8fafc;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 6px 35px rgba(0, 0, 0, 0.25);
        }

        .specification-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #212529;
        }

        .specification-text {
            color: #495057;
            line-height: 1.7;
            white-space: pre-line;
        }

        .action-buttons {
            margin-top: 35px;
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

            <div class="page-header">
                <h2 class="page-title"><i class="fa-solid fa-eye me-2"></i>View Device</h2>
                <p class="page-subtitle"><i class="fa-solid fa-info-circle me-2"></i>View detailed information about a specific device</p>
            </div>

            <?php if ($isUnavailable): ?>
                <div class="alert alert-warning mt-3"><i class="fa-solid fa-ban me-2"></i>This device is currently unavailable for rental.</div>
            <?php endif; ?>

            <div class="device-card">
                <div class="row g-5 align-items-start">

                    <!-- Device Image -->
                    <div class="col-lg-5">
                        <div class="device-image-container">
                            <img src="<?= htmlspecialchars($device['device_image']); ?>" alt="<?= htmlspecialchars($device['device_name']); ?>" class="device-image">
                        </div>
                    </div>

                    <!-- Device Details -->
                    <div class="col-lg-7">
                        <!-- Device Title -->
                        <h1 class="device-title"><i class="<?= $deviceIcon ?>"></i> <?= htmlspecialchars($device['device_name']); ?></h1>

                        <!-- Device Price -->
                        <div class="device-price"><i class="fa-solid fa-coins me-2"></i>RM <?= number_format($device['device_price'], 2); ?></div>

                        <!-- Device Metadata -->
                        <div class="device-meta">
                            <span class="badge"><i class="<?= $osIcon ?>"></i><?= htmlspecialchars($device['device_os']); ?></span>
                            <span class="badge"><i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($device['device_type']); ?></span>
                            <span class="badge"><i class="fa-solid fa-database me-2"></i><?= htmlspecialchars($device['device_storage']); ?></span>
                            <span class="badge"><i class="<?= $deviceStatus?>"></i><?= htmlspecialchars($device['device_status']); ?></span>
                        </div>
                        
                        <!-- device Information Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label"> Operating System</div>
                                    <div class="info-value"><i class="<?= $osIcon ?>"></i><?= htmlspecialchars($device['device_os']); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Device Type</div>
                                    <div class="info-value"><i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($device['device_type']); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Device Color</div>
                                    <div class="info-value"><i class="fa-solid fa-palette me-2"></i><?= htmlspecialchars($device['device_color']); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label">Storage Capacity</div>
                                    <div class="info-value"><i class="fa-solid fa-database me-2"></i><?= htmlspecialchars($device['device_storage']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="specification-box">
                            <div class="specification-title"><i class="fa-solid fa-list-check me-2"></i> Device Specifications</div>
                            <div class="specification-text"><?= nl2br(htmlspecialchars($device['device_specs'])); ?></div>
                        </div>

                        <div class="action-buttons d-flex flex-wrap gap-3">
                            <a href="devicemanagementview.php" class="btn btn-outline-secondary btn-action"><i class="fa-solid fa-arrow-left"></i> Return</a>
                            <a href="exportdevice.php?device_id=<?= htmlspecialchars($device['device_id']); ?>" class="btn btn-outline-dark btn-action"><i class="fa-solid fa-download"></i> Export device</a>
                            <a href="editdevice.php?device_id=<?= htmlspecialchars($device['device_id']); ?>" class="btn btn-primary btn-action"><i class="fa-solid fa-pen"></i> Edit device</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>
</body>