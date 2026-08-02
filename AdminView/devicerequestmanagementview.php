<?php
session_start();
include __DIR__ . '/../config.php';

// Get search query
$search = trim($_GET['search'] ?? '');

// Get all group request IDs and user details
if (!empty($search)) {
    $sql = "SELECT dr.request_group_id, u.username, SUM(dr.quantity) AS total_units, dr.rental_category, dr.rental_duration, dr.payment_method, dr.request_status, MIN(dr.created_at) AS created_at 
    FROM device_requests dr 
    INNER JOIN users u ON dr.user_id = u.user_id 
    WHERE dr.request_group_id LIKE ? 
    OR u.username LIKE ? 
    OR dr.rental_category LIKE ? 
    OR CAST(dr.rental_duration AS CHAR) LIKE ? 
    OR dr.payment_method LIKE ? 
    OR dr.request_status LIKE ?
    GROUP BY dr.request_group_id 
    ORDER BY MIN(dr.created_at) DESC";
    $stmt = $conn->prepare($sql);
    $keyword = "%{$search}%";
    $stmt->bind_param("ssssss", $keyword, $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    
    $result = $stmt->get_result();
} else {
    $sql = "SELECT dr.request_group_id, u.username, SUM(dr.quantity) AS total_units, dr.rental_category, dr.rental_duration, dr.payment_method, dr.request_status, MIN(dr.created_at) AS created_at FROM device_requests dr JOIN users u ON dr.user_id = u.user_id GROUP BY dr.request_group_id ORDER BY MIN(dr.created_at) DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Get status counts
$statusSql = "SELECT COUNT(DISTINCT request_group_id) AS total_requests, SUM(request_status = 'Pending') AS pending_count, SUM(request_status = 'Under Review') AS review_count, SUM(request_status = 'Approved') AS approved_count, SUM(request_status = 'Collected') AS collected_count, SUM(request_status = 'Returned') AS returned_count, SUM(request_status = 'Rejected') AS rejected_count FROM (SELECT request_group_id, request_status FROM device_requests GROUP BY request_group_id) grouped_requests";

$statusResult = mysqli_query($conn, $statusSql);
$statusCount = mysqli_fetch_assoc($statusResult);
$totalRequests = $statusCount['total_requests'];
$pendingCount = $statusCount['pending_count'] ?? 0;
$reviewCount = $statusCount['review_count'] ?? 0;
$approvedCount = $statusCount['approved_count'] ?? 0;
$collectedCount = $statusCount['collected_count'] ?? 0;
$returnedCount = $statusCount['returned_count'] ?? 0;
$rejectedCount = $statusCount['rejected_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Request Management</title>
    <style>
        .page-title {
            font-weight: 700;
        }

        .page-subtitle {
            color: #6c757d;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .request-card {
            background: #fff;
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .request-details {
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            padding: 14px 0;
            margin-bottom: 15px;
        }

        .request-id {
            font-weight: 700;
        }

        .request-user {
            color: #333;
            font-weight: 600;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        /* Pending + Under Review */
        .status-pending {
            background: #fff8e1;
            color: #8a6d1d;
        }

        .status-review {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-approved {
            background: #edf7ed;
            color: #1e7e34;
        }

        .status-collected {
            background: #cce5ff;
            color: #004085;
        }

        .status-returned {
            background: #d1e7dd;
            color: #06150b;
        }

        .status-rejected {
            background: #fdeaea;
            color: #c82333;
        }

        .request-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row span {
            color: #6c757d;
            font-size: .9rem;
        }

        .summary-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .summary-pill {
            background: white;
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
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
    </style>
</head>

<body>
    <?php include 'adminnavbar.php' ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>

            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex flex-column">
                    <h2 class="page-title"><i class="fa-solid fa-laptop me-2"></i>Device Requests</h2>
                    <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Monitor and manage all submitted device requests</p>
                </div>

                <form class="d-flex ms-auto w-25 me-1" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search for requests">
                    </div>
                </form>
            </div>

            <div class="col-12 mb-4 d-flex justify-content-center">
                <div class="summary-container mb-3">
                    <div class="summary-pill">
                        <i class="fa-solid fa-layer-group me-2"></i><span>Total</span>
                        <strong class="ms-2"><?= htmlspecialchars($totalRequests) ?></strong>
                    </div>

                    <div class="summary-pill">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Pending</span>
                        <strong class="ms-2"><?= htmlspecialchars($pendingCount) ?></strong>
                    </div>

                    <div class="summary-pill">
                        <i class="fa-solid fa-circle-info me-2"></i><span>Under Review</span>
                        <strong class="ms-2"><?= htmlspecialchars($reviewCount)?></strong>
                    </div>

                    <div class="summary-pill">
                        <i class="fa-solid fa-circle-check me-2"></i><span>Approved</span>
                        <strong class="ms-2"><?= htmlspecialchars($approvedCount) ?></strong>
                    </div>

                    <div class="summary-pill">  
                        <i class="fa-solid fa-circle-check me-2"></i><span>Collected</span>
                        <strong class="ms-2"><?= htmlspecialchars($collectedCount) ?></strong>
                    </div>

                    <div class="summary-pill">
                        <i class="fa-solid fa-circle-xmark me-2"></i><span>Rejected</span>
                        <strong class="ms-2"><?= htmlspecialchars($rejectedCount) ?></strong>
                    </div>

                    <div class="summary-pill">
                        <i class="fa-solid fa-circle-check me-2"></i><span>Returned</span>
                        <strong class="ms-2"><?= htmlspecialchars($returnedCount) ?></strong>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <?php
                        $status = $row['request_status'];
                        $category = strtolower($row['rental_category']);

                        $statusBadgeClass = match ($status) {
                            'Pending' => 'status-pending',
                            'Under Review' => 'status-review',
                            'Approved' => 'status-approved',
                            'Collected' => 'status-collected',
                            'Returned' => 'status-returned',
                            'Rejected' => 'status-rejected',
                            default => 'status-pending'
                        };

                        $statusIcon = match ($status) {
                            'Pending' => 'fa-solid fa-circle-info me-2',
                            'Under Review' => 'fa-solid fa-hourglass-start me-2',
                            'Approved' => 'fa-solid fa-circle-check me-2',
                            'Collected' => 'fa-solid fa-arrow-up me-2',
                            'Returned' => 'fa-solid fa-arrow-down me-2',
                            'Rejected' => 'fa-solid fa-circle-xmark me-2',
                            default => 'fa-solid fa-circle-info me-2'
                        };

                        $categoryIcon = match ($category) {
                            'school' => 'bi-backpack-fill me-2',
                            'tertiary' => 'bi-mortarboard-fill me-2',
                            'working' => 'bi-briefcase-fill me-2',
                            default => 'bi-person-fill me-2'
                        };
                        ?>

                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                            <div class="request-card">
                                <div class="card-header-row">
                                    <div class="mt-1 mb-2">
                                        <div class="request-id">
                                            <i class="fa-solid fa-id-card me-2"></i><?= htmlspecialchars($row['request_group_id']) ?>
                                        </div>

                                        <div class="request-user">
                                            <i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($row['username']) ?>
                                        </div>

                                        <div class="request-subtitle">
                                            <i class="fa-solid <?= $categoryIcon ?>"></i><?= ucfirst(htmlspecialchars($row['rental_category'])) ?>
                                            <span class="mx-2"></span>
                                            <i class="fa-solid fa-calendar me-2"></i><?= htmlspecialchars($row['rental_duration']) ?> Years
                                        </div>
                                    </div>
                                    <span class="status-badge <?= $statusBadgeClass ?>">
                                        <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars($status) ?>
                                    </span>
                                </div>

                                <div class="request-details">
                                    <div class="detail-row">
                                        <span><i class="fa-solid fa-layer-group me-2"></i>Units</span>
                                        <strong><?= $row['total_units'] ?></strong>
                                    </div>

                                    <div class="detail-row">
                                        <span><i class="fa-solid fa-credit-card me-2"></i>Payment</span>
                                        <strong><?= htmlspecialchars($row['payment_method']) ?></strong>
                                    </div>

                                    <div class="detail-row">
                                        <span><i class="fa-solid fa-calendar me-2"></i>Submitted</span>
                                        <strong><?= date('d M Y', strtotime($row['created_at'])) ?></strong>
                                    </div>
                                </div>

                                <div class="request-actions">
                                    <a href="reviewdevicerequest.php?request_group_id=<?= urlencode($row['request_group_id']) ?>" class="btn btn-outline-primary w-100"><i class="fa-solid fa-eye me-2"></i>Review Request</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <h5><i class="fa-solid fa-circle-exclamation me-2"></i>No device requests found</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php' ?>
    <script>
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.querySelector('input[name="search"]');
        let timer;

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    </script>
</body>

</html>