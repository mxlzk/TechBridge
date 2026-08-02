<?php
session_start();
include __DIR__ . '/../config.php';

// Check if user is logged in and is an admin
$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$statusIcon = match ($user['status']) {
    "active" => "fa-solid fa-check-circle me-2",
    "inactive" => "fa-solid fa-times-circle me-2",
    default => "fa-solid fa-user-circle me-2",
};

$userStatusBadgeClass = match ($user['status']) {
    "active" => "status-badge status-active",
    "inactive" => "status-badge status-inactive",
    default => "status-badge status-pending",
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <style>
        .profile-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .06);
        }

        .profile-title {
            font-weight: 700;
        }

        .profile-subtitle {
            color: #6c757d;
        }

        .profile-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 30px;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            margin: auto;
            border-radius: 50%;
            overflow: hidden;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-weight: 700;
            margin: 20px 0;
        }

        .role-badge {
            background: #e7f1ff;
            color: #0d6efd;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: .9rem;
            font-weight: 600;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 18px;
            padding: 25px;
            height: 100%;
        }

        .info-card h5 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-item label {
            display: block;
            color: #6c757d;
            font-size: .85rem;
            margin-bottom: 4px;
        }

        .info-item p {
            margin: 0;
            font-weight: 600;
            color: #212529;
        }

        .status-badge{
            display:inline-flex;
            align-items:center;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight:600;
            font-size:.875rem;
        }

        .status-active{
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive{
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pending{
            background: #fef3c7;
            color: #92400e;
        }

        .profile-avatar span {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-lg {
            padding: 12px 30px;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php'; ?>

    <div class="content">
        <div class="container py-4">
            <div class="profile-card">

                <!-- Profile Header -->
                <div class="profile-header text-center">
                    <h2 class="profile-title"><i class="fa-solid fa-user me-2"></i>View Profile</h2>
                    <p class="profile-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Here's your profile information</p>

                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
                        <?php else: ?>
                            <span>
                                <?= strtoupper(substr($user['username'], 0, 1)); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h2 class="profile-name"><?= htmlspecialchars($user['username']); ?></h2>
                    <span class="role-badge"><i class="fa-solid fa-user-secret me-2"></i><?= htmlspecialchars(ucfirst($user['role'])); ?> </span>
                </div>

                <!-- Information -->
                <div class="row g-4 mt-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fa-solid fa-info-circle me-2"></i>Account Information</h5>

                            <div class="info-item">
                                <label class="mt-2"><i class="fa-solid fa-user me-2"></i>Username</label>
                                <p><?= htmlspecialchars($user['username']); ?></p>
                            </div>

                            <div class="info-item">
                                <label class="mt-2"><i class="fa-solid fa-envelope me-2"></i>Email Address</label>
                                <p><?= htmlspecialchars($user['email']); ?></p>
                            </div>

                            <div class="info-item">
                                <label class="mt-2"><i class="fa-solid fa-user-tie me-2"></i>Role</label>
                                <p><?= htmlspecialchars(ucfirst($user['role'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fa-solid fa-user-check me-2"></i>Account Status</h5>
                            <div class="info-item">
                                <label>Status</label>
                                <p>
                                    <span class="<?= $userStatusBadgeClass ?>">
                                        <i class="<?= $statusIcon ?>"></i><?= htmlspecialchars(ucfirst($user['status'])); ?>
                                    </span>
                                </p>
                            </div>

                            <div class="info-item">
                                <label><i class="fa-solid fa-id-card me-2"></i>Account ID</label>
                                <p><?= htmlspecialchars($user['user_id']); ?></p>
                            </div>

                            <div class="info-item">
                                <label><i class="fa-solid fa-calendar-check me-2"></i>Member Since</label>
                                <p><?= htmlspecialchars(date('d M Y', strtotime($user['created_at']))); ?></p>
                            </div>

                            <div class="info-item">
                                <label><i class="fa-solid fa-clock me-2"></i>Last Updated</label>
                                <p><?= htmlspecialchars(date('d M Y', strtotime($user['updated_at']))); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                            <a href="updateuserprofile.php?user_id=<?= urlencode($user['user_id']) ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Update Profile</a>
                        <?php endif; ?>
                        <a href="userdashboard.php" class="btn btn-success"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'userfooter.php'; ?>
</body>

</html>