<?php
session_start();
include __DIR__ . '/../config.php';

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$profile_image = $user['profile_image'];
$stmt->close();

if (isset($_POST['update_profile'])) {
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
            header("Location: updatesupplierprofile.php");
        }
        
        $fileName = uniqid('profile_', true) . '.' . $extension;
        $targetFile = $uploadDir . $fileName;

        if (!empty($user['profile_image'])) {
            $oldImage = __DIR__ . '/../profileimages/' . $user['profile_image'];

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }

        if(move_uploaded_file($_FILES['profile_image']['tmp_name'],$targetFile)){
            $profileImage = $fileName;
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $profileImage, $id);

            if($stmt->execute()) {
                $_SESSION['profile_image'] = $profileImage;
                $_SESSION["success"] = "Profile image for {$user['username']} updated successfully";
            } else {
                $_SESSION["error"] = "Failed to update profile image for {$user['username']}";
            }
        }else{
            $_SESSION['error']="Unable to upload image.";
        }
    }
    header("Location:updatesupplierprofile.php");
    exit();
}

if (isset($_POST['delete_profile'])) {
    $currentImage = $user['profile_image'];

    if (!empty($currentImage)) {
        $imagePath = __DIR__ . '/../profileimages/' . $currentImage;

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $stmt = $conn->prepare("UPDATE users SET profile_image = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            unset($_SESSION['profile_image']);
            $_SESSION['success'] = "Profile image removed successfully.";
        } else {
            $_SESSION['error'] = "Unable to remove profile image.";
        }
        $stmt->close();
    }
    header("Location:updatesupplierprofile.php");
    exit();
}

if (isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $currentPassword = $user['password'];

    if ($old_password !== $currentPassword) {
        $_SESSION["error"] = "Old password does not match";
    } 
    
    else if ($new_password !== $confirm_password) {
        $_SESSION["error"] = "Passwords do not match";
    }

    else if ($new_password === $currentPassword) {
        $_SESSION["error"] = "New password must be different from old password";
    }

    elseif (strlen($new_password) < 5) {
        $_SESSION['error'] = "Password must contain at least 5 characters.";
    }

    elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[\W_]/', $new_password)) {
        $_SESSION['error'] ="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    else {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_password, $id);
        if($stmt->execute()) {
            $_SESSION["success"] = "Password updated successfully";
        }
        else {
            $_SESSION["error"] = "Failed to update password";
        }
        $stmt->close();
    }
    header("Location: updatesupplierprofile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Supplier Profile</title>
    <style>
        .card{
            border-radius: 20px;
        }

        .card-body{
            padding: 32px;
        }

        .input-group-text{
            background: #fff;
        }

        .form-control{
            box-shadow: none;
        }

        .form-control:focus{
            box-shadow: none;
        }

        .btn{
            border-radius: 12px;
        }

        .progress{
            border-radius: 10px;
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
            width: 140px;
            height: 140px;
            object-fit: cover;
        }

        .password-wrapper {
            position: relative;
            flex-grow: 1;
        }

        .password-wrapper input{
            width:100%;
            padding-right:45px;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
        }

        .password-wrapper .toggle-password:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <?php include 'suppliernavbar.php' ?>
    <div class="content">
        <div class="container py-4">
            <?php include '../includes/session_messages.php'; ?>
            <div class="mb-4">
                <h2 class="fw-bold"><i class="fa-solid fa-shield-halved me-2"></i>Update Profile</h2>
                <p class="text-muted"><i class="fa-solid fa-user-shield me-2"></i>Manage your user credentials securely</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <form action="updatesupplierprofile.php" method="POST" enctype="multipart/form-data">
                                    <div class="profile-avatar" id="profilePreview">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img id="profilePreviewImage" src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
                                        <?php else: ?>
                                            <span id="profileInitial"><?= strtoupper(substr($user['username'], 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <h4 class="mt-3 fw-bold"><i class="fa-solid fa-user-shield me-2"></i><?= htmlspecialchars($user['username']); ?></h4>

                                    <div class="text-center mt-3">
                                        <label for="profile_image" class="btn btn-primary btn-sm mb-2"><i class="fa-solid fa-image me-2"></i>Select Profile Image</label>
                                        <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.webp" hidden>
                                        <p id="selectedFileName" class="small text-success mt-2 mb-0"></p>
                                        <p class="text-muted small mt-1 mb-0"><i class="fa-solid fa-info-circle me-2"></i>Allowed types: JPG, JPEG, PNG, WEBP</p>

                                        <div class="d-flex justify-content-center align-items-center gap-2 mt-3">
                                            <button type="submit" name="update_profile" id="updateButton" class="btn btn-success btn-sm" disabled><i class="fa-solid fa-save me-2"></i>Update Image</button>

                                            <?php if (!empty($user['profile_image'])): ?>
                                                <button type="submit" name="delete_profile" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove your profile picture?');"><i class="fa-solid fa-trash me-2"></i>Delete Image</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <strong><i class="fa-solid fa-user me-2"></i>Username:</strong>
                                <span><?= htmlspecialchars($user['username']); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><i class="fa-solid fa-envelope me-2"></i>Email:</strong>
                                <span><?= htmlspecialchars($user['email']); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong><i class="fa-solid fa-user-shield me-2"></i>Role:</strong>
                                <span><?= htmlspecialchars(ucfirst($user['role'])); ?></span>
                            </div>

                            <div class="alert alert-light border mt-4">
                                <h6 class="fw-bold"><i class="fa-solid fa-user-shield me-2"></i>Security Notice</h6>
                                <p class="mb-0 text-muted small">Updating your password immediately updates your user account's credentials. Never share your password with anyone.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            <form action="updatesupplierprofile.php" method="POST">
                                <div class="mb-3">
                                    <strong class="text-muted small">Current Password</strong>
                                    <div class="input-group mt-2">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <div class="password-wrapper">
                                            <input type="password" class="form-control" placeholder="Enter old password" id="old_password" autocomplete="current-password" name="old_password" value="<?= htmlspecialchars($user['password']); ?>" required>
                                            <button class="toggle-password" type="button"><i class="fa-solid fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong class="text-muted small">New Password</strong>
                                    <div class="input-group mt-2">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <div class="password-wrapper">
                                            <input type="password" minlength="5" class="form-control" placeholder="Enter new password" id="new_password" autocomplete="new-password" name="new_password" required>
                                            <button class="toggle-password" type="button"><i class="fa-solid fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong class="text-muted small">Confirm New Password</strong>
                                    <div class="input-group mt-2">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <div class="password-wrapper">
                                            <input type="password" class="form-control" placeholder="Confirm new password" id="confirm_password" autocomplete="new-password" name="confirm_password" required>
                                            <button class="toggle-password" type="button"><i class="fa-solid fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-light border mb-4">
                                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-info me-2"></i>Password Requirements</h6>
                                    <ul class="mb-0 small">
                                        <li>Minimum 5 characters</li>
                                        <li>At least one uppercase letter</li>
                                        <li>At least one lowercase letter</li>
                                        <li>At least one number</li>
                                        <li>At least one special character</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-center align-items-center gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary" name="update_password"><i class="fa-solid fa-floppy-disk me-2"></i>Update Password</button>
                                    <a href="supplierprofileview.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Return</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'supplierfooter.php' ?>

    <script>
        const fileInput = document.getElementById("profile_image");
        const updateButton = document.getElementById("updateButton");
        const fileName = document.getElementById("selectedFileName");
        const previewContainer = document.getElementById("profilePreview");

        fileInput.addEventListener("change", function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            // Validate file type
            const allowedTypes = ["image/jpeg","image/png","image/webp"];

            if (!allowedTypes.includes(file.type)) {
                alert("Please select a JPG, JPEG, PNG or WEBP image.");
                this.value = "";
                fileName.textContent = "";
                updateButton.disabled = true;
                return;
            }

            fileName.textContent = "Selected: " + file.name;
            updateButton.disabled = false;

            const reader = new FileReader();

            reader.onload = function (e) {
                let image = document.getElementById("profilePreviewImage");

                if (!image) {
                    previewContainer.innerHTML = "";
                    image = document.createElement("img");
                    image.id = "profilePreviewImage";
                    image.alt = "Profile Preview";
                    previewContainer.appendChild(image);
                }
                image.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        document.querySelectorAll(".toggle-password").forEach(button => {
            button.addEventListener("click", function () {
                const input = this.previousElementSibling;
                if (input.type === "password") {
                    input.type = "text";
                    this.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
                } else {
                    input.type = "password";
                    this.innerHTML = '<i class="fa-solid fa-eye"></i>';
                }
            });
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