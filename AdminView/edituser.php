<?php
session_start();
require_once __DIR__ . '/../config.php';

// Initialize variables
$errorMessage = '';
$selectedUser = ['user_id' => '', 'username' => '', 'email' => '', 'role' => '', 'status' => '', 'password' => '', 'profile_image' => ''];

if (isset($_GET['user_id']) && ctype_digit($_GET['user_id'])) {

    $userId = (int) $_GET['user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $selectedUser = $row;
    } else {
        $_SESSION['error'] = 'User not found.';
        header('Location: usermanagementview.php');
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = (int) $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);

    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    $fileName = $_POST['existing_image'] ?? '';

    if (empty($username) || empty($email) || empty($role) || empty($status)) {

        $_SESSION['error'] = 'All required fields must be filled.';

        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    // Check for duplicate username or email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $stmt->bind_param("ssi", $username, $email, $userId);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Sorry, the username or email is already taken.';
        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    $stmt->close();

    $uploadDir = __DIR__ . '/../profileimages/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // If a new image is uploaded, process it
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // Validate file extension
        if (!in_array($extension, $allowedExtensions)) {

            $_SESSION['error'] = 'Invalid image format.';
            header("Location: edituser.php?user_id=$userId");
            exit();
        }

        // Generate a unique file name to prevent overwriting
        $fileName = uniqid('profile_', true) . '.' . $extension;
        $targetFile = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $profileImage = $fileName;
        } else {
            $_SESSION['error'] = 'Failed to upload profile image.';
            header("Location: edituser.php?user_id=$userId");
            exit();
        }
    }

    if (!empty($password) && (strlen($password) < 4 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))) {
        $_SESSION['error'] = 'Password must be at least 4 characters long and contain both letters and numbers.';
        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        $_SESSION['error'] = 'Username must be between 3 and 50 characters.';
        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    // Validate password if provided
    if (!empty($password) && $password !== $confirmPassword) {

        $_SESSION['error'] = 'Passwords do not match.';
        header("Location: edituser.php?user_id=$userId");
        exit();
    }

    // If password fields are filled, validate the password
    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, password = ?, profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $username, $email, $role, $status, $password, $profileImage, $userId);
    } else {
        // If password is not being updated, exclude it from the query
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, profile_image = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $username, $email, $role, $status, $profileImage, $userId);
    }

    // Execute the query
    if ($stmt->execute()) {
        if ($_SESSION['user_id'] == $userId) {
            $_SESSION['username'] = $username;
            if (!empty($profileImage)) {
                $_SESSION['profile_image'] = $profileImage;
            }
            $_SESSION['role'] = $role;
        }
        $_SESSION['success'] = 'User updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update user.';
    }

    $stmt->close();

    header('Location: usermanagementview.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body {
            background-color: #f5f7fb;
        }

        .edit-user-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #6c757d;
        }

        .form-section {
            margin-top: 40px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #212529;
        }

        .custom-input {
            border-radius: 14px;
            padding: 14px 16px;
            border: 1px solid #dee2e6;
        }

        .custom-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .profile-preview {
            margin-bottom: 20px;
        }

        .profile-image {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #f1f3f5;
        }

        .profile-placeholder {
            width: 130px;
            height: 130px;
            margin: 0 auto;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            font-size: 2.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action {
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 600;
        }

        .empty-state {
            background: #ffffff;
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>

            <div class="edit-user-card">
                <!-- Header -->
                <div class="form-header">
                    <h2 class="form-title">Edit User</h2>
                    <p class="form-subtitle">Update account information, permissions, and security settings.
                    </p>
                </div>

                <!-- Form -->
                <form action="edituser.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedUser['user_id']); ?>">
                    <input type="hidden" name="existing_image"
                        value="<?= htmlspecialchars($selectedUser['profile_image']); ?>">

                    <!-- Profile Preview -->
                    <div class="profile-preview text-center mb-5">
                        <?php if (!empty($selectedUser['profile_image'])): ?>
                            <img src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($selectedUser['profile_image']); ?>"
                                alt="Profile Picture" class="profile-image">
                        <?php else: ?>
                            <div class="profile-placeholder">
                                <?= strtoupper(substr($selectedUser['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Account Information -->
                    <div class="form-section">
                        <h5 class="section-title">Account Information</h5>
                        <div class="row g-4">

                            <!-- Username -->
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control custom-input" id="username" name="username"
                                    value="<?= htmlspecialchars($selectedUser['username']); ?>" required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control custom-input" id="email" name="email"
                                    value="<?= htmlspecialchars($selectedUser['email']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="form-section">
                        <h5 class="section-title">Access & Permissions</h5>
                        <div class="row g-4">

                            <!-- Role -->
                            <div class="col-md-6">
                                <label for="role" class="form-label">Assigned Role</label>
                                <select class="form-select custom-input" id="role" name="role"
                                    value="<?= htmlspecialchars($selectedUser['role']); ?>" required>
                                    <option value="">Select Role</option>
                                    <option value="admin" <?= ($selectedUser['role'] === 'admin') ? 'selected' : '' ?>>
                                        Admin</option>
                                    <option value="user" <?= ($selectedUser['role'] === 'user') ? 'selected' : '' ?>>User
                                    </option>
                                    <option value="supplier" <?= ($selectedUser['role'] === 'supplier') ? 'selected' : '' ?>>Supplier</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label">Account Status</label>

                                <select class="form-select custom-input" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" <?= ($selectedUser['status'] === 'active') ? 'selected' : '' ?>>
                                        Active</option>
                                    <option value="inactive" <?= ($selectedUser['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="form-section">
                        <h5 class="section-title">Security</h5>
                        <div class="row g-4">
                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control custom-input" id="password"
                                        name="password" placeholder="Leave blank to keep current password">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="generatePassword()">Generate Password</button>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control custom-input" id="confirm_password"
                                        name="confirm_password" placeholder="Re-enter new password">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="clearPassword()">Clear Password</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Picture -->
                    <div class="form-section">
                        <h5 class="section-title">Profile Picture</h5>
                        <input type="file" class="form-control custom-input" id="profile_image" name="profile_image">
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-3 mt-5">
                        <button type="submit" class="btn btn-primary btn-action">Update User</button>
                        <a href="usermanagementview.php" class="btn btn-outline-secondary btn-action">Return</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>

    <script>
        function generatePassword() {
            const chars =
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';

            let password = '';

            for (let i = 0; i < 12; i++) {
                password += chars.charAt(
                    Math.floor(Math.random() * chars.length)
                );
            }

            document.getElementById('password').value = password;
            document.getElementById('confirm_password').value = password;
        }

        function clearPassword() {
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
        }
    </script>
</body>

</html>