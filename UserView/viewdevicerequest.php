<?php
session_start();
require_once __DIR__ . '/../config.php';

// Get the user id and request group id from the session and request
$userId = (int) ($_SESSION['user_id'] ?? 0);
$requestGroupId = $_GET['group'] ?? '';

// Check if the request group id and user id is valid
if (empty($requestGroupId) || empty($userId)) {
    $_SESSION['error'] = "Invalid Request.";
    header("Location: reqstatus.php");
    exit();
}

// Fetch request details
$stmt = $conn->prepare("SELECT dr.*, d.device_name, d.device_image, d.device_type FROM device_requests dr INNER JOIN devices d ON d.device_id = dr.device_id WHERE dr.request_group_id = ? AND dr.user_id = ? ");

// Bind parameters
$stmt->bind_param("si", $requestGroupId, $userId);
$stmt->execute();
$result = $stmt->get_result();

// Initialize devices array
$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

// Check if the request is valid
if (empty($devices)) {
    $_SESSION['error'] = "Invalid Request.";
    header("Location: reqstatus.php");
    exit();
}

// Store the request info
$requestInfo = $devices[0];

// Count the total number of devices
$totalDevices = 0;
foreach ($devices as $device) {
    $totalDevices += $device['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View device</title>
    <style>
        .request-header {
            background: #fff;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        .device-card {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        .device-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 5px;
        }

        .info-label {
            color: #6c757d;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: .5rem 1rem;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        .status-pending {
            background: #fff3cd;
            color: #9d7712;
        }

        .status-under-review {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-collected {
            background: #cce5ff;
            color: #004085;
        }

        .status-returned {
            background: #d1e7dd;
            color: #146c43;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php'; ?>

    <div class="content">
        <div class="container py-4">
            <!-- Header -->
            <div class="request-header ">
                <div class="mb-4">
                    <h2 class="fw-bold"><i class="fa-solid fa-box-open me-2"></i>Device Request</h2>
                    <p><i class="fa-solid fa-id-card me-2"></i>Group ID:
                        <?= htmlspecialchars($requestInfo['request_group_id']); ?></p>
                </div>
                <?php
                $status = $requestInfo['request_status'];
                $statusText = ucfirst($status);
                $statusClass = match ($status) {
                    'Pending' => 'status-pending',
                    'Under Review' => 'status-under-review',
                    'Approved' => 'status-approved',
                    'Collected' => 'status-collected',
                    'Returned' => 'status-returned',
                    'Rejected' => 'status-rejected',
                    default => 'status-pending',
                };

                $statusIcon = match ($statusText) {
                    'Pending' => 'fa-solid fa-spinner me-2',
                    'Under Review' => 'fa-solid fa-eye me-2',
                    'Approved' => 'fa-solid fa-check me-2',
                    'Collected' => 'fa-solid fa-box-open me-2',
                    'Returned' => 'fa-solid fa-undo me-2',
                    'Rejected' => 'fa-solid fa-times me-2',
                    default => 'fa-solid fa-spinner me-2',
                };
                ?>
                <span class="status-badge <?= $statusClass ?>"><i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($requestInfo['request_status']); ?></span>

                <h4 class="mb-3 mt-4"><i class="fa-solid fa-info-circle me-2"></i>Rental Information</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa-solid fa-tag me-2"></i>Rental Category</span>
                    <strong><?= ucfirst($requestInfo['rental_category']); ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa-solid fa-calendar-days me-2"></i>Rental Duration</span>
                    <strong><?= $requestInfo['rental_duration']; ?> Years</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa-solid fa-wallet me-2"></i>Payment Method</span>
                    <strong><?= htmlspecialchars($requestInfo['payment_method']); ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa-solid fa-calendar-check me-2"></i>Submitted</span>
                    <strong><?= date('d M Y', strtotime($requestInfo['created_at'])); ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fa-solid fa-box-open me-2"></i>Total Devices</span>
                    <strong><?= $totalDevices; ?></strong>
                </div>
            </div>
            <div class="row">
                <?php foreach ($devices as $device): ?>
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="device-card">
                            <div class="text-center mb-3">
                                <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($device['device_image']); ?>"
                                    alt="<?= htmlspecialchars($device['device_name']); ?>" class="device-image">
                            </div>

                            <h3 class="fw-bold text-center"><i class="fa-solid fa-device me-2"></i><?= htmlspecialchars($device['device_name']); ?></h3>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label"><i class="fa-solid fa-tags me-2"></i>Device Type</span>
                                <strong><?= htmlspecialchars($device['device_type']); ?></strong>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="info-label"><i class="fa-solid fa-hashtag me-2"></i>Quantity</span>
                                <strong><?= $device['quantity']; ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="reqstatus.php" class="btn btn-outline-secondary mb-4">Return</a>
        </div>
    </div>
    
    <?php include 'userfooter.php'; ?>
</body>

</html>