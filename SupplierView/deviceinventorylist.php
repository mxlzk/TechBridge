<?php
session_start();
require_once __DIR__ . '/../config.php';
    
$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    $sql = "SELECT * FROM devices WHERE device_name LIKE ? OR device_type LIKE ? OR device_specs LIKE ? OR device_os LIKE ? OR device_storage LIKE ?";
    $stmt = $conn->prepare($sql);
    $keyword = "%{$search}%";
    $stmt->bind_param("sssss", $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $deviceResult = $stmt->get_result();
} else {
    $sql = "SELECT * FROM devices ORDER BY created_at DESC";
    $deviceResult = $conn->query($sql);
}

if (isset($_POST['save_inventory'])) {

    $deviceId = (int)$_POST['device_id'];
    $quantity = max(0, (int)$_POST['device_quantity']);
    $stmt = $conn->prepare("UPDATE devices SET device_quantity = ? WHERE device_id = ?");
    $stmt->bind_param("ii", $quantity, $deviceId);

    $stmt->execute();

    if($stmt->affected_rows > 0){
        $_SESSION['success']="Inventory updated successfully.";
    }else{
        $_SESSION['error']="Unable to update inventory.";
    }

    $stmt->close();
    header("Location: deviceinventorylist.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Inventory List</title>
    <style>
        .dashboard-title {
            font-weight: 700;
        }

        .dashboard-subtitle {
            color: #6c757d;
        }

        .device-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
            height: 100%;
            position: relative;
        }

        .device-image-wrapper {
            height: 260px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .device-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 7px;
        }

        .device-title {
            font-weight: 600;
            font-size: 1.1rem;
            min-height: 35px;
        }

        .device-specs,
        .device-storage,
        .device-os {
            color: #6c757d;
            font-size: .9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 35px;
        }

        .device-price {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .device-badge {
            display: inline-block;
            background: #eef4ff;
            color: #0d6efd;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }

        .btn-view {
            border-radius: 10px;
        }

        .device-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .quantity-wrapper {
            display: flex;
            align-items: center;
        }

        .quantity-input {
            font-weight: 700;
        }

        .btn-cart {
            font-weight: 600;
            border-radius: 10px;
        }

        .badge-bg-success,
        .badge-bg-danger {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }

        .badge-bg-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-bg-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .unit-device-badge {
            background: #e9ecef;
            color: #222222;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }
    </style>
</head>


<body>
    <?php include 'suppliernavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div class="d-flex flex-column">
                    <h2 class="dashboard-title"><i class="fa-solid fa-mobile-screen-button me-2"></i>Device Inventory List</h2>
                    <p class="dashboard-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Here's an overview of your device inventory.</p>
                </div>

                <form class="d-flex ms-auto me-3" action="deviceinventorylist.php" method="GET" id="searchForm">
                    <div class="input-group"> <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" placeholder="Search for device or device type" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </form>
            </div>
            
            <div class="row g-4">
                <?php if (mysqli_num_rows($deviceResult) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($deviceResult)): ?>
                        <?php $isUnavailable = ($row['device_status'] === 'Unavailable') ?>

                        <?php
                        $osIcon = match($row['device_os']){
                            'iOS' => '<i class="fa-brands fa-apple"></i>',
                            'Android' => '<i class="fa-brands fa-android"></i>',
                            'Windows' => '<i class="fa-brands fa-windows"></i>',
                            default => '<i class="fa-solid fa-mobile-screen-button"></i>',
                        };

                        $deviceIcon = match($row['device_type']){
                            'Smartphone' => 'fa-solid fa-mobile-screen-button me-2',
                            'Tablet' => 'fa-solid fa-tablet-screen-button me-2',
                            'Laptop' => 'fa-solid fa-laptop me-2',
                            default => 'fa-solid fa-display me-2',
                        };

                        ?>

                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="card device-card <?= $isUnavailable ?>">

                                <!-- device Image -->
                                <div class="device-image-wrapper">
                                    <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($row['device_image']); ?>" alt="<?= htmlspecialchars($row['device_name']); ?>" class="device-image">
                                </div>

                                <!-- device Content -->
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        <span class="device-badge">
                                            <i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($row['device_type']); ?>
                                        </span>

                                        <?php if ($isUnavailable): ?>
                                            <span class="badge-bg-danger"><i class="fa-solid fa-ban me-2"></i>Unavailable</span>
                                        <?php else: ?>
                                            <span class="badge-bg-success"><i class="fa-solid fa-check-circle me-2"></i>Available</span>
                                        <?php endif; ?>

                                        <span class="unit-device-badge">
                                            <i class="fa-solid fa-box me-2"></i><?= $row['device_quantity']; ?> Unit<?= $row['device_quantity'] > 1 ? 's' : '' ?> Available
                                        </span>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="device-title"><?= htmlspecialchars($row['device_name']); ?></h5>
                                        <p class="device-specs"><i class="fa-solid fa-list me-2"></i><b>Specifications: </b><?= htmlspecialchars($row['device_specs']); ?></p>
                                        <p class="device-specs"><i class="fa-solid fa-microchip me-2"></i><b>Operating System: </b><?= htmlspecialchars($row['device_os']); ?></p>
                                        <p class="device-storage"><i class="fa-solid fa-hard-drive me-2"></i><b>Storage: </b><?= htmlspecialchars($row['device_storage']); ?></p>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="device-price mb-3 d-flex align-items-center">
                                            <span class="device-price"><i class="fa-solid fa-coins me-2"></i>MYR <?= number_format($row['device_price'], 2); ?></span>
                                        </div>
                                        <form action="deviceinventorylist.php" method="POST">
                                            <div class="device-actions mt-auto">
                                                <input type="hidden" name="device_id" value="<?= $row['device_id']; ?>">

                                                <!-- Quantity Selector -->
                                                <div class="quantity-wrapper mb-3">
                                                    <div class="input-group">
                                                        <button type="button" class="btn btn-outline-secondary decrease-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-minus"></i></button>
                                                        <input type="number" name="device_quantity" value="<?= $row['device_quantity']; ?>" min="0" class="form-control text-center quantity-input" <?= $isUnavailable ? 'disabled' : '' ?>>
                                                        <button type="button" class="btn btn-outline-secondary increase-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-plus"></i></button>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="mb-2 mt-4">
                                                    <a href="viewdeviceinventory.php?device_id=<?= $row['device_id']; ?>" class="btn btn-outline-primary w-100"><i class="fa-solid fa-eye me-2"></i>View Device Details</a>
                                                    <button type="submit" name="save_inventory" class="btn btn-success w-100 mt-2"><i class="fa-solid fa-floppy-disk me-2"></i>Save Inventory</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <?php if (!empty($search)): ?>
                                <i class="fa-solid fa-circle-info me-2"></i>No results found for "<?= htmlspecialchars($search); ?>".
                            <?php else: ?>
                                <i class="fa-solid fa-circle-info me-2"></i>No devices available.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'supplierfooter.php'; ?>

    <script>
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.querySelector('input[name="search"]');

        let timer;

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

        document.querySelectorAll(".increase-btn").forEach(button => {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                const input = this.closest(".input-group").querySelector(".quantity-input");
                input.value = Number(input.value) + 1;
            });
        });

        document.querySelectorAll(".decrease-btn").forEach(button => {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                const input = this.closest(".input-group").querySelector(".quantity-input");
                if (Number(input.value) > 0) {
                    input.value = Number(input.value) - 1;
                }
            });
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    </script>
</body>
</html>