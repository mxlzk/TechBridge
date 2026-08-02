<?php
require_once __DIR__ . '/../config.php';

$requiredRole = 'user';
require_once '../includes/auth.php';

// To highlight the active page
$currentPage = basename($_SERVER['PHP_SELF']);

// Navigation items
$navItems = [
    ['label' => 'Dashboard', 'href' => 'userdashboard.php','icon' => 'bi-speedometer2'],
    ['label' => 'About Us', 'href' => 'aboutus.php', 'icon' => 'bi-info-circle'],
    ['label' => 'Device List', 'href' => 'devicelist.php', 'icon' => 'bi-box'],
    ['label' => 'Request Status', 'href' => 'reqstatus.php', 'icon' => 'bi-telephone'],
    ['label' => 'Cart', 'href' => 'cart.php', 'icon' => 'bi-cart'],
    ['label' => 'Logout', 'href' => BASE_URL . '/logout.php', 'icon' => 'bi-box-arrow-left', 'onclick' => "return confirm('Are you sure you want to logout?');"]
];

// To count the number of items in the cart
$cartCount = 0;

// Count items in cart for notification badge
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cartCount = (int) ($row['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: "Inter", sans-serif;
            background-color: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 40px;
        }

        .user-navbar {
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            z-index: 1030;
        }

        .navbar-brand {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            white-space: nowrap;
        }

        .navbar-nav {
            gap: 8px;
        }

        .nav-link {
            font-weight: 500;
            border-radius: 10px;
            padding: 10px 14px !important;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.12);
            font-weight: 600;
        }

        .user-profile {
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 80%;
            overflow: hidden;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .user-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .user-name {
            font-size: 0.95rem;
            font-weight: 500;
            white-space: nowrap;
        }

        @media (min-width: 992px) {

            .navbar-collapse {
                overflow-x: auto;
            }

            .navbar-collapse::-webkit-scrollbar {
                height: 4px;
            }

            .navbar-nav {
                flex-wrap: nowrap;
            }
        }

        /* --- Mobile and Tablet View --- */
        @media (max-width: 991px) {

            .navbar-nav {
                align-items: stretch !important;
                gap: 4px;
                margin-top: 12px;
            }

            .nav-link {
                width: 100%;
            }

            .user-profile {
                margin-top: 20px;
                margin-left: 0;
                padding-left: 0;
                border-left: none;
                border-top: 1px solid rgba(255, 255, 255, 0.15);
                padding-top: 16px;
            }

            .user-name {
                font-size: 0.9rem;
            }
        }

        .nav-link.position-relative {
            overflow: visible;
        }

        .cart-badge {
            position: absolute;
            top: 2px;
            right: -11px;
            min-width: 17px;
            height: 17px;
            padding: 0 5px;
            border-radius: 999px;
            background: #dc3545;
            color: white;
            font-size: .75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark user-navbar sticky-top">
        <div class="container-fluid px-4">
            <!-- Brand -->
            <a class="navbar-brand" href="userdashboard.php"><i class="fa-solid fa-laptop-code me-2"></i>TechBridge</a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar"
                aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="userNavbar">

                <!-- Navigation -->
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php foreach ($navItems as $item): ?>
                        <?php $isActive = $currentPage === $item['href']; ?>

                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($item['href']); ?>"
                                class="nav-link position-relative <?= $isActive ? 'active' : ''; ?>" title="<?= htmlspecialchars($item['label']); ?>" data-bs-toggle="tooltip" 
                                <?php if (isset($item['onclick'])): ?>
                                    onclick="<?= htmlspecialchars($item['onclick']); ?>"
                                <?php endif; ?>>
                                
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="bi <?= htmlspecialchars($item['icon']); ?> me-1"></i>
                                <?php endif; ?>

                                <?php if ($item['href'] === 'cart.php' && $cartCount > 0): ?>
                                    <span class="cart-badge">
                                        <?= $cartCount ?>
                                    </span>
                                <?php endif; ?>

                                <?= htmlspecialchars($item['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <!-- User Profile -->
                    <li class="nav-item">
                        <a href="userprofileview.php" class="d-flex align-items-center text-decoration-none user-profile">
                            <div class="me-3 text-white user-name d-none d-lg-block">Welcome,
                                <?php echo htmlspecialchars($_SESSION['username'] ?? "User"); ?>
                            </div>

                            <!-- Avatar -->
                            <div class="user-avatar">
                                <?php if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                    <img src="<?= BASE_URL ?>/profileimages/<?= htmlspecialchars($_SESSION['profile_image']); ?>"
                                        alt="Profile Picture" class="user-avatar-img">
                                <?php else: ?>
                                    <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>