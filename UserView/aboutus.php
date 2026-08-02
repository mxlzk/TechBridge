<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0a58ca;
            --bg-light: #f8f9fa;
            --text-dark: #212529;
            --text-muted: #6c757d;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 70px 20px;
            text-align: center;
            margin-bottom: 60px;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15);
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .hero-section p {
            font-size: 1.25rem;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.95;
        }

        .section-padding {
            padding: 60px 0;
        }

        .section-title {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            color: var(--text-dark);
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .content-block {
            background: var(--bg-light);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 60px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            text-align: center;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .content-block p {
            font-size: 1.1rem;
            color: #444444;
            margin-bottom: 0;
        }

        .card-custom {
            background: white;
            border-radius: 20px;
            padding: 40px 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-custom:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(13, 110, 253, 0.1);
            border-color: rgba(13, 110, 253, 0.2);
        }

        .card-custom h5,
        .card-custom .h5-like {
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .card-custom p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Process Flow Styles */
        .process-wrapper {
            position: relative;
            z-index: 1;
            margin-top: 20px;
        }

        /* Connecting lines between steps */
        .process-wrapper > div {
            position: relative;
        }

        .process-wrapper > div::after {
            content: '';
            position: absolute;
            top: 25px; /* Aligns with the center of the 50px step-number */
            left: 50%;
            width: 100%;
            height: 2px;
            background: rgba(13, 110, 253, 0.3);
            z-index: -1;
        }

        .process-wrapper > div:last-child::after {
            display: none;
        }

        @media (max-width: 767px) {
            .process-wrapper > div::after {
                display: none; /* Hide lines when stacked vertically on mobile */
            }
        }

        .process-step {
            text-align: center;
            padding: 0 15px;
            position: relative;
        }

        .process-step .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 25px;
            box-shadow: 0 0 0 8px white, 0 4px 10px rgba(13, 110, 253, 0.2);
            transition: all 0.3s ease;
        }

        .process-step:hover .step-number {
            transform: scale(1.1);
            box-shadow: 0 0 0 8px white, 0 6px 15px rgba(13, 110, 253, 0.35);
        }

        .process-step .h5-like {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .process-step p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.3;
            margin-bottom: 0;
        }

        .value-icon {
            font-size: 2.5rem;
            line-height: 1;
            margin-bottom: 20px;
            display: block;
            transition: transform 0.3s ease;
        }

        .card-custom:hover .value-icon {
            transform: scale(1.1);
        }

        .future-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .future-item {
            background: white;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            font-weight: 500;
            color: var(--text-dark);
            transition: all 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .future-item:hover {
            transform: translateX(5px);
            border-color: rgba(13, 110, 253, 0.2);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.08);
        }

        .future-item::before {
            content: '→';
            color: var(--primary-color);
            font-weight: bold;
            margin-right: 15px;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <?php include 'usernavbar.php'; ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="mb-0"><i class="fa fa-info-circle me-2"></i>About TechBridge</h1>
            <p class="mt-4">Empowering underserved communities through affordable and accessible technology.</p>
        </div>
    </div>

    <div class="container">
        <!-- Story Section -->
        <section class="section-padding pt-4">
            <div class="content-block">
                <h2 class="section-title"><i class="fa fa-lightbulb me-2"></i>Our Story</h2>
                <p class="mb-0">
                    <i class="fa fa-quote-left me-2"></i>
                    TechBridge was created to address the growing digital divide faced by underprivileged communities,
                    particularly students from Malaysia's B40 income group. Access to laptops, tablets, and other
                    digital devices has become essential for education, skill development, and career growth. However,
                    many individuals are unable to afford these resources. <br><br>TechBridge provides a platform that
                    connects users with affordable technology rental opportunities, ensuring that access to digital
                    tools is no longer a barrier to learning and professional development.
                    <i class="fa fa-quote-right me-2"></i>
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="h5-like"><i class="fa fa-flag me-2"></i>Our Mission</div>
                        <p class="mt-0">
                            <i class="fa fa-quote-left me-2"></i>
                            To empower underserved communities by providing affordable and accessible
                            technology rental services that support education, employment, and lifelong learning.
                            <i class="fa fa-quote-right me-2"></i>
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="h5-like"><i class="fa fa-eye me-2"></i>Our Vision</div>
                        <p class="mt-0">
                            <i class="fa fa-quote-left me-2"></i>
                            To become a trusted technology accessibility platform that bridges the digital
                            divide and enables equal opportunities for all individuals regardless of socioeconomic
                            background.
                            <i class="fa fa-quote-right me-2"></i>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="section-padding pt-4">
            <h2 class="section-title"><i class="fa fa-cogs me-2"></i>How It Works</h2>
            <div class="row g-4 process-wrapper">
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="h5-like"><i class="fa fa-search me-2"></i>Browse Devices</div>
                        <p class="mt-0">Explore available laptops, tablets and technology devices.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="h5-like"><i class="fa fa-edit me-2"></i>Submit Request</div>
                        <p class="mt-0">Select devices and submit a rental request seamlessly.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="h5-like"><i class="fa fa-check-circle me-2"></i>Verification</div>
                        <p class="mt-0">Requests are securely reviewed by our administrators.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="step-number">4</div>
                        <div class="h5-like"><i class="fa fa-handshake me-2"></i>Collect Device</div>
                        <p class="mt-0">Approved users can collect and start using the device.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Values Section -->
        <section class="section-padding pt-4">
            <h2 class="section-title"><i class="fa fa-heart me-2"></i>Our Values</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card-custom">
                        <span class="value-icon"><i class="fa fa-globe me-2"></i></span>
                        <div class="h5-like">Accessibility</div>
                        <p class="mt-0">Technology should be universally available to everyone.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <span class="value-icon"><i class="fa fa-search me-2"></i></span>
                        <div class="h5-like">Transparency</div>
                        <p class="mt-0">Clear rental processes and fair, honest policies.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <span class="value-icon"><i class="fa fa-lightbulb me-2"></i></span>
                        <div class="h5-like">Innovation</div>
                        <p class="mt-0">Using technology creatively to solve social challenges.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-custom">
                        <span class="value-icon"><i class="fa fa-handshake me-2"></i></span>
                        <div class="h5-like">Inclusion</div>
                        <p class="mt-0">Empowering everyone to achieve their full potential without technological
                            barriers.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Future Development Section -->
        <section class="section-padding pt-4 pb-5">
            <h2 class="section-title"><i class="fa fa-calendar-check me-2"></i>Future Development</h2>
            <ul class="future-list">
                <li class="future-item"><i class="fa fa-mobile-alt me-2"></i>Mobile App</li>
                <li class="future-item"><i class="fa fa-credit-card me-2"></i>Online Payment System</li>
                <li class="future-item"><i class="fa fa-qrcode me-2"></i>QR Code Device Collection</li>
                <li class="future-item"><i class="fa fa-language me-2"></i>Multilingual Support</li>
                <li class="future-item"><i class="fa fa-robot me-2"></i>AI Device Recommendation</li>
                <li class="future-item"><i class="fa fa-comments me-2"></i>Real-Time Chat Support</li>
            </ul>
        </section>
    </div>
    <?php include 'userfooter.php'; ?>
</body>

</html>