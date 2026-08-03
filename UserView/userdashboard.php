<?php
session_start();
require_once __DIR__ . '/../config.php';

// Total requests submitted
$totalRequestsSubmitted = 0;

$sql = "SELECT COUNT(DISTINCT request_group_id) as total_requests FROM device_requests WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    throw new Exception($conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $totalRequestsSubmitted = (int) $row['total_requests'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        .dashboard-banner {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            padding: 40px;
            border-radius: 24px;
        }

        .dashboard-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            opacity: .9;
            margin-bottom: 0;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
        }

        .stat-card h6 {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .stat-card h2 {
            font-weight: 700;
        }

        .dashboard-section {
            margin-top: 20px;
        }

        .section-header h3 {
            font-weight: 700;
        }

        .action-card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: white;
            padding: 25px;
            border-radius: 20px;
            transition: .3s;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .device-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
        }

        .dashboard-device-image {
            width: 100%;
            height: 220px;
            object-fit: contain;
            padding: 12px;
        }

        .device-price {
            color: #0d6efd;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php'; ?>
    <div class="content">
        <div class="container py-5">

            <!-- Welcome Banner -->
            <div class="dashboard-banner mb-5">
                <div>
                    <h2 class="dashboard-title"><i class="fa-solid fa-gauge-high me-2"></i>Welcome Back,
                        <?= htmlspecialchars($_SESSION['username']); ?>
                    </h2>
                    <h6 class="dashboard-subtitle"><i class="fa-solid fa-rocket me-2"></i>Let's get started</h6>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row g-4 mb-5 justify-content-center">
                <div class="col-md-6">
                    <div class="stat-card">
                        <h6><i class="fa-solid fa-rocket me-2"></i>Submitted Requests</h6>
                        <h2><?= $totalRequestsSubmitted ?? 0 ?></h2>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stat-card">
                        <h6><i class="fa-solid fa-cart-shopping me-2"></i>Devices in Cart</h6>
                        <h2><?= $cartCount ?? 0 ?></h2>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section mb-5">
                <div class="section-header">
                    <h3><i class="fa-solid fa-bolt-lightning me-2"></i>Quick Actions</h3>
                </div>

                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <a href="devicelist.php" class="action-card">
                            <h5><i class="fa-solid fa-list me-2"></i>Browse Devices</h5>
                            <p>Explore available devices and equipment.</p>
                        </a>
                    </div>

                    <div class="col-md-4 text-center">
                        <a href="cart.php" class="action-card">
                            <h5><i class="fa-solid fa-cart-plus me-2"></i>Shopping Cart</h5>
                            <p>Review devices before checkout.</p>
                        </a>
                    </div>

                    <div class="col-md-4 text-center">
                        <a href="userprofileview.php" class="action-card">
                            <h5><i class="fa-solid fa-user-check me-2"></i>My Profile</h5>
                            <p>Manage your account information.</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent devices -->
            <div class="dashboard-section">

                <div class="section-header mb-4">
                    <h3><i class="fa-solid fa-list-ol me-2"></i>Recently Added Devices</h3>
                </div>

                <div class="row g-4">
                    <?php
                    $sql = "SELECT * FROM devices ORDER BY created_at DESC LIMIT 4";
                    $result = mysqli_query($conn, $sql);
                    while ($device = mysqli_fetch_assoc($result)):

                        $deviceIcon = match ($device['device_type']) {
                            'Smartphone' => 'fa-solid fa-mobile-screen me-2',
                            'Laptop' => 'fa-solid fa-laptop me-2',
                            'Tablet' => 'fa-solid fa-tablet-screen-button me-2',
                            default => 'fa-solid fa-mobile-screen me-2',
                        };
                    ?>
                        <div class="col-md-3">
                            <div class="device-card">
                                <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($device['device_image']); ?>"
                                    class="dashboard-device-image" alt="<?= htmlspecialchars($device['device_name']); ?>">
                                <div class="p-3 text-center">
                                    <h6><i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($device['device_name']); ?></h6>
                                    <div class="device-price"><i class="fa-solid fa-money-bill me-2"></i>RM <?= number_format($device['device_price'], 2); ?></div>
                                    <a href="viewdevice.php?device_id=<?= $device['device_id']; ?>" class="btn btn-primary btn-sm w-100 mt-2">View device</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php'; ?>
</body>

</html>
