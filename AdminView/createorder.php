<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderId = 'ORD-' . date('YmdHis') . '-' . rand(100, 999);

    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    $deviceIds = $_POST['device_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    $mergedDevices = [];

    foreach ($deviceIds as $index => $deviceId) {

        $deviceId = (int)$deviceId;
        $quantity = (int)($quantities[$index] ?? 0);

        if ($deviceId <= 0) {
            continue;
        }

        if ($quantity < 1) {
            continue;
        }

        if (!isset($mergedDevices[$deviceId])) {
            $mergedDevices[$deviceId] = 0;
        }

        $mergedDevices[$deviceId] += $quantity;
    }

    if ($supplierId <= 0) {
        $_SESSION['error'] = "Please select a supplier.";
        header("Location: createorder.php");
        exit();
    }

    if (count($mergedDevices) == 0) {
        $_SESSION['error'] = "Please add at least one device.";
        header("Location: createorder.php");
        exit();
    }

    try {
        $conn->begin_transaction();
        $priceStmt = $conn->prepare("SELECT device_price FROM devices WHERE device_id = ?");
        $stmt = $conn->prepare("INSERT INTO device_orders (order_id, supplier_id, device_id, quantity, order_status, total_price, order_remarks) VALUES (?, ?, ?, ?, 'Pending', ?, ?)");

        foreach ($mergedDevices as $deviceId => $quantity) {

            // Retrieve unit price
            $priceStmt->bind_param("i", $deviceId);
            $priceStmt->execute();

            $result = $priceStmt->get_result();

            if ($result->num_rows === 0) {
                $_SESSION['error'] = "Selected device does not exist.";
                header("Location: createorder.php");
                exit();
            }

            $device = $result->fetch_assoc();

            $unitPrice = (float) $device['device_price'];

            $total_price = $unitPrice * $quantity;

            $stmt->bind_param("siiids", $orderId, $supplierId, $deviceId, $quantity, $total_price, $remarks);
            $stmt->execute();
        }

        $priceStmt->close();
        $stmt->close();
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();

        $_SESSION['error'] = "Failed to create order: " . $e->getMessage();
        header("Location: createorder.php");
        exit();
    }

    $_SESSION['success'] = "Order {$orderId} created successfully.";
    header("Location: deviceordermanagementview.php");
    exit();
}

$suppliers = mysqli_query($conn, "SELECT user_id, username FROM users WHERE role = 'supplier' ORDER BY username");
$devices = mysqli_query($conn, "SELECT device_id, device_name, device_price FROM devices ORDER BY device_name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Device Order</title>
    <style>
        .page-title {
            font-weight: 700;
        }

        .page-subtitle {
            color: #6c757d;
        }

        .form-control,
        .form-select {
            border-radius: 18px;
        }

        .btn-primary {
            border-radius: 18px;
        }

        .card-section {
            border: none;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #212529;
        }

        .device-row,
        .supplier-row,
        .info-row {
            padding: 16px;
            border: 1px solid #e9ecef;
            border-radius: 14px;
            margin-bottom: 12px;
            background: #fafafa;
        }


        .form-label {
            font-weight: 600;
            margin-bottom: .5rem;
        }

        .submit-container {
            margin-top: 32px;
        }

        .card {
            border-radius: 18px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <div class="page-header">
                <div class="mb-4">
                    <h2 class="page-title"><i class="fa-solid fa-cart-plus me-2"></i>Create Device Order</h2>
                    <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Create a new device order</p>
                </div>
            </div>

            <form action="createorder.php" method="POST">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="card-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="section-title"><i class="fa-solid fa-user-tie me-2"></i>Supplier Information</h5>
                            </div>

                            <div class="supplier-row">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fa-solid fa-user me-2"></i>Supplier</label>
                                        <select name="supplier_id" class="form-select" required>
                                            <option value="">Select Supplier</option>
                                            <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                                <option value="<?= $supplier['user_id'] ?>">
                                                    <?= htmlspecialchars($supplier['username']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="section-title mb-0"><i class="fa-solid fa-box me-2"></i>Devices To Order</h5>
                                <button type="button" id="addDevice" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus me-2"></i>Add Device</button>
                            </div>

                            <div id="deviceContainer">
                                <div class="device-row">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label"><i class="fa-solid fa-box me-2"></i>Device</label>
                                            <select name="device_id[]" class="form-select" required>
                                                <option value="">Select Device</option>
                                                <?php mysqli_data_seek($devices, 0);
                                                while ($device = mysqli_fetch_assoc($devices)): ?>
                                                    <option value="<?= $device['device_id'] ?>" data-price="<?= $device['device_price'] ?>">
                                                        <?= htmlspecialchars($device['device_name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label"><i class="fa-solid fa-hashtag me-2"></i>Quantity</label>
                                            <input type="number" name="quantity[]" min="1" value="1" class="form-control" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label"><i class="fa-solid fa-product-tag me-2"></i>Unit Price</label>
                                            <input type="text" class="form-control unit-price" readonly>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label"><i class="fa-solid fa-coins me-2"></i>Total</label>
                                            <input type="text" class="form-control total-price" readonly>
                                        </div>

                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger remove-device w-100"><i class="fa-solid fa-trash me-2"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="section-title"><i class="fa-solid fa-circle-info me-2"></i>Additional Information</h5>
                            </div>

                            <div class="info-row">
                                <label class="form-label"><i class="fa-solid fa-comments me-2"></i>Order Remarks</label>
                                <textarea name="remarks" class="form-control" rows="4" placeholder="Optional notes for supplier"></textarea>
                            </div>
                        </div>

                        <div class="submit-container">
                            <div class="submit-buttons d-flex gap-2 justify-content-center">
                                <button type="submit" name="create_order" class="btn btn-success px-4 py-2"><i class="fa-solid fa-check me-2"></i>Submit Order</button>
                                <a href="deviceordermanagementview.php" class="btn btn-outline-secondary px-4 py-2"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>

    <script>
        document.getElementById('addDevice')
            .addEventListener('click', function () {
                const firstRow = document.querySelector('.device-row');
                const clone = firstRow.cloneNode(true);
                clone.querySelector('select').value = '';
                clone.querySelector('input').value = 1;
                document.getElementById('deviceContainer').appendChild(clone);
            });

        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-device');
            if (removeBtn) {
                const rows = document.querySelectorAll('.device-row');
                if (rows.length > 1) {
                    removeBtn.closest('.device-row').remove();
                }
            }
        });

        function updatePrices(row) {
            const select = row.querySelector("select");
            const qty = row.querySelector("input[name='quantity[]']");
            const unitPriceInput = row.querySelector(".unit-price");
            const totalInput = row.querySelector(".total-price");
            const unitPrice = parseFloat(select.selectedOptions[0].dataset.price || 0);
            const quantity = parseInt(qty.value) || 0;
            unitPriceInput.value = "RM " + unitPrice.toFixed(2);
            totalInput.value = "RM " + (unitPrice * quantity).toFixed(2);
        }

        document.addEventListener("change", function(e){
            if(e.target.matches("select")){
                updatePrices(e.target.closest(".device-row"));
            }
        });

        document.addEventListener("input", function(e){
            if(e.target.name==="quantity[]"){
                updatePrices(e.target.closest(".device-row"));
            }
        });
    </script>
</body>

</html>