<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceId = (int)$_POST['device_id'];
} else {
    if (!isset($_GET['device_id']) || !is_numeric($_GET['device_id'])) {
        $_SESSION['error'] = "Invalid device.";
        header("Location: devicelist.php");
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
    header("Location: viewdevice.php?device_id=".$deviceId);
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

if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {

    // Check if device is unavailable
    $checkStmt = $conn->prepare("SELECT device_status FROM devices WHERE device_id = ?");
    $checkStmt->bind_param("i", $deviceId);
    $checkStmt->execute();

    $status = $checkStmt->get_result()->fetch_assoc();

    if (!$status || $status['device_status'] === 'Unavailable') {
        $_SESSION['error'] = "This device is currently unavailable.";
        header("Location: viewdevice.php?device_id=".$deviceId);
        exit();
    }

    // Add to cart logic
    $user_id = (int) $_SESSION['user_id'];
    $device_id = (int) $_POST['device_id'];
    $quantity = max(1, (int) $_POST['quantity']);

    // Check if device is already in cart
    $stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND device_id = ? ");
    $stmt->bind_param("ii", $user_id, $device_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();

    // 
    if ($existing) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->bind_param("ii", $quantity, $existing['cart_id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, device_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $deviceId, $quantity);
        $stmt->execute();
    }

    $stmt->close();

    if ($existing) {
        $_SESSION['success'] = "Quantity updated in cart!";
    } else {
        $_SESSION['success'] = "Device added to cart!";
    }

    header("Location: viewdevice.php?device_id=" . $deviceId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Device for <?php echo $device['device_name']; ?></title>
    <style>
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .section-subtitle {
            font-size: 1rem;
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
    <?php include 'usernavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>

            <div class="section-header mb-3">
                <h2 class="section-title"><i class="fa-solid fa-eye me-2"></i>View Device for <?php echo $device['device_name']; ?></h2>
                <p class="section-subtitle"><i class="fa-solid fa-info-circle me-2"></i>View the details of the device and add it to your cart</p>
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
                    <h2 class="fw-bold mb-3"><i class="<?= $deviceIcon ?>"></i><?php echo $device['device_name']; ?></h2>

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
                    </div>

                    <form action="viewdevice.php" method="POST">
                        <div class="quantity-section mt-4">
                            <input type="hidden" name="device_id" value="<?= $deviceId; ?>">

                            <!-- Quantity Selector -->
                            <div class="quantity-wrapper mb-3">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary decrease-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-minus"></i></button>
                                    <input type="number" name="quantity" value="1" min="1" class="form-control text-center quantity-input" <?= $isUnavailable ? 'disabled' : '' ?>>
                                    <button type="button" class="btn btn-outline-secondary increase-btn" <?= $isUnavailable ? 'disabled' : '' ?>><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="add_to_cart" class=" <?= $isUnavailable ? 'btn btn-danger' : 'btn btn-success'; ?>" <?= $isUnavailable ? 'disabled' : '' ?>>
                                <?php if($isUnavailable): ?>
                                    <i class="fa-solid fa-ban me-2"></i>Unavailable
                                <?php else: ?>
                                    <i class="fa-solid fa-cart-plus me-2"></i>Add To Cart
                                <?php endif; ?>
                            </button>
                            <a href="devicelist.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Device List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php'; ?>
    <script>
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
            button.addEventListener("click", function () {
                const input = this.closest(".input-group").querySelector(".quantity-input");
                input.value = Number(input.value) + 1;
            });
        });

        document.querySelectorAll(".decrease-btn").forEach(button => {
            button.addEventListener("click", function () {
                const input = this.closest(".input-group").querySelector(".quantity-input");
                if (Number(input.value) > 1) {
                    input.value = Number(input.value) - 1;
                }
            });
        });
    </script>
</body>

</html>