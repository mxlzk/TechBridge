<?php
session_start();
require_once __DIR__ . "/../config.php";

$orderId = trim($_GET['order_id'] ?? '');
$supplierId = (int) $_SESSION['user_id'];

if (empty($orderId)) {
    $_SESSION['error'] = "Invalid order.";
    header("Location: deviceordersmanagementview.php");
    exit();
}

$sql = "SELECT do.*, d.device_name, d.device_image, d.device_type FROM device_orders do INNER JOIN devices d ON do.device_id = d.device_id WHERE do.order_id = ? AND do.supplier_id = ? ORDER BY do.order_item_id ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Error preparing statement: " . mysqli_error($conn));
    die("Unable to retrieve order information at the moment. Please try again later.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderStatus = trim($_POST['order_status'] ?? '');
    $remarks = trim($_POST['order_remarks'] ?? '');

    $allowedStatus = ['Pending', 'Approved', 'Rejected', 'Preparing', 'Shipped', 'Delivered'];

    if (!in_array($orderStatus, $allowedStatus)) {
        $_SESSION['error'] = "Invalid status.";
        header("Location: reviewdeviceorder.php");
        exit();
    }

    $updateSql = "UPDATE device_orders SET order_status = ?, supplier_remarks = ? WHERE order_id = ? AND supplier_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    if (!$updateStmt) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        die("Unable to update order status at the moment. Please try again later.");
    }

    mysqli_stmt_bind_param($updateStmt, "sssi", $orderStatus, $remarks, $orderId, $supplierId);
    if (!mysqli_stmt_execute($updateStmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($updateStmt));
        mysqli_stmt_close($updateStmt);
        die("Unable to update order status.");
    }

    mysqli_stmt_close($updateStmt);

    $_SESSION['success'] = "Order #{$orderId} status updated to {$orderStatus}.";
    header("Location: deviceordersmanagementview.php");
    exit();
}

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Error preparing statement: " . mysqli_error($conn));
    die("Unable to retrieve order #{$orderId} information at the moment. Please try again later.");
}

mysqli_stmt_bind_param($stmt, "si", $orderId, $supplierId);
if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute failed: " . mysqli_stmt_error($stmt));
    die("Unable to retrieve order information.");
}

$result = mysqli_stmt_get_result($stmt);

$orderItems = [];
$totalUnits = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $orderItems[] = $row;
    $totalUnits += $row['quantity'];
}

mysqli_stmt_close($stmt);

if (empty($orderItems)) {
    $_SESSION['error'] = "Order not found.";
    header("Location: deviceordersmanagementview.php");
    exit();
}

$orderInfo = $orderItems[0];

$currentStatus = $orderInfo['order_status'];
$statusClass = match ($currentStatus) {
    'Pending' => 'status-pending',
    'Approved' => 'status-approved',
    'Preparing' => 'status-preparing',
    'Shipped' => 'status-shipped',
    'Delivered' => 'status-delivered',
    'Rejected' => 'status-rejected',
    default => 'status-pending',
};

$statusIcon = match ($currentStatus) {
    'Pending' => '<i class="fa-solid fa-hourglass-half me-2"></i>',
    'Approved' => '<i class="fa-solid fa-check-circle me-2"></i>',
    'Preparing' => '<i class="fa-solid fa-box me-2"></i>',
    'Shipped' => '<i class="fa-solid fa-truck me-2"></i>',
    'Delivered' => '<i class="fa-solid fa-box-open me-2"></i>',
    'Rejected' => '<i class="fa-solid fa-ban me-2"></i>',
    default => '<i class="fa-solid fa-circle-question me-2"></i>',
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Device Order <?= htmlspecialchars($orderId) ?></title>
    <style>
        .supplier-title {
            font-weight: 700;
        }

        .supplier-subtitle {
            color: #6c757d;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .device-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 12px;
            background: #f8f9fa;
            padding: 5px;
        }

        .device-card {
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            gap:18px;
            border-radius:16px;
        }

        .card{
            border-radius: 18px;
        }

        .card-header{
            padding:1.5rem 1.5rem .75rem;
        }

        .card-body{
            padding:1.5rem;
        }

        .form-label{
            font-weight:600;
        }

        .status-badge{
            display:inline-flex;
            align-items:center;
            padding:.6rem 1rem;
            border-radius:999px;
            font-size:.9rem;
            font-weight:600;
        }

        .status-pending{
            background: #fff8e1;
            color: #8a6d1d
        }

        .status-approved{
            background: #edf7ed;
            color: #1e7e34;
        }

        .status-preparing{
            background: #e8f4fd;
            color: #0d6efd;
        }

        .status-shipped{
            background: #eef4ff;
            color: #3559e0;
        }

        .status-delivered{
            background: #e9f8ef;
            color: #198754;
        }

        .status-rejected{
            background: #fdeaea;
            color: #c82333;
        }
    </style>
</head>

<body>
    <?php include 'suppliernavbar.php'; ?>

    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div class="page-header-info">
                    <h2 class="supplier-title"><i class="fa-solid fa-box-open me-2"></i>Review Device Order
                        <?= htmlspecialchars($orderId) ?>
                    </h2>
                    <p class="supplier-subtitle text-muted"><i class="fa-solid fa-circle-info me-2"></i>Review device
                        order #<?= htmlspecialchars($orderId) ?></p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-info me-2"></i>Order Information</h5>
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <small class="text-muted"><i class="fa-solid fa-id-card me-2"></i>Order ID</small>
                                    <div class="fw-semibold"><?= htmlspecialchars($orderInfo['order_id']) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted"><i class="fa-solid fa-clock me-2"></i>Ordered At</small>
                                    <div class="fw-semibold"><?= htmlspecialchars(date("d M Y", strtotime($orderInfo['created_at']))) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted"><i class="fa-solid fa-box me-2"></i>Total Devices</small>
                                    <div class="fw-semibold"><?= htmlspecialchars(count($orderItems)) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted"><i class="fa-solid fa-tag me-2"></i>Total Units</small>
                                    <div class="fw-semibold"><?= htmlspecialchars($totalUnits) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted"><i class="fa-solid fa-user me-2"></i>Ordered By</small>
                                    <div class="fw-semibold"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="fw-bold mb-4"><i class="fa-solid fa-list me-2"></i>Requested Devices</h5>
                            <?php foreach ($orderItems as $item): ?>
                                <div class="device-card">
                                    <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($item['device_image']) ?>" class="device-image" alt="Device Image">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['device_name']) ?></h6>

                                        <div class="text-muted">
                                            <i class="fa-solid fa-mobile-screen me-2"></i>
                                            <?= htmlspecialchars($item['device_type']) ?>
                                        </div>

                                        <span class="badge bg-light text-dark">
                                            Units: <?= htmlspecialchars($item['quantity']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4 position-sticky">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="fw-bold mb-1"><i class="fa-solid fa-box-open me-2"></i>Update Order Status</h5>
                            <p class="text-muted small mb-0"><i class="fa-solid fa-circle-info me-2"></i>Update order status</p>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <label for="orderStatus" class="form-label fw-semibold">Order Status</label>
                                <div>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i><?= $statusIcon ?></i>
                                        <?= htmlspecialchars($orderInfo['order_status']) ?>
                                    </span>
                                </div>
                            </div>

                            <form action="" method="post">
                                <div class="mb-4">
                                    <label for="order_status" class="form-label"><i class="fa-solid fa-box-open me-2"></i>Order Status</label>
                                    <select class="form-select" id="order_status" name="order_status" required>
                                        <?php
                                        $statusList = ['Pending', 'Approved', 'Preparing', 'Shipped', 'Delivered', 'Rejected'];

                                        foreach ($statusList as $status):?>
                                            <option value="<?= htmlspecialchars($status) ?>" <?= $orderInfo['order_status'] == $status ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="fa-solid fa-comments me-2"></i>Supplier Remarks</label>
                                    <textarea name="order_remarks" rows="4" class="form-control" placeholder="Optional remarks for admin"><?= htmlspecialchars($orderInfo['supplier_remarks'] ?? '') ?></textarea>
                                </div>

                                <div class="alert alert-light border d-flex align-items-start">
                                    <i class="fa-solid fa-circle-info text-primary mt-1 me-2"></i>
                                    <small class="mb-0 text-muted">Updating this status will apply to every device included in this order.</small>
                                </div>

                                <div class="mb-3 mt-4">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-check me-2"></i>Update Status</button>
                                    <a href="deviceordersmanagementview.php" class="btn btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'supplierfooter.php'; ?>
</body>

</html>