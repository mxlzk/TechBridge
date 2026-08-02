<?php
session_start();
include __DIR__ . '/../config.php';

$user = null;
$errorMessage = '';
$initial = '';
$roleClass = 'role-user';

// Validate user ID
if (!isset($_GET['user_id']) || !ctype_digit($_GET['user_id'])) {
    $errorMessage = 'A valid user ID was not provided.';
} else {
    $user_id = (int) $_GET['user_id'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");

    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // User display metadata
            $role = strtolower($user['role']);
            $roleClass = ($role === 'admin')
                ? 'role-admin'
                : 'role-user';

            $initial = strtoupper(substr($user['username'], 0, 1));
        } else {
            $errorMessage = 'The requested user record does not exist.';
        }
        $stmt->close();
    } else {
        $errorMessage = 'Failed to prepare database query.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User</title>
    <style>
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #212529;
        }

        .page-wrapper {
            padding: 40px 0;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .profile-avatar-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #212529;
        }

        .profile-email {
            color: #6c757d;
            margin-bottom: 15px;
        }

        .role-badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .role-admin {
            background: #ffe8cc;
            color: #d97706;
        }

        .role-user {
            background: #e7f5ff;
            color: #0b7285;
        }

        .info-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #212529;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            height: 100%;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }

        .btn-action {
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 600;
        }

        .empty-state {
            background: #ffffff;
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container page-wrapper">


            <!-- Error State -->
            <?php if ($errorMessage): ?>
                <div class="empty-state">
                    <h4>User Unavailable</h4>
                    <p><?= htmlspecialchars($errorMessage); ?></p>
                    <div class="mt-4">
                        <a href="usermanagementview.php" class="btn btn-outline-secondary btn-action">Return</a>
                    </div>
                </div>
            <?php elseif ($user): ?>

                <!-- Profile Card -->
                <div class="profile-card">

                    <div class="profile-header">
                        <h2 class="page-title"><i class="fa-solid fa-user me-2"></i>View User Profile</h2>
                        <p class="page-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Detailed information about the user account</p>
                    </div>

                    <!-- User Profile Header -->
                    <div class="profile-header">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="profile-avatar-img">
                        <?php else: ?>
                            <div class="profile-avatar">
                                <?= strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="profile-name"><i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($user['username']); ?></h2>
                        <div class="profile-email"><i class="fa-solid fa-envelope me-2"></i><?= htmlspecialchars($user['email']); ?></div>
                        <div class="role-badge <?= $roleClass; ?>"><i class="fa-solid fa-id-badge me-2"></i><?= htmlspecialchars($user['role']); ?></div>
                    </div>

                    <!-- Account Information -->
                    <div class="mb-4">
                        <h4 class="info-section-title"><i class="fa-solid fa-user me-2"></i>Account Information</h4>
                        <div class="row g-4">
                            <!-- Username -->
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label"><i class="fa-solid fa-user me-2"></i>Username</div>
                                    <div class="info-value"><?= htmlspecialchars($user['username']); ?></div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label"><i class="fa-solid fa-envelope me-2"></i>Email Address</div>
                                    <div class="info-value"><?= htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label"><i class="fa-solid fa-calendar-check me-2"></i>Account Created</div>
                                    <div class="info-value"><?= htmlspecialchars($user['created_at']); ?></div>
                                </div>
                            </div>

                            <!-- Updated At -->
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-label"><i class="fa-solid fa-calendar-check me-2"></i>Last Updated</div>
                                    <div class="info-value"><?= htmlspecialchars($user['updated_at']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-3 mt-5">
                        <a href="edituser.php?user_id=<?= $user['user_id']; ?>" class="btn btn-primary btn-action"><i class="fa-solid fa-pen-to-square me-2"></i>Edit User</a>
                        <a href="usermanagementview.php" class="btn btn-outline-secondary btn-action"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php $conn->close(); ?>

    <?php include 'adminfooter.php'; ?>
</body>

</html>