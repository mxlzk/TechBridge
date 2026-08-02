<?php
session_start();
require_once __DIR__ . '/../config.php';

$supplierId = $_SESSION['user_id'];

$orderStatus = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Delivered' => 0];

$stmt = $conn->prepare("SELECT order_status, COUNT(DISTINCT order_id) AS total FROM device_orders WHERE supplier_id=? GROUP BY order_status");

$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orderStatus[$row['order_status']] = $row['total'];
}

$totalDevices = $conn->query("SELECT COUNT(*) total FROM devices")->fetch_assoc()['total'];
$availableDevices = $conn->query("SELECT COUNT(*) total FROM devices WHERE device_status='Available'")->fetch_assoc()['total'];
$unavailableDevices = $conn->query("SELECT COUNT(*) total FROM devices WHERE device_status='Unavailable'")->fetch_assoc()['total'];
$lowInventory = $conn->query("SELECT COUNT(*) total FROM devices WHERE device_quantity<=5")->fetch_assoc()['total'];
$recentOrders = $conn->prepare("SELECT order_id, order_status, created_at FROM device_orders WHERE supplier_id= ? GROUP BY order_id ORDER BY created_at");

$recentOrders->bind_param("i", $supplierId);
$recentOrders->execute();

$recentOrders = $recentOrders->get_result();

$lowInventoryList = $conn->prepare("SELECT device_name, device_quantity, device_image FROM devices WHERE device_quantity <=5 ORDER BY device_quantity ASC LIMIT 5");

$lowInventoryList->execute();
$lowInventoryList = $lowInventoryList->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard</title>
    <style>
        .dashboard-title {
            font-weight: 700;
        }

        .dashboard-subtitle {
            color: #6c757d;
        }

        .stat-card{
            background:#fff;
            border-radius:18px;
            padding:28px;
            box-shadow:0 6px 18px rgba(0,0,0,.08);
            transition:.25s;
            text-align:center;
            height:100%;
        }

        .stat-card:hover{
            transform:translateY(-6px);
        }

        .stat-card i{
            font-size:32px;
            margin-bottom:12px;
        }

        .stat-card h2{
            font-size:34px;
            font-weight:700;
            margin-bottom:5px;
        }

        .stat-card p{
            margin:0;
            color:#6c757d;
            font-weight:500;
        }

        .pending{
            border-left:5px solid #ffc107;
        }

        .approved{
            border-left:5px solid #198754;
        }

        .rejected{
            border-left:5px solid #dc3545;
        }

        .completed{
            border-left:5px solid #0dcaf0;
        }

        .device {
            border-left:5px solid #0dcaf0;
        }

        .success{
            border-left:5px solid #198754;
        }

        .warning{
            border-left:5px solid #fd7e14;
        }

        .danger{
            border-left:5px solid #dc3545;
        }

        .section-card{
            background:#fff;
            padding:28px;
            border-radius:18px;
            box-shadow:0 6px 18px rgba(0,0,0,.08);
            margin-bottom:30px;
        }

        .section-card h4{
            font-weight:700;
            margin-bottom:20px;
        }
    </style>
</head>

<body>
    <?php include 'suppliernavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <div class="page-header">
                <h2 class="dashboard-title"><i class="fa-solid fa-gauge-high me-2"></i><?php echo htmlspecialchars($_SESSION['username'] ?? "Supplier"); ?>'s Dashboard</h2>
                <p class="dashboard-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? "Supplier"); ?>. Here's an overview of your system.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-3">
                    <div class="stat-card pending">
                        <i class="fa-solid fa-clock"></i><h2><?= $orderStatus['Pending'] ?></h2><p>Pending Orders</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card approved">
                        <i class="fa-solid fa-circle-check"></i><h2><?= $orderStatus['Approved'] ?></h2><p>Approved</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card rejected">
                        <i class="fa-solid fa-circle-xmark"></i><h2><?= $orderStatus['Rejected'] ?></h2><p>Rejected</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card completed">
                        <i class="fa-solid fa-truck-fast"></i><h2><?= $orderStatus['Delivered'] ?></h2><p>Delivered</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-3">
                    <div class="stat-card device">
                        <i class="fa-solid fa-mobile-retro"></i><h2><?= $totalDevices ?></h2><p>Total Devices</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card success">
                        <i class="fa-solid fa-circle-check"></i><h2><?= $availableDevices ?></h2><p>Available</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card danger">
                        <i class="fa-solid fa-ban"></i><h2><?= $unavailableDevices ?></h2><p>Unavailable</p>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="stat-card warning">
                        <i class="fa-solid fa-triangle-exclamation"></i><h2><?= $lowInventory ?></h2><p>Low Inventory</p>
                    </div>
                </div>
            </div>

            <div class="section-card mb-4">
                <h4><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h4>
                <div class="d-flex flex-wrap gap-3">
                    <a href="deviceinventorylist.php" class="btn btn-primary"><i class="fa-solid fa-boxes-stacked me-2"></i>Device Inventories</a>
                    <a href="deviceordersmanagementview.php" class="btn btn-success"><i class="fa-solid fa-cart-flatbed me-2"></i>Device Orders</a>
                </div>
            </div>

            <div class="section-card mb-4">
                <h4><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Purchase Orders</h4>
                <div class="list-group">
                    <?php while($order=$recentOrders->fetch_assoc()):?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6><?=$order['order_id']?></h6>
                                <small><?=date('d M Y',strtotime($order['created_at']))?></small>
                            </div>
                            <span class="badge bg-primary"><?=$order['order_status']?></span>
                        </div>
                    <?php endwhile;?>
                </div>
            </div>

            <div class="section-card">
                <h4><i class="fa-solid fa-box-open me-2"></i>Low Inventory Devices</h4>

                <?php while($device=$lowInventoryList->fetch_assoc()):?>
                <div class="list-group-item d-flex align-items-center">
                    <img src="<?=BASE_URL?>/AdminView/<?=$device['device_image']?>" width="60" height="60" class="rounded me-3" style="object-fit:cover;">
                    <div>
                        <strong><?=$device['device_name']?></strong>
                        <br>
                        <small> <?=$device['device_quantity']?> Units Remaining</small>
                    </div>
                </div>
                <?php endwhile;?>
            </div>
        </div>
    </div>
    <?php include 'supplierfooter.php'; ?>
</body>
</html>