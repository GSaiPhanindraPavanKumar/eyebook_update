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
                        <img src="../../views/public\images\user.jpg" alt="profile"/>
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
        <div id="chatbot-trigger" style="position: fixed; bottom: 100px; right: 30px; z-index: 1032; background-color: #4B49AC; width: 45px; height: 45px; border-radius: 100%; cursor: pointer; transition: rotate .3s ease; color: white; display: flex; align-items: center; justify-content: center;" onclick="openChatbot()">
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

        <div class="theme-setting-wrapper">
            <div id="settings-trigger"><i class="ti-settings"></i></div>
            <div id="theme-settings" class="settings-panel">
                <i class="settings-close ti-close"></i>
                <p class="settings-heading">SIDEBAR SKINS</p>
                <div class="sidebar-bg-options selected" id="sidebar-light-theme">
                    <div class="img-ss rounded-circle bg-light border mr-3"></div>Light
                </div>
                <div class="sidebar-bg-options" id="sidebar-dark-theme">
                    <div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark
                </div>
                <p class="settings-heading mt-2">HEADER SKINS</p>
                <div class="color-tiles mx-0 px-4">
                    <div class="tiles success"></div>
                    <div class="tiles warning"></div>
                    <div class="tiles danger"></div>
                    <div class="tiles info"></div>
                    <div class="tiles dark"></div>
                    <div class="tiles default"></div>
                </div>
            </div>
        </div>
        
        <!-- Add all necessary scripts in the correct order -->
        <script src="../../views/public/vendors/js/vendor.bundle.base.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="../../views/public/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../../views/public/js/off-canvas.js"></script>
        <script src="../../views/public/js/hoverable-collapse.js"></script>
        <script src="../../views/public/js/template.js"></script>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined') {
                // Settings panel toggle
                $('#settings-trigger').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#theme-settings').stop().fadeToggle(300); // Added stop() and animation duration
                });

                // Close settings panel
                $('.settings-close').on('click', function(e) {
                    e.preventDefault();
                    $('#theme-settings').fadeOut(300);
                });

                // Close settings when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.theme-setting-wrapper').length) {
                        $('#theme-settings').fadeOut(300);
                    }
                });

                // Prevent settings panel from closing when clicking inside it
                $('#theme-settings').on('click', function(e) {
                    e.stopPropagation();
                });

                // Profile dropdown
                $('.nav-profile.dropdown').on('click', function(e) {
                    e.stopPropagation();
                    $(this).find('.dropdown-menu').toggleClass('show');
                });

                // Notification dropdown
                $('#notificationDropdown').on('click', function(e) {
                    e.stopPropagation();
                    $(this).next('.dropdown-menu').toggleClass('show');
                });

                // Close dropdowns when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.dropdown').length) {
                        $('.dropdown-menu').removeClass('show');
                    }
                });
            }
        });

        function openChatbot() {
            window.location.href = '/student/askguru';
        }
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

        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="/student/dashboard">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#faculty" aria-expanded="false" aria-controls="faculty">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Faculty</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="faculty">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="#">Upload Faculty</a></li>
                            <li class="nav-item"> <a class="nav-link" href="add_faculty">Add Faculty</a></li>
                           <li class="nav-item"> <a class="nav-link" href="#">Manage Faculty</a></li>
                        </ul>
                    </div>
                </li> -->


                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title" >Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="/student/my_courses">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/student/virtual_classroom">Virtual Classroom</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" href="/student/manage_assignments">Assignments</a></li> -->
                            <!-- <li class="nav-item"><a class="nav-link" href="#">Meetings</a></li>  -->
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#discussion" aria-expanded="false" aria-controls="discussion">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Discussion</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="discussion">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="discussion_forum">Discussion Forum</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#lab" aria-expanded="false" aria-controls="lab">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title">Lab</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="lab">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/student/manage_contests">Contests</a></li>
                        </ul>
                    </div>
                </li>
                
                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#a" aria-expanded="false" aria-controls="student">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Student</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="student">
                        <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="upload_students">Upload Students</a></li>
                            <li class="nav-item"> <a class="nav-link" href="#">Manage Student</a></li>
                        </ul>
                    </div>
                </li> -->
                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#streams" aria-expanded="false" aria-controls="streams">
                        <i class="icon-grid-2 menu-icon"></i>
                        <span class="menu-title">Streams</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="streams">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="streams_team">Manage Streams</a></li>
                        </ul>
                    </div>
                </li> -->


                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
                        <i class="icon-contract menu-icon"></i>
                        <span class="menu-title">Icons</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="icons">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/icons/mdi.html">Mdi icons</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="icon-head menu-icon"></i>
                        <span class="menu-title">User Pages</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="auth">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/login.html"> Login </a></li>
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/register.html"> Register </a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#error" aria-expanded="false" aria-controls="error">
                        <i class="icon-ban menu-icon"></i>
                        <span class="menu-title">Error pages</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="error">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> 404 </a></li>
                            <li class="nav-item"> <a class="nav-link" href="pages/samples/error-500.html"> 500 </a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/documentation/documentation.html">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Documentation</span>
                    </a>
                </li> -->


                <li class="nav-item"><hr></li>
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
                    <a class="nav-link" href="/student/logout">
                        <i class="ti-power-off menu-icon"></i>
                        <span class="menu-title">Logout</span>
                </a>
            </li>
            
            </ul>

        </nav>
        <!-- partial -->