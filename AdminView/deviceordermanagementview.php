<?php
session_start();
include __DIR__ . '/../config.php';

// get the search query
$search = trim($_GET['search'] ?? '');

// search logic
if(!empty($search)) {
    $sql = "SELECT do.order_id, u.username, COUNT(*) AS total_devices, SUM(do.quantity) AS total_units, do.order_status, SUM(do.total_price) AS total_price, MIN(do.created_at) AS created_at
    FROM device_orders do JOIN users u ON do.supplier_id = u.user_id 
    WHERE do.order_id LIKE ? OR u.username LIKE ? OR do.order_status LIKE ?
    GROUP BY do.order_id ORDER BY MIN(do.created_at) DESC";

    $stmt = $conn->prepare($sql);
    $keyword = "%{$search}%";
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();

    $result = $stmt->get_result();
} else {
    $sql = "SELECT do.order_id, u.username, COUNT(*) AS total_devices, SUM(do.quantity) AS total_units, do.order_status, SUM(do.total_price) AS total_price, MIN(do.created_at) AS created_at
    FROM device_orders do JOIN users u ON do.supplier_id = u.user_id GROUP BY do.order_id ORDER BY MIN(do.created_at) DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
}

// total count for each status
$status = ['Pending' => 0, 'Approved' => 0, 'Preparing' => 0, 'Shipped' => 0, 'Delivered' => 0, 'Rejected' => 0];

// get all orders for the supplier
$orders = [];

// loop through the results
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
    if (isset($status[$row['order_status']])) {
        $status[$row['order_status']]++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <style>
        .page-title {
            font-weight: 700;
        }

        .page-subtitle {
            color: #6c757d;
        }

        .order-card {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .order-id {
            font-weight: 700;
        }

        .order-supplier {
            color: #6c757d;
        }

        .order-meta {
            display: flex;
            justify-content: space-between;
            font-size: .9rem;
        }

        .summary-scroll {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;

        }

        .summary-mini {
            background: white;
            border-radius: 16px;
            padding: 14px 18px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 140px;
            transition: all 0.25s ease;
        }

        .summary-mini:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.15);
        }

        .summary-mini span {
            color: #6c757d;
            font-size: .9rem;
        }

        .summary-mini strong {
            font-size: 1.2rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .status-pending { 
            background: #fff3cd; 
            color: #856404; 
        }
        
        .status-review { 
            background: #d1ecf1; 
            color: #0c5460; 
        }
        
        .status-approved { 
            background: #d4edda; 
            color: #155724; 
        }
        
        .status-preparing { 
            background: #cce5ff; 
            color: #004085; 
        }
        
        .status-shipped { 
            background: #ffeeba; 
            color: #856404; 
        }
        
        .status-delivered { 
            background: #d4edda; 
            color: #155724; 
        }

        .status-rejected { 
            background: #f8d7da; 
            color: #721c24; 
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex flex-column">
                    <h2 class="page-title"><i class="fa-solid fa-box-open me-2"></i>Order Management </h2>
                    <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i> Manage and monitor all available orders </p>
                </div>

                <form class="d-flex ms-auto me-3" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" placeholder="Search for order" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </form>

                <div class="d-flex justify-content-end">
                    <a href="createorder.php" class="btn btn-primary px-4 py-2 btn-action"> <i class="fa-solid fa-plus me-2"></i>Create New Order </a>
                </div>
            </div>

            <div class="col-12 mb-5 d-flex justify-content-center">
                <div class="summary-scroll">
                    <div class="summary-mini">
                        <i class="fa-solid fa-box-open me-2"></i><span>Total</span>
                        <strong class="ms-2"><?= count($orders) ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Pending</span>
                        <strong class="ms-2"><?= $status['Pending'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Preparing</span>
                        <strong class="ms-2"><?= $status['Preparing'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Shipped</span>
                        <strong class="ms-2"><?= $status['Shipped'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Delivered</span>
                        <strong class="ms-2"><?= $status['Delivered'] ?></strong>
                    </div>

                    <div class="summary-mini">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Rejected</span>
                        <strong class="ms-2"><?= $status['Rejected'] ?></strong>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $statusClass = match ($order['order_status']) {
                        'Pending' => 'status-pending',
                        'Approved' => 'status-approved',
                        'Preparing' => 'status-approved',
                        'Shipped' => 'status-approved',
                        'Delivered' => 'status-approved',
                        'Rejected' => 'status-rejected',
                        default => 'status-review'
                    };

                    $statusIcon = match ($order['order_status']) {
                        'Pending' => 'fa-solid fa-circle-info me-2',
                        'Approved' => 'fa-solid fa-circle-check me-2',
                        'Preparing' => 'fa-solid fa-box-open me-2',
                        'Shipped' => 'fa-solid fa-truck me-2',
                        'Delivered' => 'fa-solid fa-truck-pickup me-2',
                        'Rejected' => 'fa-solid fa-circle-xmark me-2',
                        default => 'fa-solid fa-circle-info me-2'
                    };
                    ?>

                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                        <div class="order-card">
                            <div class="card-header-row">
                                <div class="mb-3">
                                    <div class="order-id"><i class="fa-solid fa-cart-plus me-2"></i><?= htmlspecialchars($order['order_id']) ?></div>
                                    <div class="order-supplier"><i class="fa-solid fa-user-tie me-2"></i><?= htmlspecialchars($order['username']) ?></div>
                                </div>
                                <span class="status-badge <?= $statusClass ?>"><i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($order['order_status']) ?></span>
                            </div>

                            <div class="order-meta mt-3">
                                <div class="d-flex flex-column gap-2">
                                    <span><i class="fa-solid fa-box-open me-2"></i>Devices Ordered: <?= $order['total_devices'] ?></span>
                                    <span><i class="fa-solid fa-layer-group me-2"></i>Units Ordered: <?= $order['total_units'] ?></span>
                                    <span><i class="fa-solid fa-money-bill me-2"></i>MYR <?= $order['total_price'] ?></span>
                                </div>
                            </div>

                            <div class="order-meta border-top mt-3">
                                <div class="text-muted small mt-2">
                                    <i class="fa-solid fa-calendar-check me-2"></i>Submitted On: <span><?= date('d M Y', strtotime($order['created_at'])) ?></span>
                                </div>
                            </div>

                            <a href="viewdeviceorder.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn btn-outline-primary w-100 mt-3"><i class="fa-solid fa-eye me-2"></i>View Order Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted mt-4">No device orders available</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>

    <script>
        const searchForm = document.getElementById("searchForm")
        const searchInput = document.querySelector("input[name='search']")
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

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                searchForm.submit();
            }, 500);
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