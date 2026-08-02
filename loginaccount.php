<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: " . LOGIN_PAGE);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?"); 
    $stmt->bind_param("ss", $login, $login); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['error'] = "No account found with that username or email.";
        $stmt->close();
        $conn->close();
        header("Location: " . LOGIN_PAGE);
        exit();
    }

    $user = $result->fetch_assoc();

    if ($password !== $user['password']) {
        $_SESSION['error'] = "Incorrect password.";
        $stmt->close();
        $conn->close();
        header("Location: " . LOGIN_PAGE);
        exit();
    }

    // Successful login - set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_image'] = $user['profile_image'];

    $stmt->close();
    $conn->close();

    // Route user based on their role
    $roleRedirects = [
        'admin' => '/AdminView/admindashboard.php',
        'user' => '/UserView/userdashboard.php',
        'supplier' => '/SupplierView/supplierdashboard.php'
    ];

    if (array_key_exists($user['role'], $roleRedirects)) {
        header("Location: " . BASE_URL . $roleRedirects[$user['role']]);
        exit(); // CRITICAL: always call exit() after a header redirect
    }

    // Fallback for invalid role
    $_SESSION['error'] = "Invalid user role.";
    header("Location: " . LOGIN_PAGE);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e4e8f0);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #dcdfe4;
            padding: 12px 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .btn-login {
            height: 50px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
        }

        .register-link {
            text-decoration: none;
            font-weight: 500;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .toggle-password:hover {
            color: #212529;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <?php include 'includes/session_messages.php'; ?>
        <div class="text-center mb-4">
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to continue to TechBridge</p>
        </div>

        <form action="loginaccount.php" method="POST">

            <!-- Username -->
            <div class="mb-4">
                <label for="login" class="form-label">Username</label>
                <input type="text" class="form-control" id="login" name="login" placeholder="Enter your username or email" autocomplete="login" required>
            </div>

            <!-- Password -->
            <div class="mb-5">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <!-- Login Button -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-login">Login Account</button>
            </div>

            <!-- Register -->
            <div class="text-center">
                <span class="text-muted">Don't have an account?</span>
                <div class="d-grid mb-2">
                    <a href="registeruseraccount.php" class="btn btn-outline-primary btn-sm">Register as user here</a>
                </div>
                <div class="d-grid mb-2">
                    <a href="registersupplieraccount.php" class="btn btn-outline-primary btn-sm">Register as supplier here</a>
                </div>
                <div class="d-grid mb-2">
                    <a href="registeradminaccount.php" class="btn btn-outline-primary btn-sm">Register as admin here</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"></script>

    <!-- Password Toggle -->
    <script>
        function togglePassword() {

            const passwordInput = document.getElementById("password");
            const toggleBtn = document.querySelector(".toggle-password");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                passwordInput.type = "password";
                toggleBtn.textContent = "Show";
            }
        }
    </script>
</body>

</html>