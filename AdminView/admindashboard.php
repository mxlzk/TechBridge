<?php
session_start();
include __DIR__ . '/../config.php';

// Dashboard Statistics
$deviceCount = 0;
$userCount = 0;
$orderCount = 0;

$deviceResult = $conn->query("SELECT COUNT(*) AS total FROM devices");
while ($row = $deviceResult->fetch_assoc()) {
    $deviceCount = $row['total'];
}

$userResult = $conn->query("SELECT COUNT(*) AS total FROM users");
while ($row = $userResult->fetch_assoc()) {
    $userCount = $row['total'];
}

$orderResult = $conn->query("SELECT COUNT(DISTINCT order_id) AS total FROM device_orders");
while ($row = $orderResult->fetch_assoc()) {
    $orderCount = $row['total'];
}

$orderRequestResult = $conn->query("SELECT COUNT(DISTINCT request_group_id) AS total FROM device_requests");
while ($row = $orderRequestResult->fetch_assoc()) {
    $orderRequestCount = $row['total'];
}

// Recent devices
$recentDevices = $conn->query("SELECT device_name, device_image, device_type, created_at FROM devices ORDER BY created_at DESC LIMIT 8");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        .dashboard-header {
            margin-bottom: 40px;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
        }

        .dashboard-subtitle {
            color: #6c757d;
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
            transition: .2s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-label {
            color: #6c757d;
        }

        .section-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 20px;
        }

        .quick-action-btn {
            min-width: 180px;
        }

        .device-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        .device-name {
            font-weight: 600;
            font-size: 16px;
        }

        .device-date {
            color: #6c757d;
            font-weight: 400;
            font-size: 13px;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php' ?>
    <div class="content">
        <div class="container py-4">
            <div class="dashboard-header">
                <h1 class="dashboard-title"><i class="fa-solid fa-gauge-high me-2"></i><?php echo htmlspecialchars($_SESSION['username'] ?? "Admin"); ?>'s Dashboard</h1>
                <p class="dashboard-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? "Admin"); ?>. Here's an overview of your system.</p>
            </div>

            <!-- Statistics -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card bg-white">
                        <div class="stat-number"><?=$deviceCount?></div>
                        <div class="stat-label"><i class="fa-solid fa-mobile-screen me-2"></i>Total Devices</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card bg-white">
                        <div class="stat-number"><?=$userCount?></div>
                        <div class="stat-label"><i class="fa-solid fa-users me-2"></i>Total Users</div>
                    </div>
                </div>

                <div class="col-md-3">  
                    <div class="stat-card bg-white">
                        <div class="stat-number"><?=$orderCount?></div>
                        <div class="stat-label"><i class="fa-solid fa-cart-shopping me-2"></i>Total Orders</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card bg-white">
                        <div class="stat-number"><?=$orderRequestCount?></div>
                        <div class="stat-label"><i class="fa-solid fa-receipt me-2"></i>Total Requests</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card mb-4">
                <h4 class="section-title"><i class="fa-solid fa-plus me-2"></i>Quick Actions</h4>
                <div class="d-flex flex-wrap gap-3">
                    <a href="adddevice.php" class="btn btn-primary quick-action-btn"><i class="fa-solid fa-mobile-screen me-2"></i>Add Device</a>
                    <a href="adduser.php" class="btn btn-success quick-action-btn"><i class="fa-solid fa-user me-2"></i>Add User</a>
                    <a href="deviceordermanagementview.php" class="btn btn-warning quick-action-btn"><i class="fa-solid fa-box me-2"></i>View Device Orders</a>
                </div>
            </div>

            <!-- Recent devices -->
            <div class="section-card">
                <h4 class="section-title"><i class="fa-solid fa-box me-2"></i>Recently Added Devices</h4>
                <?php if ($recentDevices && $recentDevices->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($device = $recentDevices->fetch_assoc()): ?>
                            <?php 
                            $deviceTypeIcon = match($device['device_type']) {
                                'Smartphone' => '<i class="fa-solid fa-mobile-screen-button me-2"></i>', 
                                'Tablet' => '<i class="fa-solid fa-tablet-screen-button me-2"></i>', 
                                'Laptop' => '<i class="fa-solid fa-laptop me-2"></i>',
                                default => '<i class="fa-solid fa-display me-2"></i>', 
                            };
                            ?>
                            <div class="list-group-item d-flex align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($device['device_image']); ?>" class="device-image" alt="device-image">
                                    <div class="device-name">
                                        <?= $deviceTypeIcon ?>
                                        <?= htmlspecialchars($device['device_name']); ?>
                                    </div>
                                </div>
                                <div class="ms-auto">
                                    <span class="device-date">Added at: <?= htmlspecialchars($device['created_at']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-box me-2"></i>No devices available.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php' ?>
</body>

</html>