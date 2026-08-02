<?php
session_start();
require_once __DIR__ . '/../config.php';

$userId = (int) $_SESSION['user_id'];

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("We are currently experiencing technical difficulties. Please try again later.");
}

$sql = "SELECT do.order_id, COUNT(*) AS total_devices, SUM(do.quantity) AS total_units, do.order_status, MIN(do.created_at) AS created_at
FROM device_orders do WHERE do.supplier_id = ? GROUP BY do.order_id ORDER BY MIN(do.created_at) DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Error preparing statement: " . mysqli_error($conn));
    die("Unable to retrieve order information at the moment. Please try again later.");
}

mysqli_stmt_bind_param($stmt, "i", $userId);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute failed: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    die("Unable to retrieve order information.");
}

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    error_log("Error getting result: " . mysqli_error($conn));
    mysqli_stmt_close($stmt);
    die("Unable to retrieve order information.");
}

$status = array_fill_keys(['Pending', 'Approved', 'Preparing', 'Shipped', 'Delivered', 'Rejected'], 0);
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
    if (isset($status[$row['order_status']])) {
        $status[$row['order_status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Orders Management</title>
    <style>
        .supplier-title {
            font-weight: 700;
        }

        .supplier-subtitle {
            color: #6c757d;
        }

        .order-card {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .order-id {
            font-weight: 700;
        }

        .order-supplier {
            color: #6c757d;
        }

        .order-meta {
            display: flex;
            justify-content: space-between;
            font-size: .9rem;
        }

        .summary-scroll {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .summary-mini {
            background: white;
            border-radius: 16px;
            padding: 14px 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 140px;
        }

        .summary-mini span {
            color: #6c757d;
            font-size: .9rem;
        }

        .summary-mini strong {
            font-size: 1.2rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .status-pending { 
            background: #fff3cd; 
            color: #856404; 
        }
        
        .status-review { 
            background: #d1ecf1; 
            color: #0c5460; 
        }
        
        .status-approved { 
            background: #d4edda; 
            color: #155724; 
        }
        
        .status-preparing { 
            background: #cce5ff; 
            color: #004085; 
        }
        
        .status-shipped { 
            background: #ffeeba; 
            color: #856404; 
        }
        
        .status-delivered { 
            background: #d4edda; 
            color: #155724; 
        }

        .status-rejected { 
            background: #f8d7da; 
            color: #721c24; 
        }
    </style>
</head>
<body>
    <?php include 'suppliernavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'?>
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex flex-column">
                    <h2 class="supplier-title"><i class="fa-solid fa-box-open me-2"></i>Device Orders Management</h2>
                    <p class="supplier-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Manage device orders</p>
                </div>

                <form class="d-flex ms-auto me-3" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" placeholder="Search for submitted order" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </form>
            </div>

            <div class="col-12 mb-4 d-flex justify-content-center">
                <div class="summary-scroll">
                    <div class="summary-mini">
                        <i class="fa-solid fa-box-open me-2"></i><span>Total</span>
                        <strong class="ms-2"><?= count($orders) ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Pending</span>
                        <strong class="ms-2"><?= $status['Pending'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Approved</span>
                        <strong class="ms-2"><?= $status['Approved'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Preparing</span>
                        <strong class="ms-2"><?= $status['Preparing'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Shipped</span>
                        <strong class="ms-2"><?= $status['Shipped'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Delivered</span>
                        <strong class="ms-2"><?= $status['Delivered'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Rejected</span>
                        <strong class="ms-2"><?= $status['Rejected'] ?></strong>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $statusClass = match ($order['order_status']) {
                        'Pending' => 'status-pending',
                        'Approved' => 'status-approved',
                        'Preparing' => 'status-approved',
                        'Shipped' => 'status-approved',
                        'Delivered' => 'status-approved',
                        'Rejected' => 'status-rejected',
                        default => 'status-review'
                    };

                    $statusIcon = match ($order['order_status']) {
                        'Pending' => 'fa-solid fa-circle-info me-2',
                        'Approved' => 'fa-solid fa-circle-check me-2',
                        'Preparing' => 'fa-solid fa-box-open me-2',
                        'Shipped' => 'fa-solid fa-truck me-2',
                        'Delivered' => 'fa-solid fa-truck-pickup me-2',
                        'Rejected' => 'fa-solid fa-circle-xmark me-2',
                        default => 'fa-solid fa-circle-info me-2'
                    };
                    ?>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="order-card">
                            <div class="card-header-row">
                                <div class="mb-3">
                                    <div class="order-id">
                                        <i class="fa-solid fa-cart-plus me-2"></i><?= htmlspecialchars($order['order_id']) ?>
                                    </div>
                                </div>

                                <span class="status-badge <?= $statusClass ?>">
                                    <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($order['order_status']) ?>
                                </span>
                            </div>

                            <div class="small mt-3">
                                <span class="text-muted"><i class="fa-solid fa-circle-info me-2"></i>Order Details</span>
                            </div>

                            <div class="order-meta mt-3">
                                <div class="detail-row">
                                    <i class="fa-solid fa-user me-2"></i><span><?= htmlspecialchars($_SESSION['username']) ?></span>
                                </div>

                                <div class="detail-row">
                                    <i class="fa-solid fa-mobile-screen-button me-2"></i><span>Devices: <?= $order['total_devices'] ?></span>
                                </div>

                                <div class="detail-row">
                                    <i class="fa-solid fa-layer-group me-2"></i><span>Units: <?= $order['total_units'] ?></span>
                                </div>
                            </div>

                            <div class="order-meta border-top mt-3">
                                <div class="small mt-2">
                                    <i class="fa-solid fa-calendar-check me-2"></i>
                                    <?= date('d M Y', strtotime($order['created_at'])) ?>
                                </div>
                            </div>

                            <a href="reviewdeviceorder.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn btn-outline-primary w-100 mt-3"><i class="fa-solid fa-eye me-2"></i>View Order Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted mt-4">No device orders available</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'supplierfooter.php'; ?>
</body>
</html>