<?php
session_start();
include __DIR__ . '/../config.php';

$search = trim($_GET['search'] ?? '');

if(!empty($search)) {
    $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR role LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    $keyword = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $keyword, $keyword, $keyword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management View</title>
    <style>
        .page-wrapper {
            padding: 40px 0;
        }

        .page-header {
            margin-bottom: 35px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #212529;
        }

        .page-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }

        .user-card {
            border: none;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .card-body {
            padding: 1.5rem;
        }

        .user-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            overflow: hidden;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 18px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #212529;
        }

        .user-email {
            font-size: 0.92rem;
            color: #6c757d;
            margin-bottom: 15px;
            word-break: break-word;
        }

        .role-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .role-admin {
            background: #ffe8cc;
            color: #d97706;
        }

        .role-user {
            background: #e7f5ff;
            color: #0b7285;
        }

        .role-supplier {
            background: #e6f4ea;
            color: #198754;
        }

        .btn-action {
            border-radius: 10px;
            font-weight: 600;
        }

        .empty-state {
            background: #ffffff;
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        .empty-state h4 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 0;
        }

        .user-avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 80%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>

            <!-- Header -->
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h2 class="page-title"><i class="fa-solid fa-users me-2"></i>User Management </h2>
                    <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Manage all registered user accounts </p>
                </div>

                <form class="d-flex ms-auto me-3" method="GET" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input class="form-control" type="search" name="search" placeholder="Search for user" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </form>

                <div class="d-flex justify-content-end">
                    <a href="adduser.php" class="btn btn-primary px-4 py-2 btn-action"><i class="fa-solid fa-plus me-2"></i>Add New User </a>
                </div>
            </div>

            <!-- User Count -->
            <?php if ($result && $result->num_rows > 0): ?>
                <p class="text-muted mb-4"><i class="fa-solid fa-users me-2"></i> <?= $result->num_rows ?> registered user(s) </p>
            <?php endif; ?>

            <!-- User Grid -->
            <div class="row g-4">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?> <?php $role = strtolower($user['role']); $roleClass = match ($role) {'admin' => 'role-admin', 'supplier' => 'role-supplier', default => 'role-user'}; ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 user-card ">
                                <div class="card-body d-flex flex-column"> <!-- User Avatar -->

                                    <div class="user-avatar">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="user-avatar-img">
                                        <?php else: ?>
                                            <i class="fa-solid fa-user"></i>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Username -->
                                    <h5 class="user-name"><i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($user['username']); ?></h5>

                                    <!-- Email -->
                                    <div class="user-email"><i class="fa-solid fa-envelope me-2"></i><?= htmlspecialchars($user['email']); ?></div>

                                    <!-- Role Badge -->
                                    <div class="role-badge <?= $roleClass; ?>"><i class="fa-solid fa-user-tag me-2"></i><?= htmlspecialchars($user['role']); ?></div>

                                    <!-- Action Buttons -->
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="viewuser.php?user_id=<?= $user['user_id']; ?>" class="btn btn-sm btn-primary flex-fill btn-action"><i class="fa-solid fa-eye me-2"></i>View</a>
                                        <a href="edituser.php?user_id=<?= $user['user_id']; ?>" class="btn btn-sm btn-outline-warning flex-fill btn-action"><i class="fa-solid fa-pen-to-square me-2"></i>Edit</a>
                                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                            <a href="deleteuser.php?user_id=<?= $user['user_id']; ?>" class="btn btn-sm btn-outline-danger flex-fill btn-action" onclick="return confirm('Delete this user?');"><i class="fa-solid fa-trash me-2"></i>Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="col-12">
                        <div class="empty-state">
                            <h4><i class="fa-solid fa-users-slash me-2"></i>No Users Found </h4>
                            <p>No user accounts were found matching your search criteria.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>
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