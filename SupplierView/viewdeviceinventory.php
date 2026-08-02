<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceId = (int)$_POST['device_id'];
} else {
    if (!isset($_GET['device_id']) || !is_numeric($_GET['device_id'])) {
        $_SESSION['error'] = "Invalid device.";
        header("Location: viewdeviceinventory.php");
        exit();
    }
    $deviceId = (int)$_GET['device_id'];
}

// Get the device details
$stmt = $conn->prepare("SELECT * FROM devices WHERE device_id = ?");
$stmt->bind_param("i", $deviceId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Device not found";
    header("Location: viewdeviceinventory.php");
    exit();
}
$device = $result->fetch_assoc();
$isUnavailable = ($device['device_status'] === 'Unavailable');

$deviceIcon = match ($device['device_type']) {
    'Smartphone' => 'fa-solid fa-mobile-screen me-2',
    'Laptop' => 'fa-solid fa-laptop me-2',
    'Tablet' => 'fa-solid fa-tablet-screen-button me-2',
    default => 'fa-solid fa-mobile-screen me-2',
};

$osIcon = match ($device['device_os']) {
    'Android' => 'fa-brands fa-android me-2',
    'iOS' => 'fa-brands fa-apple me-2',
    'Windows' => 'fa-brands fa-windows me-2',
    default => 'fa-solid fa-mobile-screen me-2',
};

if (isset($_POST['save_inventory'])) {


    // Check if device is unavailable
    $checkStmt = $conn->prepare("SELECT device_status FROM devices WHERE device_id = ?");
    $checkStmt->bind_param("i", $deviceId);
    $checkStmt->execute();

    $status = $checkStmt->get_result()->fetch_assoc();

    if (!$status || $status['device_status'] === 'Unavailable') {
        $_SESSION['error'] = "This device is currently unavailable.";
        header("Location: viewdeviceinventory.php?device_id=".$deviceId);
        exit();
    }
    
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
    header("Location: viewdeviceinventory.php?device_id=".$deviceId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Device Inventory</title>
    <style>
        .section-title {
            font-weight: 700;
        }

        .section-subtitle {
            color: #6c757d;
        }

        .device-image-container {
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .device-image {
            height: 380px;
            width: 350px;
            object-fit: contain;
            border-radius: 10px;
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
                    <h2 class="section-title"><i class="fa-solid fa-mobile-screen-button me-2"></i>View Device Inventory</h2>
                    <p class="section-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Here's an overview of your device inventory.</p>
                </div>
            </div>

            <?php if ($isUnavailable): ?>
                <div class="alert alert-warning mt-3"><i class="fa-solid fa-ban me-2"></i>This device is currently unavailable for rental.</div>
            <?php endif; ?>

            <div class="d-flex flex-row gap-2 justify-content-around align-items-center bg-white shadow-sm border border-secondary rounded-4 p-5">
                <div class="col-md-4 p-3">
                    <div class="device-image-container bg-light border shadow-sm rounded-4 p-3">
                        <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($device['device_image']); ?>" class="device-image" alt="<?= htmlspecialchars($device['device_name']); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <h2 class="fw-bold mb-2"><i class="<?= $deviceIcon ?>"></i><?= htmlspecialchars($device['device_name']); ?></h2>

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-info-circle me-2"></i>Device Specfications</label>
                        <div class="bg-light border border-secondary shadow-sm rounded p-3"><?= htmlspecialchars($device['device_specs']); ?></div>
                    </div>  

                    <div class="mb-3">
                        <label class="form-label fw-semibold"><i class="fa-solid fa-coins me-2"></i>Rental Price</label>
                        <div class="bg-light border border-secondary shadow-sm rounded p-3 fw-bold text-success">RM <?= number_format($device['device_price'], 2); ?></div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-mobile-screen me-2"></i>Device Type</label>
                            <div class="bg-light border border-secondary shadow-sm rounded p-3"><i class="<?= $deviceIcon ?>"></i><?= $device['device_type']; ?></div>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-microchip me-2"></i>Operating System</label>
                            <div class="bg-light border border-secondary shadow-sm rounded p-3"><i class="<?= $osIcon ?>"></i><?= $device['device_os']; ?></div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-database me-2"></i>Storage</label>
                            <div class="bg-light border border-secondary shadow-sm rounded p-3"><?= $device['device_storage']; ?></div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-palette me-2"></i>Color</label>
                            <div class="bg-light border border-secondary shadow-sm rounded p-3"><?= $device['device_color']; ?></div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold"><i class="fa-solid fa-layer-group me-2"></i>Units Available</label>
                            <div class="bg-light border border-secondary shadow-sm rounded p-3"><?= $device['device_quantity']; ?></div>
                        </div>

                        <div class="col-6">
                            <label for="device_status" class="form-label fw-semibold"><i class="fa-solid fa-circle-info me-2"></i>Device Status</label>
                            <select name="device_status" id="device_status" class="form-select bg-light border border-secondary shadow-sm rounded p-3">
                                <option value="Available" <?= $device['device_status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Unavailable" <?= $device['device_status'] === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>

                    <form action="viewdeviceinventory.php" method="POST">
                        <div class="quantity-section mt-4">
                            <input type="hidden" name="device_id" value="<?= $deviceId; ?>">

                            <!-- Quantity Selector -->
                            <div class="quantity-wrapper mb-3">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary decrease-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-minus"></i></button>
                                    <input type="number" name="device_quantity" value="<?= $device['device_quantity']; ?>" min="0" class="form-control text-center quantity-input" <?= $isUnavailable ? 'disabled' : '' ?>>
                                    <button type="button" class="btn btn-outline-secondary increase-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success" name="save_inventory"><i class="fa-solid fa-floppy-disk me-2"></i>Save Inventory</button>
                            <a href="deviceinventorylist.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Inventory List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'supplierfooter.php'?>  
    
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