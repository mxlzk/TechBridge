<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (trim($_POST['username'] ?? ''));
    $email = (trim($_POST['email'] ?? ''));
    $password = (trim($_POST['password'] ?? ''));
    $confirm_password = (trim($_POST['confirm_password'] ?? ''));
    
    $role = "user";
    $status = "active";
    
    // Validate input fields 
    if ($username === '' || $email === '' || $password === '' || $role === '' || $status === '') 
    { 
        $_SESSION['error'] = 'Please fill in all required fields.'; 
        header('Location: registeruseraccount.php');
        exit(); 
    } 
    
    // Validate email format 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        $_SESSION['error'] = 'Please enter a valid email address.'; 
        header('Location: registeruseraccount.php'); 
        exit(); 
    } 
    
    // Check if email already exists 
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE email = ? OR username = ? "); 
    $stmt->bind_param("ss", $email, $username); 
    $stmt->execute(); 
    $stmt->store_result(); 
    
    if ($stmt->num_rows > 0) {

        $stmt->bind_result($existingEmail, $existingUsername);
        $stmt->fetch();
        
        if ($existingEmail === $email) {
            $_SESSION['error'] = "Sorry, this email is already taken.";
        } 
        elseif ($existingUsername === $username) {
            $_SESSION['error'] = "Username already exists.";
        }

        header("Location: registeruseraccount.php");
        exit();
    }

    if (strlen($password) < 6) { 
        $_SESSION['error'] = 'Password must be at least 6 characters long.'; 
        header('Location: registeruseraccount.php'); 
        exit(); 
    } 
    
    if (strlen($username) < 3 || strlen($username) > 50) { 
        $_SESSION['error'] = 'Username must be between 3 and 50 characters.'; 
        header('Location: registeruseraccount.php'); 
        exit(); 
    } 

    if ($password !== $confirm_password) { 
        $_SESSION['error'] = 'Passwords do not match.'; 
        header('Location: registeruseraccount.php'); 
        exit(); 
    } 
    
    // Prepare and execute the insert statement 
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)"); 
    $stmt->bind_param("sssss", $username, $email, $password, $role, $status); 
    
    // Execute the statement and set session messages based on the result 
    if ($stmt->execute()) { 
        $_SESSION['success'] = "User added successfully."; 
    } else { 
        $_SESSION['error'] = "Unable to create account.";
    }
    
    // Close the statement and database connection 
    $stmt->close(); 
    $conn->close(); 
    header("Location: loginaccount.php"); 
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e4e8f0);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .register-title {
            font-weight: 700;
            color: #212529;
        }

        .register-subtitle {
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
    <div class="register-card">
        <?php include 'includes/session_messages.php'; ?>
        <div class="text-center mb-4">
            <h2 class="register-title"><i class="fa-solid fa-user-plus me-2"></i>Register User Account</h2>
            <p class="register-subtitle"><i class="fa-solid fa-info-circle me-2"></i>Sign up to get started with TechBridge</p>
        </div>

        <form action="registeruseraccount.php" method="POST">
            <div class="mb-4">
                <label for="email" class="form-label"><i class="fa-solid fa-envelope me-2"></i>Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" autocomplete="email" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label"><i class="fa-solid fa-lock me-2"></i>Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()"><i class="fa-solid fa-eye me-2"></i></button>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label"><i class="fa-solid fa-lock me-2"></i>Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()"><i class="fa-solid fa-eye me-2"></i></button>
                </div>
            </div>

            <div class="mb-4">
                <label for="username" class="form-label"><i class="fa-solid fa-user-tag me-2"></i>Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-login"><i class="fa-solid fa-user-plus me-2"></i>Register Account</button>
            </div>

            <div class="text-center">
                <span class="text-muted">Already have an account?</span>
                <a href="loginaccount.php" class="register-link">Login here</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"></script>

    <!-- Password Toggle -->
    <script>
        function togglePassword() {

            const passwordInput = document.getElementById("password");
            const toggleBtn = document.querySelector(".toggle-password");
            const confirmPasswordInput = document.getElementById("confirm_password");
            const toggleConfirmBtn = document.querySelector(".toggle-password");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                passwordInput.type = "password";
                toggleBtn.textContent = "Show";
            }

            if (confirmPasswordInput.type === "password") {
                confirmPasswordInput.type = "text";
                toggleConfirmBtn.textContent = "Hide";
            } else {
                confirmPasswordInput.type = "password";
                toggleConfirmBtn.textContent = "Show";
            }
        }
    </script>
</body>

</html>