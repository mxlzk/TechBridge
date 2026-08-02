<?php
session_start();
require_once __DIR__ . '/../config.php';

// Get the user ID from the session
$userId = (int) $_SESSION['user_id'];

// Get search query
$search = trim($_GET['search'] ?? '');

// Search logic
if (!empty($search)) {
    $sql = "SELECT dr.request_group_id, COUNT(*) AS total_devices , SUM(dr.device_price * dr.quantity) AS grand_total, dr.rental_category, dr.rental_duration, dr.payment_method, dr.request_status, MIN(dr.created_at) AS created_at 
    FROM device_requests dr 
    INNER JOIN devices d ON dr.device_id = d.device_id 
    WHERE dr.user_id = ? 
    AND (dr.request_group_id LIKE ? 
    OR d.device_name LIKE ? 
    OR d.device_type LIKE ?
    OR dr.rental_category LIKE ? 
    OR CAST(dr.rental_duration AS CHAR) LIKE ? 
    OR dr.payment_method LIKE ? 
    OR dr.request_status LIKE ?) 
    GROUP BY dr.request_group_id 
    ORDER BY dr.request_group_id DESC";

    $stmt = $conn->prepare($sql);
    $keyword = "%{$search}%";
    $stmt->bind_param("isssssss", $userId, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();

    $result = $stmt->get_result();
} else {
    // Get all requests for the user
    $stmt = $conn->prepare("SELECT dr.request_group_id, COUNT(*) AS total_devices , SUM(dr.device_price * dr.quantity) AS grand_total, dr.rental_category, dr.rental_duration, dr.payment_method, dr.request_status, MIN(dr.created_at) AS created_at 
    FROM device_requests dr 
    INNER JOIN devices d ON dr.device_id = d.device_id 
    WHERE dr.user_id = ? 
    GROUP BY dr.request_group_id 
    ORDER BY dr.request_group_id DESC");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
}

// Check if the query was successful
if (!$result) {
    die($conn->error);
}

// Initialize status counts
$status = ['Pending' => 0, 'Under Review' => 0, 'Approved' => 0, 'Collected' => 0, 'Returned' => 0, 'Rejected' => 0];
$requests = [];

// Loop through the results
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;

    // Increment the status count
    if (isset($status[$row['request_status']])) {
        $status[$row['request_status']]++;
    }
}

// Calculate the total number of requests
$totalRequests = count($requests);

// Define status classes
$statusClasses = ['Pending' => 'status-pending', 'Under Review' => 'status-review', 'Approved' => 'status-approved', 'Collected' => 'status-collected', 'Returned' => 'status-returned', 'Rejected' => 'status-rejected'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status</title>
    <style>
        .summary-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .summary-pill {
            background: white;
            border-radius: 16px;
            padding: 14px 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 140px;
            transition: all 0.25s ease;
        }

        .summary-pill:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.15);
        }

        .summary-pill span {
            color: #6c757d;
            font-size: .9rem;
        }

        .summary-pill strong {
            font-size: 1.2rem;
        }

        .request-details {
            border: 1px solid #dee2e6;
            border-radius: 14px;
            padding: 22px;
            background: #fafafa;
            font-size: 0.8rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .05);
        }

        /* Header */
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f3f5;
        }

        .request-id {
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
        }

        /* Rows */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .85rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-row:last-of-type {
            border-bottom: none;
        }

        .detail-label {
            color: #6c757d;
            font-size: .95rem;
            font-weight: 500;        
        }

        .detail-row strong {
            font-weight: 600;
            text-align: right;
            font-size: .9rem;
        }

        /* Badge */
        .req-status-badge {
            padding: .55rem 1rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Pending */
        .status-pending,
        .status-review{
            background: #fff8e1;
            color: #9a6700;
        }

        /* Positive statuses */
        .status-approved,
        .status-collected,
        .status-returned {
            background: #edf7ed;
            color: #1e7e34;
        }

        /* Rejected */
        .status-rejected {
            background: #fdeaea;
            color: #c82333;
        }

        .empty-state {
            background: white;
            border-radius: 18px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-weight: 700;
        }

        .page-subtitle {
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php' ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php' ?>
            <div class="dashboard-container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex flex-column ">
                        <h1 class="page-title"><i class="fa-solid fa-box-open me-2"></i>Device Rental Requests</h1>
                        <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>View your rental request status</p>
                    </div>

                    <form class="d-flex ms-auto me-3" method="GET" id="searchForm">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                            <input class="form-control" type="search" name="search" placeholder="Search for device or device type" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                    </form>
                </div>

                <div class="col-12 mb-4 d-flex justify-content-center">
                    <div class="summary-container">
                        <div class="summary-pill">
                            <span><i class="fa-solid fa-box-open me-2"></i>Total</span>
                            <strong class="ms-2"><?= count($requests) ?></strong>
                        </div>

                        <div class="summary-pill">
                            <span><i class="fa-solid fa-spinner me-2"></i>Pending</span>
                            <strong class="ms-2"><?= $status['Pending'] ?></strong>
                        </div>

                        <div class="summary-pill">
                            <span><i class="fa-solid fa-circle-check me-2"></i>Approved</span>
                            <strong class="ms-2"><?= $status['Approved'] ?></strong>
                        </div>

                        <div class="summary-pill">
                            <span><i class="fa-solid fa-box-open me-2"></i>Collected</span>
                            <strong class="ms-2"><?= $status['Collected'] ?></strong>
                        </div>

                        <div class="summary-pill">
                            <span><i class="fa-solid fa-circle-xmark me-2"></i>Rejected</span>
                            <strong class="ms-2"><?= $status['Rejected'] ?></strong>
                        </div>

                        <div class="summary-pill">
                            <span><i class="fa-solid fa-circle-check me-2"></i>Returned</span>
                            <strong class="ms-2"><?= $status['Returned'] ?></strong>
                        </div>
                    </div>
                </div>

                <?php if (!empty($requests)): ?>
                    <div class="row g-4">
                        <?php foreach ($requests as $request): ?>
                            <?php $statusClass = $statusClasses[$request['request_status']] ?? 'status-rejected'; ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="request-details">
                                    <!-- Header -->
                                    <div class="request-header mb-2 d-flex justify-content-between">
                                        <div>
                                            <h5 class="request-id mb-2"><i class="fa-solid fa-box-open me-2"></i><?= htmlspecialchars($request['request_group_id']); ?></h5>

                                            <small class="text-muted"><i class="fa-solid fa-calendar-days me-2"></i>Submitted on <?= htmlspecialchars(date('d M Y', strtotime($request['created_at']))); ?></small>
                                        </div>

                                        <span class="req-status-badge <?= $statusClass ?>"><?= htmlspecialchars($request['request_status']); ?></span>
                                    </div>

                                    <!-- Details -->
                                     <div class="detail-row">
                                        <span class="detail-label"><i class="fa-solid fa-money-bill me-2"></i>Grand Total</span>
                                        <strong>MYR <?= number_format((float)$request['grand_total'], 2); ?></strong>
                                     </div>

                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fa-solid fa-box-open me-2"></i>Devices Requested</span>
                                        <strong><?= $request['total_devices']; ?></strong>
                                    </div>

                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fa-solid fa-tag me-2"></i>Rental Category</span>
                                        <strong><?= ucfirst($request['rental_category']); ?></strong>
                                    </div>

                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fa-solid fa-hourglass-half me-2"></i>Rental Duration</span>
                                        <strong><?= $request['rental_duration']; ?> Year/s</strong>
                                    </div>

                                    <div class="detail-row">
                                        <span class="detail-label"><i class="fa-solid fa-credit-card me-2"></i>Payment Method</span>
                                        <strong><?= htmlspecialchars($request['payment_method']); ?></strong>
                                    </div>

                                    <!-- Action -->
                                    <div class="mt-4">
                                        <a href="viewdevicerequest.php?group=<?= urlencode($request['request_group_id']); ?>" class="btn btn-outline-primary w-100"><i class="fa-solid fa-eye me-2"></i>View Requested Devices</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3><i class="fa-solid fa-triangle-exclamation me-2"></i>No Requests Found</h3>
                            <p class="text-muted"><i class="fa-solid fa-circle-info me-2"></i>You have not submitted any device rental requests yet.</p>
                            <a href="devicelist.php" class="btn btn-primary"><i class="fa-solid fa-box-open me-2"></i>Browse Devices</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php' ?>
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
    </script>
</body>

</html>