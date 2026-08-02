<?php
session_start();
require_once __DIR__ . '/../config.php';

$search = trim($_GET['search'] ?? '');

//Search for devices by name or device type
if ($search) {
    $sql = "SELECT * FROM devices WHERE device_name LIKE ? OR device_type LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    $keyword = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM devices ORDER BY created_at DESC";
    $result = $conn->query($sql);
}

$countDevicesSql = "SELECT COUNT(*) AS total_available FROM devices WHERE device_status = 'Available'";
$countResult = $conn->query($countDevicesSql);
$availableDevices = $countResult->fetch_assoc()['total_available'];

if ($search) {
    $deviceCount = $result->num_rows;
} else {
    $deviceCount = $availableDevices;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Management</title>
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

        .device-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            background-color: #ffffff;
        }

        .device-image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
            padding: 10px;
        }

        .device-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .device-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #212529;
        }

        .device-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .device-meta .badge {
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 10px;
            background-color: #eef2f7;
            color: #495057;
        }

        .device-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 18px;
        }

        .card-body {
            padding: 1.2rem;
        }

        .btn-action {
            border-radius: 10px;
            font-weight: 600;
        }

        .empty-state {
            background: white;
            padding: 50px 20px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        .empty-state h4 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 0;
        }

        .input-group .form-control {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>

    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex flex-column mb-3">
                    <h2 class="page-title"><i class="fa-solid fa-display me-2"></i>Device Management</h2>
                    <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Manage and monitor all available devices </p>
                </div>

                <form class="d-flex ms-auto me-3" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" placeholder="Search device or device type" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </form>

                <div class="d-flex justify-content-end">
                    <a href="adddevice.php" class="btn btn-primary px-4 py-2 btn-action"><i class="fa-solid fa-plus me-2"></i>Add New Device</a>
                </div>
            </div>

            <!-- device Count -->
            <?php if ($result && $result->num_rows > 0): ?>
                <p class="text-muted mb-4"><i class="fa-solid fa-list me-2"></i><?= $deviceCount ?> device(s) found </p>
            <?php else: ?>
                <p class="text-muted mb-4"><i class="fa-solid fa-list me-2"></i><?= $deviceCount ?> device(s) available</p>
            <?php endif; ?>

            <!-- device Grid -->
            <div class="row g-4"> 
                <!-- Display devices -->
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($device = $result->fetch_assoc()): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <?php
                                $deviceType = match($device['device_type']) { 
                                    'Smartphone' => '<i class="fa-solid fa-mobile-screen-button me-2"></i>Phone', 
                                    'Tablet' => '<i class="fa-solid fa-tablet-screen-button me-2"></i>Tablet', 
                                    'Laptop' => '<i class="fa-solid fa-laptop me-2"></i>Laptop',
                                    default => '<i class="fa-solid fa-display me-2"></i>Device', 
                                };

                                $deviceIcon = match($device['device_type']) {
                                    'Smartphone' => 'fa-solid fa-mobile-screen-button me-2',
                                    'Tablet' => 'fa-solid fa-tablet-screen-button me-2',
                                    'Laptop' => 'fa-solid fa-laptop me-2',
                                    default => 'fa-solid fa-display me-2',
                                };

                                $deviceOs = match($device['device_os']) {
                                    'iOS' => 'fa-brands fa-apple me-2',
                                    'Android' => 'fa-brands fa-android me-2',
                                    'Windows' => 'fa-brands fa-windows me-2',
                                    default => 'fa-solid fa-display me-2',
                                };

                                $statusClass = match ($device['device_status']) {
                                    'Available' => 'bg-light-success text-success',
                                    'Unavailable' => 'bg-light-danger text-danger',
                                    default => 'bg-light-secondary text-secondary',
                                };

                                $statusIcon = match ($device['device_status']) {
                                    'Available' => 'fa-solid fa-circle-check me-2',
                                    'Unavailable' => 'fa-solid fa-circle-xmark me-2',
                                    default => 'fa-solid fa-circle-question me-2',
                                };
                            ?>

                            <div class="card h-100 device-card">
                                <!-- Device Image -->
                                <div class="device-image-container">
                                    <img src="<?= htmlspecialchars($device['device_image']); ?>" class="device-image" alt="device Image">
                                </div>

                                <!-- Device Content -->
                                <div class="card-body d-flex flex-column">
                                    <!-- Device Name -->
                                    <h5 class="device-title"><i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($device['device_name']); ?></h5>

                                    <!-- Device Metadata -->
                                    <div class="device-meta">
                                        <span class="badge">
                                            <i class="<?= $deviceOs ?>"></i><?= htmlspecialchars($device['device_os']); ?>
                                        </span>

                                        <span class="badge">
                                            <?= $deviceType ?>
                                        </span>

                                        <span class="badge">
                                            <i class="fa-solid fa-hard-drive me-2"></i><?= htmlspecialchars($device['device_storage']); ?>
                                        </span>

                                        <span class="badge">
                                            <i class="fa-solid fa-palette me-2"></i><?= htmlspecialchars($device['device_color']); ?>
                                        </span>

                                        <span class="badge <?= $statusClass ?>">
                                            <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($device['device_status']); ?>
                                        </span>
                                    </div>

                                    <!-- Device Price -->
                                    <div class="device-price"><i class="fa-solid fa-coins me-2"></i>MYR <?= number_format($device['device_price'], 2); ?> </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="viewdevice.php?device_id=<?= $device['device_id']; ?>" class="btn btn-sm btn-primary flex-fill"><i class="fa-solid fa-eye me-2"></i>View </a>
                                        <a href="editdevice.php?device_id=<?= $device['device_id']; ?>" class="btn btn-sm btn-outline-warning flex-fill"><i class="fa-solid fa-pen me-2"></i>Edit </a>
                                        <a href="deletedevice.php?device_id=<?= $device['device_id']; ?>" class="btn btn-sm btn-outline-danger flex-fill" onclick="return confirm('Delete this device?');"><i class="fa-solid fa-trash me-2"></i>Delete </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                <!-- Return if no devices found -->
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <h4><i class="fa-solid fa-exclamation-circle me-2"></i>No devices Found</h4>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>

    <script>
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchForm');

        let timer;

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                searchForm.submit();
            }, 500); // wait 500ms after user stops typing
        });

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.auto-dismiss-alert').forEach(alert => {
                setTimeout(() => {
                    if (alert) {
                        alert.classList.remove('show');
                        alert.classList.add('fade');
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }
                }, 5000);
            });
        });
    </script>
</body>

</html>