<?php
// Start the session
// session_start();

// Include the database connection
use Models\Database;
use Models\Notification;

$conn = Database::getConnection();

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit;
}

// Get the email from the session
$email = $_SESSION['email'];

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM students WHERE email = :email");
if (!$stmt) {
    die('Error in preparing statement: ' . $conn->errorInfo()[2]);
}

$stmt->execute(['email' => $email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user details are found
if ($userData) {
    // User details found, proceed with the rest of the code
    $studentId = $userData['id'];
    $notifications = Notification::getByStudentId($conn, $studentId);

    // Get profile image URL, use default if not set
    $profileImageUrl = !empty($userData['profile_image_url']) && filter_var($userData['profile_image_url'], FILTER_VALIDATE_URL) ? 
        htmlspecialchars($userData['profile_image_url']) : 
        null;
} else {
    echo "Invalid Credentials";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Student</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../views/public/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../views/ublic/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../views/public/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../../views/public//vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="../../views/public/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="../../views/public/css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="../../views/public\assets\images\android-chrome-512x512.png" />
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo mr-5" href="#"><img src="../../views/public\assets\images\logo1.png" class="mr-2" alt="logo" height="50">Knowbots</a>
            <a class="navbar-brand brand-logo-mini" href="#"><img src="../../views/public\assets\images\logo1.png" alt="logo"/></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                <span class="icon-menu"></span>
            </button>

            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item dropdown">
                    <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
                        <i class="icon-bell mx-0"></i>
                        <span class="count"><?php echo count($notifications); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                        <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="dropdown-item preview-item notification-item" href="#" data-notification-id="<?php echo htmlspecialchars($notification['id']); ?>">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-info">
                                            <i class="ti-info-alt mx-0"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <h6 class="preview-subject font-weight-normal"><?php echo htmlspecialchars($notification['message']); ?></h6>
                                        <p class="font-weight-light small-text mb-0 text-muted">
                                            <?php echo htmlspecialchars($notification['created_at']); ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a class="dropdown-item preview-item">
                                <div class="preview-item-content">
                                    <h6 class="preview-subject font-weight-normal">No notifications</h6>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        <?php if ($profileImageUrl): ?>
                            <img src="<?php echo $profileImageUrl; ?>" alt="profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"/>
                        <?php else: ?>
                            <img src="../../views/public/images/user.jpg" alt="profile"/>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="profile">
                            <i class="ti-user text-primary"></i>
                            Profile
                        </a>
                        <a class="dropdown-item" href="/logout">
                            <i class="ti-power-off text-primary"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                <span class="icon-menu"></span>
            </button>
        </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_settings-panel.html -->
        <div id="chatbot-trigger" style="position: fixed; bottom: 100px; right: 30px; z-index: 1032; background-color: #4B49AC; width: 45px; height: 45px; border-radius: 100%; cursor: pointer; transition: rotate .3s ease; color: white; display: flex; align-items: center; justify-content: center;" onclick="window.open('/student/askguru', '_blank')">
            <i class="ti-comments" style="font-size: 20px;"></i>
            <div class="pulse" style="position: absolute; width: 100%; height: 100%; border-radius: 100%; border: 3px solid #4B49AC; animation: pulse 2s infinite;"></div>
        </div>
        <style>
            @keyframes pulse {
                0% {
                    transform: scale(1);
                    opacity: 1;
                }
                100% {
                    transform: scale(1.5);
                    opacity: 0;
                }
            }
            #chatbot-trigger:hover {
                transform: scale(1.1);
                box-shadow: 0 0 15px rgba(75, 73, 172, 0.5);
                transition: transform 0.3s ease;
            }

            /* Updated settings panel styles */
            .theme-setting-wrapper {
                position: fixed !important;
                bottom: 40px !important;
                right: 30px !important;
                z-index: 9999 !important;
                display: block !important;
                width: 40px !important;
                height: 40px !important;
            }
            
            #settings-trigger {
                position: absolute !important;
                width: 40px !important;
                height: 40px !important;
                background: #4B49AC !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 100% !important;
                color: #fff !important;
                cursor: pointer !important;
                box-shadow: 0 0 10px rgba(0,0,0,0.2) !important;
                transition: all 0.3s ease !important;
                bottom: 0 !important;
                right: 0 !important;
            }

            #settings-trigger:hover {
                transform: scale(1.1) !important;
                box-shadow: 0 0 15px rgba(75, 73, 172, 0.5) !important;
            }

            .settings-panel {
                position: fixed !important;
                right: 80px !important;
                bottom: 40px !important;
                z-index: 9998 !important;
                background: white !important;
                padding: 20px !important;
                border-radius: 5px !important;
                box-shadow: 0 0 15px rgba(0,0,0,0.1) !important;
                display: none;
            }

            /* Mobile-specific styles */
            @media (max-width: 991px) {
                .theme-setting-wrapper {
                    position: fixed !important;
                    bottom: 20px !important;
                    right: 20px !important;
                    display: block !important;
                    z-index: 9999 !important;
                }
                
                #settings-trigger {
                    position: absolute !important;
                    display: flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }

                .settings-panel {
                    position: fixed !important;
                    right: 20px !important;
                    bottom: 70px !important;
                    max-width: 300px !important;
                    width: calc(100vw - 40px) !important;
                }

                /* Ensure the settings panel doesn't conflict with the chatbot trigger */
                #chatbot-trigger {
                    bottom: 120px !important;
                }
            }

            /* Add these styles for notification count positioning */
            .count-indicator {
                position: relative;
                padding: 0.75rem;
                margin-right: 1rem;
            }

            .count-indicator .count {
                position: absolute;
                right: 0px;
                top: 4px;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: #ff0000;
                color: #ffffff;
                font-size: 11px;
                line-height: 18px;
                text-align: center;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .count-indicator i {
                font-size: 20px;
                margin-right: 0;
                vertical-align: middle;
            }

            /* Add these styles for read notifications */
            .notification-item.read {
                opacity: 0.7;
                background-color: #f8f9fa;
            }

            .count {
                display: none; /* Initially hidden, shown by JS if there are unread notifications */
            }
        </style>

        <!-- Theme Settings Modal -->
        <div class="modal fade" id="themeModal" tabindex="-1" role="dialog" aria-labelledby="themeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="themeModalLabel">Theme Settings</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="theme-options">
                            <div class="theme-option selected" id="theme-light">
                                <div class="option-icon">
                                    <i class="fas fa-sun"></i>
                                </div>
                                <span>Light Theme</span>
                            </div>
                            <div class="theme-option" id="theme-dark">
                                <div class="option-icon">
                                    <i class="fas fa-moon"></i>
                                </div>
                                <span>Dark Theme</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replace the existing theme-setting-wrapper div with this -->
        <div class="theme-setting-wrapper">
            <div id="settings-trigger">
                <i class="fas fa-cog"></i>
            </div>
        </div>

        <!-- Add these styles -->
        <style>
        /* Update the theme variables */
        :root {
            --body-bg: #f4f7fa;
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --sidebar-bg: #ffffff;
            --menu-title: #333333;
            --menu-icon: #4B49AC;
            --link-color: #4B49AC;
            --hover-bg: rgba(75, 73, 172, 0.1);
            --modal-bg: #ffffff;
            --header-bg: #ffffff;
            --table-bg: #ffffff;
            --table-header-bg: #f8f9fa;
            --table-text: #333333;
            --table-border: #dee2e6;
            --heading-color: #333333;
            --input-bg: #ffffff;
            --input-text: #495057;
            --dropdown-bg: #ffffff;
            --dropdown-text: #333333;
            --logo-filter: none;
        }

        body.dark-theme {
            --body-bg: #1a1a1a;
            --text-color: #e1e1e1;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --sidebar-bg: #2d2d2d;
            --menu-title: #e1e1e1;
            --menu-icon: #6ea8fe;
            --link-color: #6ea8fe;
            --hover-bg: rgba(110, 168, 254, 0.1);
            --modal-bg: #2d2d2d;
            --header-bg: #2d2d2d;
            --table-bg: #2d2d2d;
            --table-header-bg: #363636;
            --table-text: #e1e1e1;
            --table-border: #404040;
            --heading-color: #e1e1e1;
            --input-bg: #363636;
            --input-text: #e1e1e1;
            --dropdown-bg: #2d2d2d;
            --dropdown-text: #e1e1e1;
            --logo-filter: brightness(0) invert(1);
        }

        /* Global styles */
        body {
            background-color: var(--body-bg) !important;
            color: var(--text-color) !important;
        }

        /* Header/Navbar styles */
        .navbar {
            background-color: var(--header-bg) !important;
            border-color: var(--border-color) !important;
        }

        .navbar-brand, .navbar-brand span {
            color: var(--text-color) !important;
        }

        /* Card styles */
        .card {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }

        .card-title, .card-header {
            color: var(--heading-color) !important;
        }

        /* Table styles */
        .table {
            background-color: var(--table-bg) !important;
            color: var(--table-text) !important;
        }

        .table thead th {
            background-color: var(--table-header-bg) !important;
            color: var(--table-text) !important;
            border-color: var(--table-border) !important;
        }

        .table td, .table th {
            border-color: var(--table-border) !important;
        }

        /* Heading styles */
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            color: var(--heading-color) !important;
        }

        /* Input styles */
        .form-control {
            background-color: var(--input-bg) !important;
            color: var(--input-text) !important;
            border-color: var(--border-color) !important;
        }

        /* Dropdown styles */
        .dropdown-menu {
            background-color: var(--dropdown-bg) !important;
            border-color: var(--border-color) !important;
        }

        .dropdown-item {
            color: var(--dropdown-text) !important;
        }

        .dropdown-item:hover {
            background-color: var(--hover-bg) !important;
        }

        /* Sidebar menu items */
        .nav-item .nav-link {
            color: var(--text-color) !important;
        }

        .nav-item .nav-link i {
            color: var(--menu-icon) !important;
        }

        .nav-item .nav-link .menu-title {
            color: var(--menu-title) !important;
        }

        /* Content wrapper */
        .content-wrapper {
            background-color: var(--body-bg) !important;
        }

        /* Footer */
        .footer {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
            border-color: var(--border-color) !important;
        }

        /* Notification styles */
        .preview-list .preview-item {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }

        .preview-list .preview-item .preview-item-content p {
            color: var(--text-color) !important;
        }
        </style>

        <!-- Add this script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const settingsTrigger = document.getElementById('settings-trigger');
            const themeLight = document.getElementById('theme-light');
            const themeDark = document.getElementById('theme-dark');

            // Show modal on settings click
            settingsTrigger.addEventListener('click', function() {
                $('#themeModal').modal('show');
            });

            function updateTheme(isDark) {
                if (isDark) {
                    document.body.classList.add('dark-theme');
                    themeDark.classList.add('selected');
                    themeLight.classList.remove('selected');
                } else {
                    document.body.classList.remove('dark-theme');
                    themeLight.classList.add('selected');
                    themeDark.classList.remove('selected');
                }
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }

            // Initialize theme
            const savedTheme = localStorage.getItem('theme');
            updateTheme(savedTheme === 'dark');

            // Theme option click handlers
            themeLight.addEventListener('click', () => {
                updateTheme(false);
                $('#themeModal').modal('hide');
            });

            themeDark.addEventListener('click', () => {
                updateTheme(true);
                $('#themeModal').modal('hide');
            });
        });
        </script>

        <!-- Add before your existing scripts -->
        <script>
        // IndexedDB initialization and functions
        const dbName = 'NotificationsDB';
        const storeName = 'readNotifications';
        let db;

        const initDB = () => {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(dbName, 1);
                
                request.onerror = () => reject(request.error);
                
                request.onsuccess = (event) => {
                    db = event.target.result;
                    resolve(db);
                };
                
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains(storeName)) {
                        db.createObjectStore(storeName, { keyPath: 'id' });
                    }
                };
            });
        };

        const markNotificationAsRead = async (notificationId, userId) => {
            const store = db.transaction(storeName, 'readwrite').objectStore(storeName);
            await store.put({
                id: `${userId}_${notificationId}`,
                userId: userId,
                notificationId: notificationId,
                readAt: new Date().toISOString()
            });
        };

        const isNotificationRead = async (notificationId, userId) => {
            return new Promise((resolve) => {
                const store = db.transaction(storeName, 'readonly').objectStore(storeName);
                const request = store.get(`${userId}_${notificationId}`);
                request.onsuccess = () => resolve(!!request.result);
                request.onerror = () => resolve(false);
            });
        };

        const updateNotificationCount = async () => {
            const unreadCount = await getUnreadCount();
            const countElement = document.querySelector('.count');
            if (unreadCount > 0) {
                countElement.style.display = 'flex';
                countElement.textContent = unreadCount;
            } else {
                countElement.style.display = 'none';
            }
        };

        const getUnreadCount = async () => {
            const notifications = <?php echo json_encode($notifications); ?>;
            const userId = <?php echo json_encode($studentId); ?>;
            let unreadCount = 0;
            
            for (const notification of notifications) {
                const isRead = await isNotificationRead(notification.id, userId);
                if (!isRead) unreadCount++;
            }
            
            return unreadCount;
        };

        const updateNotificationPanel = async () => {
            const notifications = <?php echo json_encode($notifications); ?>;
            const userId = <?php echo json_encode($studentId); ?>;
            const notificationsList = [];
            
            // Get unread notifications
            for (const notification of notifications) {
                const isRead = await isNotificationRead(notification.id, userId);
                if (!isRead) {
                    notificationsList.push(notification);
                }
            }
            
            // Update the notification panel content
            const dropdownMenu = $('.dropdown-menu.preview-list');
            const header = '<p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>';
            
            if (notificationsList.length > 0) {
                const notificationsHtml = notificationsList.map(notification => `
                    <a class="dropdown-item preview-item notification-item" href="#" data-notification-id="${notification.id}">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-info">
                                <i class="ti-info-alt mx-0"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-normal">${notification.message}</h6>
                            <p class="font-weight-light small-text mb-0 text-muted">
                                ${notification.created_at}
                            </p>
                        </div>
                    </a>
                `).join('');
                
                dropdownMenu.html(header + notificationsHtml);
            } else {
                const noNotificationsHtml = `
                    <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                    <a class="dropdown-item preview-item">
                        <div class="preview-item-content">
                            <img src="https://i.ibb.co/0SpmPCg/empty-box.png" alt="Empty Box" style="height: 50px; padding-left: 5vh; margin-bottom: 5vh;" class="empty-box-image">
                            <h6 class="preview-subject font-weight-normal">No new notifications</h6>
                        </div>
                    </a>
                `;
                dropdownMenu.html(noNotificationsHtml);
            }

            // Reattach click handlers for new notification items
            $('.notification-item').on('click', handleNotificationClick);
        };

        const handleNotificationClick = async function(e) {
            e.preventDefault();
            const notificationId = $(this).data('notification-id');
            const userId = <?php echo json_encode($studentId); ?>;
            
            await markNotificationAsRead(notificationId, userId);
            await updateNotificationCount();
            await updateNotificationPanel(); // Update the panel after marking as read
        };

        // Update the existing DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', async function() {
            await initDB();
            await updateNotificationCount();
            await updateNotificationPanel(); // Initial panel update

            if (typeof jQuery !== 'undefined') {
                // Your existing jQuery code...

                // Replace the old notification click handler with the new one
                $('.notification-item').on('click', handleNotificationClick);
            }
        });
        </script>

        <!-- Add this script after your existing scripts -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fix sidebar menu expansion
            const menuItems = document.querySelectorAll('.nav-item');
            
            menuItems.forEach(item => {
                const link = item.querySelector('.nav-link');
                const submenu = item.querySelector('.sub-menu');
                
                if (link && submenu) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Toggle the collapsed class
                        link.classList.toggle('collapsed');
                        
                        // Toggle aria-expanded
                        const isExpanded = link.getAttribute('aria-expanded') === 'true';
                        link.setAttribute('aria-expanded', !isExpanded);
                        
                        // Toggle submenu visibility
                        if (submenu.style.display === 'block') {
                            submenu.style.display = 'none';
                            item.classList.remove('active');
                        } else {
                            submenu.style.display = 'block';
                            item.classList.add('active');
                        }
                    });
                }
            });
        });
        </script>

        <!-- Add these styles -->
        <style>
        /* Sidebar menu expansion fixes */
        .sidebar .nav .nav-item {
            position: relative;
        }

        .sidebar .nav .nav-item .nav-link {
            transition: background-color 0.3s ease;
        }

        .sidebar .nav .nav-item .sub-menu {
            display: none;
            background: var(--sidebar-bg) !important;
            padding-left: 1.5rem;
        }

        .sidebar .nav .nav-item.active > .nav-link {
            background: var(--hover-bg) !important;
        }

        .sidebar .nav .nav-item.active > .sub-menu {
            display: block;
        }

        /* Rotation animation for collapse indicator */
        .sidebar .nav .nav-item .nav-link[aria-expanded="true"] .menu-arrow {
            transform: rotate(90deg);
        }

        .sidebar .nav .nav-item .menu-arrow {
            transition: transform 0.3s ease;
        }

        /* Submenu item styles */
        .sidebar .nav.sub-menu .nav-item .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: var(--text-color) !important;
            border-radius: 4px;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link:hover {
            background: var(--hover-bg) !important;
        }

        /* Active state styles */
        .sidebar .nav .nav-item.active > .nav-link .menu-title,
        .sidebar .nav .nav-item.active > .nav-link i {
            color: var(--menu-icon) !important;
        }
        </style>

        <!-- Update the script block that handles sidebar toggle -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar toggle button click
            const sidebarToggleBtn = document.querySelector('button[data-toggle="minimize"]');
            const body = document.querySelector('body');
            
            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event bubbling
                    body.classList.toggle('sidebar-icon-only');
                    
                    // Store the sidebar state
                    localStorage.setItem('sidebarState', 
                        body.classList.contains('sidebar-icon-only') ? 'collapsed' : 'expanded'
                    );
                });
            }

            // Restore sidebar state on page load
            const sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                body.classList.add('sidebar-icon-only');
            }

            // Handle mobile sidebar toggle
            const mobileToggleBtn = document.querySelector('.navbar-toggler-right');
            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('sidebar-open');
                });
            }
        });
        </script>

        <!-- Add these additional styles -->
        <style>
        /* Sidebar transition styles */
        body {
            transition: all 0.3s ease;
        }

        .sidebar {
            transition: width 0.3s ease, min-width 0.3s ease;
        }

        /* Sidebar collapsed state */
        .sidebar-icon-only .sidebar {
            width: 70px !important;
            min-width: 70px !important;
        }

        .sidebar-icon-only .sidebar .nav-item {
            padding: 0;
        }

        .sidebar-icon-only .sidebar .nav-item .nav-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .sidebar-icon-only .sidebar .menu-title,
        .sidebar-icon-only .sidebar .menu-arrow,
        .sidebar-icon-only .sidebar .nav-item .nav-link span {
            display: none !important;
        }

        .sidebar-icon-only .sidebar .nav-item .nav-link i {
            margin: 0;
        }

        /* Main panel adjustment when sidebar is collapsed */
        .sidebar-icon-only .main-panel {
            width: calc(100% - 70px) !important;
        }

        /* Mobile sidebar styles */
        @media (max-width: 991px) {
            .sidebar {
                position: fixed;
                z-index: 1031;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
            }

            .sidebar-open .sidebar {
                transform: translateX(0);
            }

            .sidebar-icon-only .main-panel {
                width: 100% !important;
            }
        }
        </style>

        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="/student/dashboard">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <!-- Course Management -->
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title">Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="/student/my_courses">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/student/manage_public_courses">Paid Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/student/virtual_classroom">Virtual Classroom</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Assignments & Labs -->
                <li class="nav-item">
                    <a class="nav-link" href="/student/manage_assignments">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Assignments</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/student/view_assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assessments</span>
                    </a>
                </li>

                <!-- Community -->
                <li class="nav-item">
                    <a class="nav-link" href="/student/discussion_forum">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Discussion Forum</span>
                    </a>
                </li>

                <!-- Miscellaneous -->
                <li class="nav-item">
                    <a class="nav-link" href="/student/xp_status">
                        <i class="ti-star menu-icon"></i>
                        <span class="menu-title">XP & Levels</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/student/tickets">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Support Tickets</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <!-- Profile Section -->
                <li class="nav-item">
                    <a class="nav-link" href="/student/profile">
                        <i class="ti-user menu-icon"></i>
                        <span class="menu-title">Profile</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/student/updatePassword">
                        <i class="ti-settings menu-icon"></i>
                        <span class="menu-title">Change Password</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/logout">
                        <i class="ti-power-off menu-icon"></i>
                        <span class="menu-title">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- partial -->

        <!-- Add this script block after all other scripts but before the closing </body> tag -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar toggle button click
            const sidebarToggleBtn = document.querySelector('button[data-toggle="minimize"]');
            const body = document.querySelector('body');
            
            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event bubbling
                    body.classList.toggle('sidebar-icon-only');
                    
                    // Store the sidebar state
                    localStorage.setItem('sidebarState', 
                        body.classList.contains('sidebar-icon-only') ? 'collapsed' : 'expanded'
                    );
                });
            }

            // Restore sidebar state on page load
            const sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'collapsed') {
                body.classList.add('sidebar-icon-only');
            }

            // Handle mobile sidebar toggle
            const mobileToggleBtn = document.querySelector('.navbar-toggler-right');
            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('sidebar-open');
                });
            }
        });
        </script>

        <style>
        /* Add these styles to ensure smooth transitions */
        .sidebar {
            transition: width 0.3s ease, background 0.3s ease;
        }

        .sidebar .nav-item {
            transition: all 0.3s ease;
        }

        .sidebar .menu-title {
            transition: opacity 0.3s ease;
        }

        /* Improve collapse animation */
        .collapse {
            transition: height 0.3s ease;
        }

        /* Ensure sidebar items are properly aligned in collapsed state */
        .sidebar-icon-only .sidebar .nav-item .nav-link {
            text-align: center;
            padding: 1rem;
        }

        .sidebar-icon-only .sidebar .nav-item .nav-link i {
            margin: 0;
        }

        /* Improve mobile sidebar */
        @media (max-width: 991px) {
            .sidebar-open .sidebar {
                transform: translateX(0);
                visibility: visible;
            }
            
            .sidebar {
                transform: translateX(-100%);
                visibility: hidden;
                transition: transform 0.3s ease, visibility 0.3s ease;
            }
        }
        </style>

        <!-- Add these specific styles for sidebar and header -->
        <style>
        .sidebar {
            background-color: var(--sidebar-bg) !important;
            border-right: 1px solid var(--border-color) !important;
        }

        .sidebar .sidebar-brand-wrapper {
            background-color: var(--sidebar-bg) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .navbar-brand-wrapper {
            background-color: var(--header-bg) !important;
            border-right: 1px solid var(--border-color) !important;
        }

        .navbar-menu-wrapper {
            background-color: var(--header-bg) !important;
            color: var(--text-color) !important;
        }

        /* Logo and brand text */
        .navbar .navbar-brand-wrapper .navbar-brand {
            color: var(--text-color) !important;
        }

        .navbar .navbar-brand-wrapper .navbar-brand img {
            filter: var(--logo-filter);
        }

        /* Sidebar menu items */
        .sidebar .nav .nav-item.active > .nav-link {
            background-color: var(--hover-bg) !important;
        }

        .sidebar .nav .nav-item .nav-link:hover {
            background-color: var(--hover-bg) !important;
        }

        .sidebar .nav.sub-menu {
            background-color: var(--sidebar-bg) !important;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link {
            color: var(--text-color) !important;
        }

        /* Settings trigger button */
        #settings-trigger {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            width: 45px !important;
            height: 45px !important;
            background: var(--menu-icon) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #fff !important;
            cursor: pointer !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
            transition: transform 0.3s ease !important;
            z-index: 1000 !important;
        }

        #settings-trigger:hover {
            transform: rotate(30deg) !important;
        }

        /* Modal styles */
        .modal-content {
            background-color: var(--modal-bg) !important;
            color: var(--text-color) !important;
        }

        .modal-header {
            border-color: var(--border-color) !important;
        }

        .modal-header .close {
            color: var(--text-color) !important;
        }

        /* Theme options */
        .theme-options {
            display: flex !important;
            justify-content: center !important;
            gap: 30px !important;
            padding: 20px !important;
        }

        .theme-option {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 20px !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            border: 2px solid transparent !important;
            transition: all 0.3s ease !important;
            min-width: 120px !important;
        }

        .theme-option:hover {
            background-color: var(--hover-bg) !important;
        }

        .theme-option.selected {
            border-color: var(--menu-icon) !important;
            background-color: var(--hover-bg) !important;
        }
        </style>

        <!-- Update the active state styles -->
        <style>
        /* Active menu item styles */
        .sidebar .nav .nav-item.active > .nav-link {
            background: var(--menu-icon) !important;
            color: #ffffff !important;
        }

        .sidebar .nav .nav-item.active > .nav-link .menu-title,
        .sidebar .nav .nav-item.active > .nav-link i,
        .sidebar .nav .nav-item.active > .nav-link .menu-arrow {
            color: #ffffff !important;
        }

        /* Hover state */
        .sidebar .nav .nav-item .nav-link:hover {
            background: var(--hover-bg) !important;
        }

        .sidebar .nav .nav-item .nav-link:hover .menu-title,
        .sidebar .nav .nav-item .nav-link:hover i {
            color: var(--menu-icon) !important;
        }

        /* Submenu active styles */
        .sidebar .nav.sub-menu .nav-item .nav-link.active {
            background: var(--menu-icon) !important;
            color: #ffffff !important;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link:hover {
            background: var(--hover-bg) !important;
            color: var(--menu-icon) !important;
        }
        </style>
