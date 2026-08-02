<?php
session_start();
require_once __DIR__ . '/../config.php';

// Calculate totals
function calculateTotals(float $subtotal): array
{
    $shipping = $subtotal > 0 ? 15 : 0;
    $tax = $subtotal * 0.06;
    $total = $subtotal + $shipping + $tax;

    return ['shipping' => $shipping, 'tax' => $tax, 'total' => $total];
}   

$userId = (int) $_SESSION['user_id'];

// Store selected items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items']) && !isset($_POST['action'])) {
    $_SESSION['selected_items'] = $_POST['selected_items'];
    header("Location: checkout.php");
    exit();
}

// Get selected items
$selectedItems = $_SESSION['selected_items'] ?? [];
if (empty($selectedItems)) {
    $_SESSION['error'] = "Please select at least one item.";
    header("Location: cart.php");
    exit();
}

// Load logged in user's info
$userStmt = $conn->prepare("SELECT user_id, username, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Load items from cart
$placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

$sql = "SELECT c.cart_id, c.quantity, d.device_id, d.device_name, d.device_price, d.device_image, d.device_type FROM cart c INNER JOIN devices d ON c.device_id = d.device_id WHERE c.user_id = ? AND c.cart_id IN ($placeholders)";

$cartStmt = $conn->prepare($sql);
$types = 'i' . str_repeat('i', count($selectedItems));
$params = array_merge([$userId], $selectedItems);
$cartStmt->bind_param($types, ...$params);
$cartStmt->execute();
$cart = $cartStmt->get_result();

$cartItems = [];
$totalDevices = 0;
$subtotal = 0;

// Generate unique request group id for each request
$requestGroupId = 'REQ-' . time() . '-' . $userId;

while ($row = $cart->fetch_assoc()) {
    $row['item_total'] = $row['device_price'] * $row['quantity']; // Calculate item total
    $subtotal += $row['item_total']; // Calculate subtotal
    $totalDevices += $row['quantity']; // Count total devices
    $cartItems[] = $row; // Add item to cart items
}

// Calculate totals using the function
$totals = calculateTotals($subtotal);
$shipping = $totals['shipping'];
$tax = $totals['tax'];
$grandTotal = $totals['total'];

// Submit device rental request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_request') {

    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $rentalCategory = strtolower(trim($_POST['rental_category'] ?? ''));
    $rentalDuration = filter_input(INPUT_POST, 'rental_duration', FILTER_VALIDATE_INT);

    $maxRentalYears = ['school' => 11, 'tertiary' => 4, 'working' => 5];

    if (!array_key_exists($rentalCategory, $maxRentalYears)) {
        $_SESSION['error'] = "Please select a valid rental category.";
        header("Location: checkout.php");
        exit();
    }

    $allowedYears = $maxRentalYears[$rentalCategory];

    if ($rentalDuration === false || $rentalDuration < 1 || $rentalDuration > $allowedYears) {
        $_SESSION['error'] = "Rental duration must be between 1 and {$allowedYears} years.";
        header("Location: checkout.php");
        exit();
    }

    if (empty($cartItems)) {
        $_SESSION['error'] = "Your cart is empty.";
        header("Location: cart.php");
        exit();
    }

    try {
        $conn->begin_transaction();
        foreach ($cartItems as $item) {
            $requestStmt = $conn->prepare("INSERT INTO device_requests (request_group_id, user_id, device_id, quantity, payment_method, rental_category, rental_duration, request_status, device_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $status = "Pending";
            $requestStmt->bind_param("siiissisd", $requestGroupId, $userId, $item['device_id'], $item['quantity'], $paymentMethod, $rentalCategory, $rentalDuration, $status, $item['device_price']);
            $requestStmt->execute();
        }

        $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

        $sql = "DELETE FROM cart WHERE user_id = ? AND cart_id IN ($placeholders)";

        $deleteStmt = $conn->prepare($sql);
        $types = 'i' . str_repeat('i', count($selectedItems));
        $params = array_merge([$userId], $selectedItems);
        $deleteStmt->bind_param($types, ...$params);
        $deleteStmt->execute();

        $conn->commit();
        unset($_SESSION['selected_items']);

        $_SESSION['success'] = "Rental request submitted successfully.";
        header("Location: reqstatus.php");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        //$_SESSION['error'] = "Unable to submit request.";
        //header("Location: checkout.php");
        die("Checkout Error: " . $e->getMessage());
        //exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        .checkout-header {
            margin-bottom: 30px;
        }

        .checkout-title {
            font-size: 2rem;
            font-weight: 700;
        }

        .checkout-subtitle {
            color: #6c757d;
        }

        .checkout-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .summary-image {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #f8f9fa;
        }

        .summary-name {
            font-weight: 600;
        }

        .summary-meta {
            color: #6c757d;
            font-size: .9rem;
        }

        .checkout-btn {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head> 

<body>
    <?php include 'usernavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <!-- Header -->
            <div class="checkout-header">
                <h1 class="checkout-title"><i class="fa-solid fa-cart-shopping me-2"></i>Checkout</h1>
                <p class="checkout-subtitle"><i class="fa-solid fa-info-circle me-2"></i>Review your selected devices and submit your rental request</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fa-solid fa-circle-user me-2"></i>User Information</h5>
                            <p><i class="fa-solid fa-user me-2"></i>Username: <?= htmlspecialchars($user['username']); ?></p>
                            <p><i class="fa-solid fa-envelope me-2"></i>Email: <?= htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>

                    <div class="card checkout-card">
                        <div class="card-body">
                            <h5 class="mb-4"><i class="fa-solid fa-circle-info me-2"></i>Selected Devices</h5>
                            <?php foreach ($cartItems as $item): ?>
                                <?php
                                // Assign icon for device type
                                $deviceIcon = match ($item['device_type']) {
                                    "Smartphone" => "fa-mobile-screen-button me-2",
                                    "Tablet" => "fa-tablet-screen-button me-2",
                                    "Laptop" => "fa-laptop me-2",
                                    default => "fa-microchip me-2",
                                };
                                ?>
                                <div class="summary-item">
                                    <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($item['device_image']); ?>" alt="<?= htmlspecialchars($item['device_name']); ?>" class="summary-image">
                                    <div>
                                        <div class="summary-name">
                                            <i class="fa-solid <?= $deviceIcon ?>"></i>
                                            <span><?= htmlspecialchars($item['device_name']); ?></span>
                                        </div>
                                        <div class="summary-meta">
                                            <i class="fa-solid fa-list me-2"></i>
                                            <span>Unit(s) Requested: <?= htmlspecialchars($item['quantity']); ?></span>
                                        </div>

                                        <div class="summary-meta">
                                            <i class="fa-solid fa-tag me-2"></i>
                                            <span>Unit Price: RM<?= number_format(htmlspecialchars($item['device_price']), 2); ?></span>
                                        </div>

                                        <div class="summary-meta">
                                            <i class="fa-solid fa-money-bill-wave me-2"></i>
                                            <span>Total Price: RM<?= number_format(htmlspecialchars($item['item_total']), 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-flex justify-content-between">
                                <strong><i class="fa-solid fa-list-check me-2"></i>Total Devices</strong>
                                <strong><i class="fa-solid fa-list-check me-2"></i><?= number_format(htmlspecialchars($totalDevices ?? 0)); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card checkout-card">
                        <div class="card-body">
                            <h5 class="mb-4"><i class="fa-solid fa-circle-info me-2"></i>Rental Information</h5>

                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fa-solid fa-shipping-fast me-2"></i>Shipping</span>
                                <strong>RM <?= number_format($shipping, 2); ?></strong>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fa-solid fa-calculator me-2"></i>Tax (6%)</span>
                                <strong>RM <?= number_format($tax, 2); ?></strong>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <strong><i class="fa-solid fa-credit-card me-2"></i>Grand Total</strong>
                                <strong class="fs-5 text-success">RM <?= number_format($grandTotal, 2); ?></strong>
                            </div>

                            <hr class="my-3">

                            <form action="checkout.php" method="POST">
                                <input type="hidden" name="action" value="submit_request">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fa-solid fa-list-check me-2"></i>Rental Category</label>
                                    <select name="rental_category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <option value="school">Elementary / High School</option>
                                        <option value="tertiary">Tertiary Education</option>
                                        <option value="working">Working Adult</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fa-solid fa-clock me-2"></i>Rental Duration (Years)</label>
                                    <input type="number" name="rental_duration" class="form-control" min="1" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fa-solid fa-receipt me-2"></i>Payment Method</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Online Banking">Online Banking / Debit Card</option>
                                        <option value="E-Wallet">E-Wallet</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>

                                <div class="mb-3 mt-4">
                                    <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-paper-plane me-2"></i>Submit Rental Request</button>
                                    <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php'; ?>

    <script>
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