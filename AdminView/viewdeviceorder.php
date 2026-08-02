<?php
session_start();
include __DIR__ . '/../config.php';

// get order id from url
$orderId = $_GET['order_id'] ?? '';

// get order information
$stmt = $conn->prepare("SELECT do.*, d.device_name, d.device_image, d.device_type, d.device_price, u.username AS supplier_name FROM device_orders do INNER JOIN devices d ON do.device_id = d.device_id INNER JOIN users u ON do.supplier_id = u.user_id WHERE do.order_id = ? ORDER BY do.order_item_id ASC");

// bind parameters and execute
$stmt->bind_param("s", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$orderItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// check if order is empty 
if (empty($orderItems)) {
    $_SESSION['error'] = "Order not found.";
    header("Location: deviceordermanagementview.php");
    exit();
}

// get order information
$order = $orderItems[0];
$totalDevices = count($orderItems);

// calculate total units and grand total
$totalUnits = 0;
$grandTotal = 0;

// loop through order items to calculate total units and grand total
foreach ($orderItems as $item) {
    $totalUnits += $item['quantity'];
    $grandTotal += ($item['quantity'] * $item['device_price']);
}

// count total devices
$totalDevices = count($orderItems);

// get status color
$statusColor = match ($order['order_status']) {
    "Pending" => "warning",
    "Approved" => "primary",
    "Preparing" => "info",
    "Shipped" => "secondary",
    "Delivered" => "success",
    "Rejected" => "danger",
    default => "dark"
};

// get status icon
$statusIcon = match ($order['order_status']) {
    "Pending" => "fa-solid fa-clock me-2",
    "Approved" => "fa-solid fa-check-circle me-2",
    "Preparing" => "fa-solid fa-cog fa-spin me-2",
    "Shipped" => "fa-solid fa-truck me-2",
    "Delivered" => "fa-solid fa-box-open me-2",
    "Rejected" => "fa-solid fa-times-circle me-2",
    default => "fa-solid fa-question-circle me-2"
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Device Order</title>
    <style>
        .img-fluid {
            max-height: 120px;
            max-width: 120px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex flex-column">
                            <h3 class="fw-bold"><i class="fa-solid fa-file-invoice me-2"></i>Order Summary</h3>
                            <h5 class="text-primary mt-1"><?= htmlspecialchars($order['order_id']) ?></h5>

                            <p class="text-muted mb-0"><i class="fa-solid fa-user-tie me-2"></i>Assigned Supplier: <strong><?= htmlspecialchars($order['supplier_name']) ?></strong></p>
                        </div>
                        <span class="badge bg-<?= $statusColor ?> fs-6">
                            <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($order['order_status']) ?>
                        </span>
                    </div>

                    <hr class="my-4">

                    <div class="my-2">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="border rounded-3 p-3 text-center">
                                    <i class="fa-solid fa-box fa-2x text-primary"></i>
                                    <h5 class="mt-3"><?= $totalDevices ?></h5>
                                    <p class="text-muted mb-0">Device(s)</p>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-3 p-3 text-center">
                                    <i class="fa-solid fa-cubes fa-2x text-success"></i>
                                    <h5 class="mt-3"><?= $totalUnits ?></h5>
                                    <p class="text-muted mb-0">Unit(s)</p>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-3 p-3 text-center">
                                    <i class="fa-solid fa-money-bill fa-2x text-warning"></i>
                                    <h5 class="mt-3">RM<?= number_format($grandTotal, 2) ?></h5>
                                    <p class="text-muted mb-0">Grand Total</p>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-3 p-3 text-center">
                                    <i class="fa-solid fa-calendar fa-2x text-info"></i>
                                    <h6 class="mt-3"><?= date('d M Y', strtotime($order['created_at'])) ?></h6>
                                    <p class="text-muted mb-0">Ordered On</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3"><i class="fa-solid fa-box-open me-2"></i>Ordered Devices</h4>

                    <!--loop through order items-->
                    <?php foreach ($orderItems as $item): ?>
                        <?php
                        // get device type icon
                        $deviceTypeIcon = match ($item['device_type']) {
                            "Laptop" => "fa-solid fa-laptop me-2",
                            "Tablet" => "fa-solid fa-tablet-screen-button me-2",
                            "Smartphone" => "fa-solid fa-mobile-screen-button me-2",
                            default => "fa-solid fa-desktop me-2"
                        };
                        ?>
                        <div class="card mb-3 border rounded-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($item['device_image']) ?>" class="img-fluid">
                                    </div>

                                    <div class="col-md-6">
                                        <h5 class="fw-bold"><?= htmlspecialchars($item['device_name']) ?></h5>
                                        <span class="text-primary fw-bold"><i class="<?= $deviceTypeIcon ?>"></i><?= htmlspecialchars($item['device_type']) ?></span>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <strong class="fw-bold"><i class="fa-solid fa-cubes me-2"></i>Units Ordered:</strong>
                                            <span class="text-muted"><?= $item['quantity'] ?></span>
                                        </div>

                                        <div class="mb-2">
                                            <strong class="fw-bold"><i class="fa-solid fa-money-bill me-2"></i>Unit Price:</strong>
                                            <span class="text-muted">RM<?= number_format($item['device_price'], 2) ?></span>
                                        </div>

                                        <div class="mb-2">
                                            <strong class="fw-bold"><i class="fa-solid fa-coins me-2"></i>Subtotal:</strong>
                                            <span class="text-muted">RM<?= number_format($item['device_price'] * $item['quantity'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3"><i class="fa-solid fa-comments me-2"></i>Remarks</h4>

                    <div class="alert alert-light">
                        <h6 class="fw-bold mb-2">Admin Remarks</h6>
                        <p class="m-0"><?= nl2br(htmlspecialchars($order['order_remarks'] ?: 'No remarks.')) ?></p>
                    </div>

                    <div class="alert alert-secondary">
                        <h6 class="fw-bold mb-2">Supplier Remarks</h6>
                        <p class="m-0"><?= nl2br(htmlspecialchars($order['supplier_remarks'] ?: 'No remarks.')) ?></p>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <a href="deviceordermanagementview.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>
</body>

</html>