<?php
// Start the session
// session_start();
ob_start();
// Include the database connection
// include '../../config/connection.php';
use Models\Database;

$conn = Database::getConnection();

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login");
    exit;
}

// Get the email from the session
$email = $_SESSION['email'];

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM faculty WHERE email = :email");
if (!$stmt) {
    die("Error in preparing statement: {$conn->errorInfo()[2]}");
}

$stmt->execute(['email' => $email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user details are found
if ($userData) {
    // User details found, proceed with the rest of the code
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
    <title>Knowbots</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="/views/public/vendors/feather/feather.css">
    <link rel="stylesheet" href="/views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/views/public/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/views/public/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="/views/public//vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="../views\public/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/views/public/css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="/views/public\assets\images\android-chrome-512x512.png" />
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo mr-5" href="#"><img src="/views/public\assets\images\logo1.png" class="mr-2" alt="logo" height="50">Knowbots</a>
            <a class="navbar-brand brand-logo-mini" href="#"><img src="/views/public\assets\images\logo1.png" alt="logo"/></a>
        </div>
        <di class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                <span class="icon-menu"></span>
            </button>

            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="theme-toggle">
                        <i class="fas fa-sun" id="theme-icon-light" style="display: none;"></i>
                        <i class="fas fa-moon" id="theme-icon-dark"></i>
                    </a>
                </li>

                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        <?php if ($profileImageUrl): ?>
                            <img src="<?php echo $profileImageUrl; ?>" alt="profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"/>
                        <?php else: ?>
                            <img src="/views/public/images/user.jpg" alt="profile"/>
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
        <!-- <div class="theme-setting-wrapper">
            <div id="settings-trigger" style="z-index: 9999; width: 40px; height: 40px; background: #4B49AC; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
                <i class="ti-settings" style="color: #ffffff;"></i>
            </div>
            <div id="theme-settings" class="settings-panel">
                <i class="settings-close ti-close"></i>
                <p class="settings-heading">THEME MODE</p>
                <div class="theme-mode-options">
                    <div class="mode-option selected" id="light-mode">
                        <div class="img-ss rounded-circle bg-light border mr-3"></div>Light Mode
                    </div>
                    <div class="mode-option" id="dark-mode">
                        <div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark Mode
                    </div>
                </div>
            </div>
        </div> -->

        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="/faculty/dashboard">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <li class="nav-item">

                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title">Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="my_courses.php">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="faculty_dashboard.php">Virtual Classroom</a></li>
                            <li class="nav-item"><a class="nav-link" href="faculty.php">Assessments</a></li>
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
                            <li class="nav-item"> <a class="nav-link" href="/faculty/my_courses">My Courses</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/faculty/virtual_classroom">Virtual Classroom</a></li>
                            <!-- <li class="nav-item"> <a class="nav-link" href="/faculty/faculty">Assessments</a></li> -->
                        </ul>
                    </div>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#assessments" aria-expanded="false" aria-controls="assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assignments</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="assessments">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/faculty/create_assignment">Create Assignments</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/faculty/manage_assignments">Manage Assignments</a></li>
                        </ul>
                    </div>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" href="/faculty/manage_assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assessments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#discussion" aria-expanded="false" aria-controls="discussion">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Discussion</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="discussion">
                        <ul class="nav flex-column sub-menu">
                        <!-- <li class="nav-item"> <a class="nav-link" href="upload_students">Upload Students</a></li> -->
                            <li class="nav-item"> <a class="nav-link" href="/faculty/discussion_forum">Discussion Forum</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#student" aria-expanded="false" aria-controls="student">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Student</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="student">
                        <ul class="nav flex-column sub-menu">
                        <!-- <li class="nav-item"> <a class="nav-link" href="upload_students">Upload Students</a></li> -->
                            <li class="nav-item"> <a class="nav-link" href="/faculty/manage_students">Manage Student</a></li>
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
                            <li class="nav-item"> <a class="nav-link" href="/faculty/create_lab">Create Lab</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/faculty/manage_contests">Contests</a></li>
                        </ul>
                    </div>
                </li>
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
                    <a class="nav-link" href="/faculty/tickets">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Support Tickets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/faculty/profile">
                        <i class="ti-user menu-icon"></i>
                        <span class="menu-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/faculty/updatePassword">
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

<style>
/* Update the theme variables */
:root {
    /* Existing variables */
    --body-bg: #f4f7fa;
    --text-color: #333333;
    --card-bg: #ffffff;
    /* ... other existing variables ... */

    /* Add these new variables */
    --avatar-bg: #4B49AC;
    --avatar-text: #ffffff;      /* Light text on dark background for light theme */
    --avatar-hover-bg: #413ea9;
}

body.dark-theme {
    /* Existing variables */
    --body-bg: #1a1a1a;
    --text-color: #e1e1e1;
    --card-bg: #2d2d2d;
    /* ... other existing variables ... */

    /* Add these new variables */
    --avatar-bg: #6ea8fe;
    --avatar-text: #333333;      /* Dark text on light background for dark theme */
    --avatar-hover-bg: #5a95eb;
}

/* Add these CSS variables and styles */
:root {
    /* Light theme default variables */
    --body-bg: #f4f7fa;
    --text-color: #333333;
    --card-bg: #ffffff;
    --border-color: #dee2e6;
    --input-bg: #ffffff;
    --input-text: #495057;
    --table-bg: #ffffff;
    --table-border: #dee2e6;
    --hover-bg: #f8f9fa;
    --sidebar-bg: #ffffff;
    --sidebar-text: #333333;
    --navbar-bg: #ffffff;
    --link-color: #4B49AC;
    --muted-text: #6c757d;
}

/* Apply variables to elements */
body {
    background-color: var(--body-bg) !important;
    color: var(--text-color) !important;
}

.card {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.table {
    background-color: var(--table-bg) !important;
    color: var(--text-color) !important;
}

.table td, .table th {
    border-color: var(--table-border) !important;
    color: var(--text-color) !important;
}

.form-control {
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
    border-color: var(--border-color) !important;
}

.navbar {
    background-color: var(--navbar-bg) !important;
}

.sidebar {
    background-color: var(--sidebar-bg) !important;
}

/* Dark theme overrides */
.dark-theme {
    --body-bg: #222222;
    --text-color: #e1e1e1;
    --card-bg: #2d2d2d;
    --border-color: #404040;
    --input-bg: #333333;
    --input-text: #e1e1e1;
    --table-bg: #2d2d2d;
    --table-border: #404040;
    --hover-bg: #3a3a3a;
    --sidebar-bg: #2d2d2d;
    --sidebar-text: #ffffff;
    --navbar-bg: #2d2d2d;
    --link-color: #6ea8fe;
    --muted-text: #9e9e9e;
}

/* Additional dark theme styles */
.dark-theme .dropdown-menu {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.dark-theme .dropdown-item {
    color: var(--text-color) !important;
}

.dark-theme .dropdown-item:hover {
    background-color: var(--hover-bg) !important;
}

.dark-theme .nav-link {
    color: var(--text-color) !important;
}

.dark-theme .footer {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

.dark-theme .text-muted {
    color: var(--muted-text) !important;
}

.dark-theme h1, 
.dark-theme h2, 
.dark-theme h3, 
.dark-theme h4, 
.dark-theme h5, 
.dark-theme h6,
.dark-theme .card-title {
    color: var(--text-color) !important;
}

.dark-theme .btn-light {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

.dark-theme .modal-content {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

#theme-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    margin-right: 15px;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

#theme-toggle:hover {
    background-color: rgba(75, 73, 172, 0.1);
}

#theme-icon-light,
#theme-icon-dark {
    font-size: 20px;
    transition: transform 0.3s ease;
    color: var(--text-color);
}

#theme-toggle:hover #theme-icon-light,
#theme-toggle:hover #theme-icon-dark {
    transform: rotate(30deg);
}

.dark-theme #theme-icon-dark {
    display: none !important;
}

.dark-theme #theme-icon-light {
    display: block !important;
}

.light-theme #theme-icon-light {
    display: none !important;
}

.light-theme #theme-icon-dark {
    display: block !important;
}

/* Add these additional theme styles */
.dark-theme .content-wrapper {
    background-color: var(--body-bg) !important;
}

.dark-theme .navbar .navbar-brand-wrapper {
    background-color: var(--card-bg) !important;
}

.dark-theme .navbar .navbar-menu-wrapper {
    background-color: var(--card-bg) !important;
}

.dark-theme .font-weight-bold {
    color: var(--text-color) !important;
}

.dark-theme .card .card-title {
    color: var(--text-color) !important;
}

.dark-theme .card .card-description {
    color: var(--muted-text) !important;
}

.dark-theme .table-hover tbody tr:hover {
    background-color: var(--hover-bg) !important;
}

.dark-theme .grid-margin {
    color: var(--text-color) !important;
}

.dark-theme .stretch-card {
    color: var(--text-color) !important;
}

.dark-theme .menu-title {
    color: var(--text-color) !important;
}

.dark-theme .nav-item hr {
    border-color: var(--border-color) !important;
}

.dark-theme .settings-panel .settings-heading {
    color: var(--text-color) !important;
}

.dark-theme .navbar-brand {
    color: var(--text-color) !important;
}

.dark-theme .text-primary {
    color: var(--link-color) !important;
}

.dark-theme .btn-primary {
    background-color: #4B49AC !important;
    border-color: #4B49AC !important;
}

.dark-theme .btn-primary:hover {
    background-color: #3f3e91 !important;
    border-color: #3f3e91 !important;
}

/* Fix for specific faculty components */
.dark-theme .main-panel {
    background-color: var(--body-bg) !important;
}

.dark-theme .page-body-wrapper {
    background-color: var(--body-bg) !important;
}

.dark-theme .container-fluid {
    background-color: var(--body-bg) !important;
}

.dark-theme .container-scroller {
    background-color: var(--body-bg) !important;
}

/* Fix for form elements */
.dark-theme select.form-control {
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
}

.dark-theme textarea.form-control {
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
}

/* Fix for badges and labels */
.dark-theme .badge {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

/* Fix for icons */
.dark-theme .menu-icon {
    color: var(--text-color) !important;
}

.dark-theme .menu-arrow {
    color: var(--text-color) !important;
}

/* Add these table-specific styles */
.dark-theme .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

.dark-theme .table-striped tbody tr:nth-of-type(even) {
    background-color: var(--card-bg) !important;
}

.dark-theme .table-borderless th {
    border: none !important;
    color: var(--text-color) !important;
    background-color: var(--card-bg) !important;
}

.dark-theme .table thead th {
    border-bottom: 2px solid var(--border-color) !important;
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

.dark-theme .table tbody td {
    border-bottom: 1px solid var(--border-color) !important;
    color: var(--text-color) !important;
}

.dark-theme .table-responsive {
    border-color: var(--border-color) !important;
}

/* Pagination styles for dark theme */
.dark-theme .page-link {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

.dark-theme .page-item.active .page-link {
    background-color: #4B49AC !important;
    border-color: #4B49AC !important;
    color: #ffffff !important;
}

.dark-theme .page-item.disabled .page-link {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--muted-text) !important;
}

/* Search and entries per page controls */
.dark-theme #searchInput,
.dark-theme #entriesPerPage {
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
    border-color: var(--border-color) !important;
}

/* Table hover effect */
.dark-theme .table-striped tbody tr:hover {
    background-color: rgba(75, 73, 172, 0.1) !important;
}

/* Text colors */
.dark-theme #showing-entries,
.dark-theme .card-title {
    color: var(--text-color) !important;
}

.dark-theme #noRecords {
    color: var(--muted-text) !important;
}
</style>

<script>
// Theme management constants
const THEME_DB_NAME = 'facultyThemeDB';
const THEME_STORE_NAME = 'themeSettings';
const THEME_KEY = 'currentTheme';

// Initialize IndexedDB
const initThemeDB = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(THEME_DB_NAME, 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(THEME_STORE_NAME)) {
                db.createObjectStore(THEME_STORE_NAME);
            }
        };
    });
};

// Save theme preference
const saveThemePreference = async (isDark) => {
    try {
        const db = await initThemeDB();
        const tx = db.transaction(THEME_STORE_NAME, 'readwrite');
        const store = tx.objectStore(THEME_STORE_NAME);
        await store.put(isDark, THEME_KEY);
        localStorage.setItem('themeChange', Date.now().toString());
    } catch (error) {
        console.error('Error saving theme:', error);
        localStorage.setItem(THEME_KEY, JSON.stringify(isDark));
    }
};

// Load theme preference
const loadThemePreference = async () => {
    try {
        const db = await initThemeDB();
        const tx = db.transaction(THEME_STORE_NAME, 'readonly');
        const store = tx.objectStore(THEME_STORE_NAME);
        const request = store.get(THEME_KEY);
        
        return new Promise((resolve) => {
            request.onsuccess = () => resolve(request.result ?? false);
            request.onerror = () => resolve(false);
        });
    } catch (error) {
        console.error('Error loading theme:', error);
        const fallbackTheme = localStorage.getItem(THEME_KEY);
        return fallbackTheme ? JSON.parse(fallbackTheme) : false;
    }
};

// Apply theme function
const applyTheme = (isDark) => {
    if (isDark) {
        document.body.classList.add('dark-theme');
        document.body.classList.remove('light-theme');
    } else {
        document.body.classList.remove('dark-theme');
        document.body.classList.add('light-theme');
    }

    // Update sidebar specifically
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link:not(.active)').forEach(link => {
            link.style.color = isDark ? '#e1e1e1' : '#333333';
        });
        sidebar.querySelectorAll('.nav-link:not(.active) i').forEach(icon => {
            icon.style.color = isDark ? '#e1e1e1' : '#333333';
        });
        sidebar.querySelectorAll('.nav-link.active, .nav-link.active i').forEach(element => {
            element.style.color = '#ffffff';
        });
    }
};

// Theme toggle handler
const handleThemeToggle = async () => {
    const isDark = !document.body.classList.contains('dark-theme');
    await saveThemePreference(isDark);
    applyTheme(isDark);
};

// Initialize theme
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const savedTheme = await loadThemePreference();
        applyTheme(savedTheme);
        
        // Set up click handler for theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle?.addEventListener('click', handleThemeToggle);
        
    } catch (error) {
        console.error('Error initializing theme:', error);
        applyTheme(false);
    }
});

// Listen for theme changes
window.addEventListener('storage', async (event) => {
    if (event.key === 'themeChange') {
        const newTheme = await loadThemePreference();
        applyTheme(newTheme);
    }
});
</script>
