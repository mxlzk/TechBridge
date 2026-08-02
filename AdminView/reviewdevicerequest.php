<?php
session_start();
include __DIR__ . '/../config.php';

$requestGroupId = $_GET['request_group_id'] ?? '';
$userId = $_GET['user'] ?? '';
$successMessage = '';
$errorMessage = '';

// Handle POST request to update status safely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['request_status'] ?? '';
    $postedGroupId = $_POST['request_group_id'] ?? '';

    if (!empty($newStatus) && !empty($postedGroupId)) {
        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE device_requests SET request_status = ? WHERE request_group_id = ?"
        );
        mysqli_stmt_bind_param($updateStmt, "ss", $newStatus, $postedGroupId);

        if (mysqli_stmt_execute($updateStmt)) {
            $_SESSION["success"] = "Request #{$postedGroupId} updated successfully.";
            header("Location: devicerequestmanagementview.php");
            exit();
        } else {
            $_SESSION["error"] = "Error updating request status. Please try again.";
            header("Location: devicerequestmanagementview.php");
            exit();
        }
        mysqli_stmt_close($updateStmt);
    } else {
        $errorMessage = "Invalid input for updating status.";
    }
}

// Fetch request details
$stmt = $conn->prepare("SELECT dr.*, d.device_name, d.device_image, d.device_type FROM device_requests dr INNER JOIN devices d ON dr.device_id = d.device_id WHERE dr.request_group_id = ? ORDER BY dr.request_id ASC");
$stmt->bind_param("s", $requestGroupId);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
$currentStatus = '';

while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
    // Assume all items in a group share the same status, grab the first one
    if (empty($currentStatus) && isset($row['request_status'])) {
        $currentStatus = $row['request_status'];
    }
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Device Request</title>
    <style>
        .page-title {
            font-weight: 700;
        }

        .page-subtitle {
            color: #6c757d;
        }

        .device-card {
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .device-card img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-right: 16px;
            border-radius: 14px;
            background: #f8f9fa;
        }

        .card-custom {
            background-color: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
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

        .status-review {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Green */
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
    <?php include 'adminnavbar.php'; ?>
    <div class="container py-4">
        <div class="page-header mb-4">
            <h2 class="page-title"><i class="fa-solid fa-file-circle-exclamation me-2"></i>Review Device Request</h2>
            <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Review and manage device requests statuses</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Request Information -->
                <div class="card-custom mb-4">
                    <h5 class="mb-4"><i class="fa-solid fa-user me-2"></i>Device Request Information</h5>
                    <?php $request = $devices[0]; ?>
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <i class="fa-solid fa-tag me-2"></i><small class="text-muted">Request Group</small>
                            <div><?= htmlspecialchars($request['request_group_id']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <i class="fa-solid fa-credit-card me-2"></i><small class="text-muted">Payment Method</small>
                            <div><?= htmlspecialchars($request['payment_method']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <i class="fa-solid fa-layer-group me-2"></i><small class="text-muted">Rental Category</small>
                            <div><?= ucfirst(htmlspecialchars($request['rental_category'])) ?></div>
                        </div>

                        <div class="col-md-6">
                            <i class="fa-solid fa-calendar-alt me-2"></i><small class="text-muted">Duration</small>
                            <div><?= htmlspecialchars($request['rental_duration']) ?> Years</div>
                        </div>

                        <div class="col-md-6">
                            <i class="fa-solid fa-calendar-check me-2"></i><small class="text-muted">Created</small>
                            <div><?= date('d M Y', strtotime($request['created_at'])) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Devices in Request -->
                <div class="card-custom">
                    <h5 class="mb-4"><i class="fa-solid fa-laptop me-2"></i>Requested Devices</h5>
                    <?php foreach ($devices as $device): ?>
                        <div class="device-card">
                            <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($device['device_image']) ?>">
                            <div>
                                <h5><i class="fa-solid fa-laptop me-2"></i><?= htmlspecialchars($device['device_name']) ?></h5>
                                <div class="text-muted"><i class="fa-solid fa-layer-group me-2"></i> <?= htmlspecialchars($device['device_type']) ?> </div>
                                <div><i class="fa-solid fa-list me-2"></i>Quantity: <?= $device['quantity'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-custom">
                    <h5 class="mb-3">Update Status</h5>

                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <?php

                        $statusClass = match ($currentStatus) {
                            'Pending' => 'status-pending',
                            'Under Review' => 'status-review',
                            'Approved' => 'status-approved',
                            'Collected' => 'status-collected',
                            'Returned' => 'status-returned',
                            'Rejected' => 'status-rejected',
                            default => 'status-review'
                        }; 
                        
                        $statusIcon = match ($currentStatus) {
                            'Pending' => 'fa-solid fa-circle-info me-2',
                            'Under Review' => 'fa-solid fa-hourglass me-2',
                            'Approved' => 'fa-solid fa-circle-check me-2',
                            'Collected' => 'fa-solid fa-circle-arrow-up me-2',
                            'Returned' => 'fa-solid fa-circle-arrow-down me-2',
                            'Rejected' => 'fa-solid fa-circle-xmark me-2',
                            default => 'fa-solid fa-circle-info me-2'
                        }; 
                        ?>

                        <span class="status-badge <?= $statusClass ?>">
                            <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($currentStatus) ?>
                        </span>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="request_group_id" value="<?= htmlspecialchars($requestGroupId) ?>">

                        <div class="mb-3">
                            <label class="form-label">Request Status</label>
                            <select class="form-select" name="request_status">
                                <?php $statusList = ['Pending', 'Under Review', 'Approved', 'Collected', 'Returned', 'Rejected'];
                                foreach ($statusList as $status): ?>
                                    <option value="<?= $status ?>" <?= ($status == $currentStatus) ? 'selected' : '' ?>>
                                        <?= $status ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="mb-3 mt-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-check me-2"></i>Update Status</button>
                            <a href="devicerequestmanagementview.php" class="btn btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>
</body>

</html>