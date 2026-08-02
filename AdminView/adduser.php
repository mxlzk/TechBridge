<?php
session_start();
include __DIR__ . '/../config.php';

// Handle form submission for adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));
    $role = htmlspecialchars(trim($_POST['role'] ?? ''));

    // Validate input fields
    if ($username === '' || $email === '' || $password === '' || $role === '') {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: adduser.php');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: adduser.php');
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Sorry, the username or email is already taken.";
        header("Location: adduser.php");
        exit();
    }

    if (!in_array($role, ['admin', 'user', 'supplier'])) {
        $_SESSION['error'] = 'Invalid role selected.';
        header('Location: adduser.php');
        exit();
    }

    if (strlen($password) < 4) {
        $_SESSION['error'] = 'Password must be at least 4 characters long.';
        header('Location: adduser.php');
        exit();
    }

    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $_SESSION['error'] = 'Password must contain both letters and numbers.';
        header('Location: adduser.php');
        exit();
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        $_SESSION['error'] = 'Username must be between 3 and 50 characters.';
        header('Location: adduser.php');
        exit();
    }

    // Prepare and execute the insert statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);

    // Execute the statement and set session messages based on the result
    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully.";
    } else {
        $_SESSION['error'] = "Error adding user: " . $stmt->error;
    }
    // Close the statement and database connection
    $stmt->close();
    $conn->close();

    header("Location: usermanagementview.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <style>
        .user-form-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        }

        .form-header {
            margin-bottom: 35px;

        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: #212529;
        }

        .custom-input {
            border-radius: 14px;
            padding: 14px 16px;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .form-helper-text {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .btn-action {
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 600;
        }

        .alert-modern {
            border-radius: 14px;
            margin-bottom: 25px;
        }
    </style>
</head>

<body>
    <?php include 'adminnavbar.php'; ?>
    <div class="content">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="user-form-card">

                        <!-- Header -->
                        <div class="form-header">
                            <h2 class="form-title"><i class="fa-solid fa-user-plus me-2"></i>Add New User</h2>
                            <p class="form-subtitle"><i class="fa-solid fa-circle-info me-2"></i>Create a new account and assign system access permissions.</p>
                        </div>

                        <!-- Session Alerts -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-modern">
                                <?= htmlspecialchars($_SESSION['success']); ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-modern">
                                <?= htmlspecialchars($_SESSION['error']); ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <!-- Form -->
                        <form action="adduser.php" method="POST" novalidate>
                            <div class="row g-4">

                                <!-- Username -->
                                <div class="col-md-6">
                                    <label for="username" class="form-label"><i class="fa-solid fa-user me-2"></i>Username</label>
                                    <input type="text" class="form-control custom-input" id="username" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label"><i class="fa-solid fa-envelope me-2"></i>Email Address</label>
                                    <input type="email" class="form-control custom-input" id="email" name="email" placeholder="Enter email address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label"><i class="fa-solid fa-lock me-2"></i>Password</label>
                                    <input type="password" class="form-control custom-input" id="password" name="password" placeholder="Create password" required>
                                    <div class="form-helper-text"><i class="fa-solid fa-circle-info me-2"></i>Use a strong password with letters and numbers.</div>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <label for="role" class="form-label"><i class="fa-solid fa-id-badge me-2"></i>Assigned Role</label>

                                    <select class="form-select custom-input" id="role" name="role" required>
                                        <option>Select Role</option>
                                        <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                                        <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>User</option>
                                        <option value="supplier" <?= (($_POST['role'] ?? '') === 'supplier') ? 'selected' : '' ?>>Supplier</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex flex-wrap gap-2 mt-5 justify-content-center">
                                <button type="submit" class="btn btn-primary btn-action px-4 py-2"><i class="fa-solid fa-plus me-2"></i>Add User</button>
                                <a href="usermanagementview.php" class="btn btn-outline-secondary btn-action px-4 py-2"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'adminfooter.php'; ?>
</body>

</html>