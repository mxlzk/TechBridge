<?php
session_start();
require_once __DIR__ . '/../config.php';

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartId = (int) ($_POST['cart_id'] ?? 0);

    switch ($action) {
        case 'increase':
            $cartId = (int) $_POST['cart_id'];
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cartId, $userId);
            $stmt->execute();
            break;

        case 'decrease':
            $cartId = (int) $_POST['cart_id'];
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE cart_id = ? AND user_id = ? AND quantity > 1");
            $stmt->bind_param("ii", $cartId, $userId);
            $stmt->execute();
            break;

        case 'remove':
            $cartId = (int) $_POST['cart_id'];
            $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cartId, $userId);
            $stmt->execute();
            break;
    }

    header("Location: cart.php");
    exit();
}

$stmt = $conn->prepare("SELECT c.cart_id, c.quantity, d.device_id, d.device_name, d.device_price, d.device_image, d.device_type FROM cart c INNER JOIN devices d ON c.device_id = d.device_id WHERE c.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);

$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];

while ($row = $result->fetch_assoc()) {
    $row['item_total'] = $row['device_price'] * $row['quantity'];
    $cartItems[] = $row;
}

$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <style>
        .cart-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .cart-subtitle {
            color: #6c757d;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 18px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        .device-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
        }

        .device-details {
            flex: 1;
        }

        .device-name {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .device-category {
            color: #6c757d;
            margin-bottom: 8px;
        }

        .device-price {
            color: #0d6efd;
            font-weight: 600;
        }

        .device-quantity {
            display: flex;
            align-items: center;
        }

        .device-total {
            font-weight: 700;
            min-width: 110px;
            text-align: right;
        }

        .summary-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
            position: sticky;
            top: 20px;
        }

        .summary-title {
            font-weight: 700;
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #495057;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php' ?>

    <div class="content">
        <div class="container py-4">

            <!-- Page Header -->
            <div class="cart-header mb-4">
                <h2 class="cart-title"><i class="fa-solid fa-cart-shopping me-2"></i>Shopping Cart</h2>
                <p class="cart-subtitle"><i class="fa-solid fa-pen-to-square me-2"></i>Review your selected devices before checkout.</p>
            </div>

            <!-- Cart Items and Summary -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if (!empty($cartItems)): ?>
                        <div class="mb-3">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            <label for="selectAll">Select All</label>
                        </div>

                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $deviceType = $item['device_type'];

                            $icon = match ($deviceType) {
                                'Smartphone' => 'fa-mobile-screen-button',
                                'Tablet' => 'fa-tablet-screen-button',
                                'Laptop' => 'fa-laptop',
                                default => 'fa-microchip'
                            };
                            ?>
                            <div class="cart-item">
                                <input type="checkbox" class="item-checkbox" data-price="<?= $item['item_total']; ?>" value="<?= $item['cart_id']; ?>" name="selected_items[]">
                                <img src="<?= BASE_URL ?>/AdminView/<?= htmlspecialchars($item['device_image']); ?>" alt="<?= htmlspecialchars($item['device_name']); ?>" class="device-image">

                                <div class="device-details">
                                    <h5 class="device-name"><?= htmlspecialchars($item['device_name']); ?></h5>
                                    <p class="device-category"><i class="fa-solid <?= $icon ?> me-2"></i><?= htmlspecialchars($item['device_type']); ?></p>
                                    <div class="device-price"><i class="fa-solid fa-coins me-2"></i>RM <?= number_format($item['device_price'], 2); ?></div>
                                </div>

                                <div class="device-quantity">
                                    <form method="POST" class="me-1">
                                        <input type="hidden" name="action" value="decrease">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-minus"></i></button>
                                    </form>

                                    <span class="mx-2"><?= $item['quantity']; ?></span>

                                    <form method="POST" class="ms-1">
                                        <input type="hidden" name="action" value="increase">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-plus"></i></button>
                                    </form>
                                </div>

                                <div class="device-total">RM <?= number_format($item['item_total'], 2); ?></div>

                                <!-- Remove -->
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash-can me-2"></i>Remove Item</button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <div class="card p-5 text-center">
                            <h4><i class="fa-solid fa-cart-shopping me-2"></i>Your cart is empty</h4>
                            <p class="text-muted"><i class="fa-solid fa-mobile me-2"></i>Browse devices and add items to your cart.</p>
                            <a href="devicelist.php" class="btn btn-primary"><i class="fa-solid fa-cart-shopping me-2"></i>Continue Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="summary-card">
                        <h4 class="summary-title"><i class="fa-solid fa-cart-shopping me-2"></i>Order Summary</h4>

                        <div class="summary-row">
                            <span><i class="fa-solid fa-list me-2"></i>Selected Items</span>
                            <span id="selectedCount"></span>
                        </div>

                        <div class="summary-row">
                            <span><i class="fa-solid fa-coins me-2"></i>Subtotal</span>
                            <span>RM <span id="subtotal"></span></span>
                        </div>

                        <div class="summary-row">
                            <span><i class="fa-solid fa-shipping-fast me-2"></i>Shipping</span>
                            <span>RM <span id="shipping"></span></span>
                        </div>

                        <div class="summary-row">
                            <span><i class="fa-solid fa-calculator me-2"></i>Tax (6%)</span>
                            <span>RM <span id="tax"></span></span>
                        </div>

                        <div class="summary-total">
                            <span><i class="fa-solid fa-coins me-2"></i>Total</span>
                            <span>RM <span id="total"></span></span>
                        </div>

                        <form action="checkout.php" method="POST" id="checkoutForm">
                            <div id="selectedItemsContainer"></div>
                            <button type="submit" id="checkoutBtn" class="btn btn-primary w-100 mt-4" disabled>Proceed to Checkout</button>
                        </form>

                        <a href="devicelist.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php' ?>

    <script>
        // Initialize DOM elements
        const checkoutBtn = document.getElementById('checkoutBtn');
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selectedItemsContainer = document.getElementById('selectedItemsContainer');
        const checkoutForm = document.querySelector("form[action='checkout.php']");
        const STORAGE_KEY = "selectedCartItems";

        // Handle form submission
        checkoutForm.addEventListener("submit", function(e) {
            const selected = document.querySelectorAll('.item-checkbox:checked');

            // Check if at least one item is selected
            if (selected.length === 0) {
                e.preventDefault();
                alert("Please select at least one item.");
                return;
            }

            // Clear previous hidden inputs
            selectedItemsContainer.innerHTML = '';

            // Add selected items to hidden inputs
            selected.forEach(item => {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "selected_items[]";
                input.value = item.value;
                selectedItemsContainer.appendChild(input);
            });
        });

        // Update order summary
        function updateSummary() {
            // Initialize variables
            let subtotal = 0;
            let selectedCount = 0;

            // Loop through each checkbox
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedCount++;
                    subtotal += parseFloat(cb.dataset.price);
                }
            });

            // Add shipping fee if subtotal is greater than 0
            let shipping = subtotal > 0 ? 15 : 0;

            // Calculate tax (6%)
            let tax = subtotal * 0.06;

            // Calculate total
            let total = subtotal + shipping + tax;

            // Enable/disable checkout button
            checkoutBtn.disabled = subtotal <= 0;

            // Update order summary
            document.getElementById("selectedCount").innerText = selectedCount;
            document.getElementById("subtotal").innerText = subtotal.toFixed(2);
            document.getElementById("shipping").innerText = shipping.toFixed(2);
            document.getElementById("tax").innerText = tax.toFixed(2);
            document.getElementById("total").innerText = total.toFixed(2);
        }

        // Handle select all items checkbox
        if (selectAll) {
            selectAll.addEventListener("change", function() {

                // Select or deselect all checkboxes
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });

                // Save selected items to local storage
                const selectedIds = [];
                document.querySelectorAll('.item-checkbox:checked').forEach(item => {
                    selectedIds.push(item.value);
                });

                // Save selected items to local storage
                localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedIds));
                updateSummary();
            });
        }

        // Handle individual item checkboxes
        checkboxes.forEach(cb => {
            cb.addEventListener("change", function() {
                const selectedIds = [];
                document.querySelectorAll('.item-checkbox:checked').forEach(item => {
                    selectedIds.push(item.value);
                });

                localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedIds));
                updateSummary();

                if (selectAll) {
                    const checkCount = document.querySelectorAll('.item-checkbox:checked').length;
                    selectAll.checked = checkCount === checkboxes.length;
                }
            });
        });

        // Load saved selections from local storage
        const savedSelections = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

        // Check saved items
        checkboxes.forEach(cb => {
            if (savedSelections.includes(cb.value)) {
                cb.checked = true;
            }
        });

        // Update select all checkbox after loading saved selections
        if (selectAll) {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            selectAll.checked = checkedCount === checkboxes.length;
        }

        // Update order summary
        updateSummary();
    </script>
</body>

</html>