<?php
// session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
$message = $message ?? ''; // Ensure $message is defined

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowbots</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/views/public/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/views/public/assets/images/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/views/public/assets/images/android-chrome-192x192.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/views/public/assets/styles/core.css">
    <link rel="stylesheet" type="text/css" href="/views/public/assets/styles/icon-font.min.css">
    <link rel="stylesheet" type="text/css" href="/views/public/assets/styles/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Remove theme-related variables and keep only light theme colors */
        :root {
            --primary-color: #4B49AC;
            --primary-hover: #3f3e91;
            --body-bg: #f4f7fa;
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --input-bg: #ffffff;
            --input-text: #495057;
            --muted-text: #6c757d;
            --box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
            --input-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Enhanced Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #ffffff;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }

        .footer p {
            margin: 0;
            color: var(--primary-color);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--primary-hover);
        }

        /* Add margin to main content to prevent footer overlap */
        .login-wrap {
            margin-bottom: 80px; /* Adjust based on footer height */
        }

        /* Modern UI Variables */
        :root {
            --primary-color: #4B49AC;
            --primary-hover: #3f3e91;
            --body-bg: #f4f7fa;
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --input-bg: #ffffff;
            --input-text: #495057;
            --link-color: #4B49AC;
            --muted-text: #6c757d;
            --box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
            --input-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Dark theme enhancements */
        body.dark-theme {
            --body-bg: #1a1a1a;
            --text-color: #e1e1e1;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --input-bg: #333333;
            --input-text: #e1e1e1;
            --link-color: #6ea8fe;
            --muted-text: #9e9e9e;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            --input-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Enhanced Login Box */
        .login-box {
            background-color: var(--card-bg);
            border-radius: 16px !important;
            box-shadow: var(--box-shadow);
            padding: 2.5rem !important;
            border: none !important;
            transition: all 0.3s ease;
        }

        /* Modern Form Controls */
        .form-control {
            height: 48px;
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            border: 2px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--input-text);
            box-shadow: var(--input-shadow);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(75, 73, 172, 0.15);
        }

        /* Enhanced Button */
        .btn-primary {
            height: 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: none;
            letter-spacing: 0.5px;
            background: var(--primary-color);
            border: none;
            box-shadow: 0 4px 6px rgba(75, 73, 172, 0.2);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 8px rgba(75, 73, 172, 0.25);
        }

        /* Modern Header */
        .login-header {
            background-color: var(--card-bg);
            padding: 0.5rem 0;
            box-shadow: var(--box-shadow);
            height: 60px; /* Reduced height */
        }

        .navbar {
            padding: 0 !important;
            min-height: auto !important;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0;
        }

        .navbar-brand img {
            height: 35px !important; /* Reduced logo size */
            width: 35px !important;
        }

        .navbar-brand h2 {
            margin: 0;
            font-size: 1.4rem !important; /* Reduced font size */
            color: var(--text-color);
            font-family: cursive;
        }

        /* Container adjustments */
        .container-fluid {
            padding: 0 1.5rem;
        }

        /* Adjust login-wrap top margin to account for smaller header */
        .login-wrap {
            margin-top: 20px;
        }

        /* Checkbox Enhancement */
        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .custom-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 6px;
            border: 2px solid var(--border-color);
            cursor: pointer;
        }

        /* Password Toggle Enhancement */
        .input-group.custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--muted-text);
            z-index: 10;
            transition: color 0.3s ease;
        }

        /* Theme Toggle Enhancement */
        #theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px;
            border-radius: 50%;
            background: var(--card-bg);
            box-shadow: var(--box-shadow);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #theme-toggle:hover {
            transform: rotate(30deg);
            background: var(--primary-color);
        }

        #theme-toggle:hover i {
            color: #ffffff;
        }

        /* Alert Enhancement */
        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            background-color: var(--card-bg);
            box-shadow: var(--box-shadow);
        }

        /* Footer Enhancement */
        .footer-text {
            margin-top: 2rem;
            padding: 1rem;
            text-align: center;
            color: var(--muted-text);
            font-size: 0.9rem;
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer-text a:hover {
            color: var(--primary-hover);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .login-header {
                height: 50px; /* Even smaller on mobile */
            }

            .navbar-brand img {
                height: 30px !important;
                width: 30px !important;
            }

            .navbar-brand h2 {
                font-size: 1.2rem !important;
            }
        }
    </style>
</head>
<body class="login-page">
<div class="login-header box-shadow">
    <div class="container-fluid">
        <nav class="navbar navbar-light">
            <a class="navbar-brand" href="#">
                <img src="/views/public/assets/images/logo1.png" alt="logo">
                <h2>Knowbots</h2>
            </a>
        </nav>
    </div>
</div>
<div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
    <div class="container">
        <?php if (!empty($warning)): ?>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="alert alert-warning" role="alert">
                        <?php echo htmlspecialchars($warning); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row align-items-center">
            <div class="col-md-6 col-lg-7">
                <img src="/views/public/assets/images/login-page-img.webp" alt="">
            </div>
            <div class="col-md-6 col-lg-5">
                <div class="login-box bg-light box-shadow border-radius-12">
                    <div class="login-title">
                        <h2 class="text-center text-primary" style="font-family: cursive;">Login</h2>
                    </div>
                    <form id="loginForm" action="/login" method="POST">
                        <div class="input-group custom">
                            <input type="text" class="form-control form-control-lg" placeholder="Username/Email id" name="username" id="username" required>
                        </div>
                        <div class="input-group custom">
                            <input type="password" class="form-control form-control-lg" placeholder="**********" name="password" id="password" required>
                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="input-group custom custom-checkbox">
                            <input type="checkbox" name="agree" id="agree" required>
                            <label for="agree" class="ml-2">I agree to the <a href="/#privacy-policy" style="color: blue;">Privacy Policy</a></label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="input-group mb-0">
                                    <input class="btn btn-primary btn-lg btn-block" name="login" id="login" type="submit" value="Sign In">
                                </div>
                                <div class="col-6 mt-3 float-right">
                                    <div class="forgot-password"><a href="/forgot_password" style="color: blue;">Forgot Password?</a></div>
                                </div>
                            </div>
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($message); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div><br>
    </div>
</div>
<footer class="footer">
    <p>
        Developed and maintained by<br>
        <a href="about.html">Phemesoft</a>
    </p>
</footer>
<script src="/views/public/assets/scripts/core.js"></script>
<script src="/views/public/assets/scripts/script.min.js"></script>
<script src="/views/public/assets/scripts/process.js"></script>
<script src="/views/public/assets/scripts/layout-settings.js"></script>
<script>
function togglePasswordVisibility() {
    var passwordInput = document.getElementById('password');
    var toggleIcon = document.querySelector('.toggle-password i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>