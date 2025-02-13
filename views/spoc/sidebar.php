<?php
// Start the session
// session_start();

use Models\Database;

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Get the email from the session
$email = $_SESSION['email'];

// Get the database connection
$conn = Database::getConnection();

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM spocs WHERE email = :email");
if (!$stmt) {
    die('Error in preparing statement.');
}

$stmt->execute(['email' => $email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user details are found
if ($userData) {
    // User details found, proceed with the rest of the code
} else {
    echo "Invalid Credentials";
}

// Add after getting user data
$profileImageUrl = !empty($userData['profile_image_url']) && filter_var($userData['profile_image_url'], FILTER_VALIDATE_URL) ? 
    htmlspecialchars($userData['profile_image_url']) : 
    null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Knowbots</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../views/public/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../views/public/vendors/css/vendor.bundle.base.css">
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
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        <?php if ($profileImageUrl): ?>
                            <img src="<?php echo $profileImageUrl; ?>" alt="profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"/>
                        <?php else: ?>
                            <img src="../../views/public/images/user.jpg" alt="profile"/>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="/spoc/profile">
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
        <div class="theme-setting-wrapper">
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
        </div>

        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="/spoc/dashboard">
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
                            <li class="nav-item"> <a class="nav-link" href="#">Upload Faculty</a></li> -->
                            <!-- <li class="nav-item"> <a class="nav-link" href="/spoc/addFaculty">Add Faculty</a></li>
                           <li class="nav-item"> <a class="nav-link" href="/spoc/manage_faculty">Manage Faculty</a></li>
                        </ul>
                    </div>
                </li> -->

                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title">Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="/spoc/manage_courses">Manage Courses</a></li>
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
                        <!-- <li class="nav-item"> <a class="nav-link" href="upload_students">Upload Students</a></li> -->
                            <li class="nav-item"> <a class="nav-link" href="/spoc/discussion_forum">Discussion Forum</a></li>
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
                            <li class="nav-item"> <a class="nav-link" href="/spoc/manage_contests">Contests</a></li>
                        </ul>
                    </div>
                </li>


                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title" >Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="#">Manage Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Manage Courses</a></li> 
                            <li class="nav-item"><a class="nav-link" href="courses">Submission</a></li>
                        </ul>
                    </div>
                </li> -->
                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#student" aria-expanded="false" aria-controls="student">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title" >Student</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="student">
                        <ul class="nav flex-column sub-menu">
                         <li class="nav-item"> <a class="nav-link" href="upload_students">Upload Students</a></li> -->
                            <!-- <li class="nav-item"> <a class="nav-link" href="/spoc/manage_students">Manage Student</a></li>
                        </ul>
                    </div>
                </li>  -->
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
                    <a class="nav-link" href="/spoc/tickets">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Support Tickets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/spoc/profile">
                        <i class="ti-user menu-icon"></i>
                        <span class="menu-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/spoc/updatePassword">
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

<script>
// Theme management constants
const THEME_DB_NAME = 'spocThemeDB';
const THEME_STORE_NAME = 'themeSettings';
const THEME_KEY = 'currentTheme';

// Initialize IndexedDB
const initThemeDB = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(THEME_DB_NAME, 1);
        
        request.onerror = () => {
            console.error('Failed to open theme database:', request.error);
            reject(request.error);
        };
        
        request.onsuccess = () => {
            resolve(request.result);
        };
        
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
        
        return new Promise((resolve, reject) => {
            const request = store.put(isDark, THEME_KEY);
            
            request.onsuccess = () => {
                localStorage.setItem('themeChange', Date.now().toString());
                resolve();
            };
            
            request.onerror = () => {
                console.error('Failed to save theme:', request.error);
                reject(request.error);
            };
            
            tx.oncomplete = () => db.close();
        });
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
        
        return new Promise((resolve, reject) => {
            const request = store.get(THEME_KEY);
            
            request.onsuccess = () => {
                resolve(request.result ?? false);
            };
            
            request.onerror = () => {
                console.error('Failed to load theme:', request.error);
                reject(request.error);
            };
            
            tx.oncomplete = () => db.close();
        });
    } catch (error) {
        console.error('Error loading theme:', error);
        const fallbackTheme = localStorage.getItem(THEME_KEY);
        return fallbackTheme ? JSON.parse(fallbackTheme) : false;
    }
};

// Apply theme
const applyTheme = (isDark) => {
    const root = document.documentElement;
    
    if (isDark) {
        root.style.setProperty('--body-bg', '#1a1a1a');
        root.style.setProperty('--text-color', '#e1e1e1');
        root.style.setProperty('--card-bg', '#2d2d2d');
        root.style.setProperty('--border-color', '#404040');
        root.style.setProperty('--input-bg', '#333333');
        root.style.setProperty('--input-text', '#ffffff');
        root.style.setProperty('--table-bg', '#2d2d2d');
        root.style.setProperty('--table-border', '#404040');
        root.style.setProperty('--hover-bg', '#3a3a3a');
        root.style.setProperty('--sidebar-bg', '#2d2d2d');
        root.style.setProperty('--sidebar-text', '#ffffff');
        root.style.setProperty('--navbar-bg', '#2d2d2d');
        root.style.setProperty('--link-color', '#6ea8fe');
        root.style.setProperty('--muted-text', '#9e9e9e');
        root.style.setProperty('--calendar-bg', '#2d2d2d');
        root.style.setProperty('--calendar-header', 'rgba(75, 73, 172, 0.1)');
        root.style.setProperty('--calendar-today', 'rgba(75, 73, 172, 0.15)');
        root.style.setProperty('--calendar-event', '#4B49AC');
        root.style.setProperty('--calendar-text', '#e1e1e1');
        document.body.classList.add('dark-theme');
        
        // Update sidebar specifically
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.backgroundColor = '#2d2d2d';
            sidebar.querySelectorAll('.nav-link:not(.active)').forEach(link => {
                link.style.color = '#e1e1e1';
            });
            sidebar.querySelectorAll('.nav-link:not(.active) i').forEach(icon => {
                icon.style.color = '#e1e1e1';
            });
            sidebar.querySelectorAll('.nav-link.active, .nav-link.active i').forEach(element => {
                element.style.color = '#ffffff';
            });
        }

        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.remove('navbar-light');
            navbar.classList.add('navbar-dark');
        }
        
        // Update content wrapper
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '#1a1a1a';
        }
    } else {
        root.style.setProperty('--body-bg', '#f4f7fa');
        root.style.setProperty('--text-color', '#333333');
        root.style.setProperty('--card-bg', '#ffffff');
        root.style.setProperty('--border-color', '#dee2e6');
        root.style.setProperty('--input-bg', '#ffffff');
        root.style.setProperty('--input-text', '#495057');
        root.style.setProperty('--table-bg', '#ffffff');
        root.style.setProperty('--table-border', '#dee2e6');
        root.style.setProperty('--hover-bg', '#f8f9fa');
        root.style.setProperty('--sidebar-bg', '#ffffff');
        root.style.setProperty('--sidebar-text', '#333333');
        root.style.setProperty('--navbar-bg', '#ffffff');
        root.style.setProperty('--calendar-bg', '#ffffff');
        root.style.setProperty('--calendar-header', '#f8f9fa');
        root.style.setProperty('--calendar-today', '#e8f4ff');
        root.style.setProperty('--calendar-event', '#4B49AC');
        root.style.setProperty('--calendar-text', '#333333');
        document.body.classList.remove('dark-theme');
        
        // Reset sidebar
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.backgroundColor = '#ffffff';
            sidebar.querySelectorAll('.nav-link:not(.active)').forEach(link => {
                link.style.color = '#333333';
            });
            sidebar.querySelectorAll('.nav-link:not(.active) i').forEach(icon => {
                icon.style.color = '#333333';
            });
            sidebar.querySelectorAll('.nav-link.active, .nav-link.active i').forEach(element => {
                element.style.color = '#ffffff';
            });
        }

        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-light');
        }
        
        // Reset content wrapper
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '#f4f7fa';
        }
    }
};

// Update UI
const updateThemeToggleUI = (isDark) => {
    const lightMode = document.getElementById('light-mode');
    const darkMode = document.getElementById('dark-mode');
    
    if (lightMode && darkMode) {
        if (isDark) {
            lightMode.classList.remove('selected');
            darkMode.classList.add('selected');
        } else {
            darkMode.classList.remove('selected');
            lightMode.classList.add('selected');
        }
    }
};

// Theme toggle handler
const handleThemeToggle = async (isDark) => {
    await saveThemePreference(isDark);
    applyTheme(isDark);
    updateThemeToggleUI(isDark);
};

// Initialize theme
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const savedTheme = await loadThemePreference();
        applyTheme(savedTheme);
        updateThemeToggleUI(savedTheme);
        
        // Set up click handlers
        const lightMode = document.getElementById('light-mode');
        const darkMode = document.getElementById('dark-mode');
        
        lightMode?.addEventListener('click', () => handleThemeToggle(false));
        darkMode?.addEventListener('click', () => handleThemeToggle(true));
        
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
        updateThemeToggleUI(newTheme);
    }
});
</script>

<style>
/* Add theme-related styles */
:root {
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
    --link-color: #0d6efd;
    --muted-text: #6c757d;
}

/* Theme toggle styles */
.theme-mode-options {
    margin-bottom: 1.5rem;
}

.mode-option {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.mode-option:hover {
    background-color: rgba(0,0,0,0.05);
}

.mode-option.selected {
    background-color: rgba(75, 73, 172, 0.1);
}

/* Dark theme specific styles */
.dark-theme {
    --bs-body-color: var(--text-color);
}

.dark-theme .text-muted {
    color: var(--muted-text) !important;
}

.dark-theme a:not(.btn) {
    color: var(--link-color);
}

.dark-theme .nav-link {
    color: var(--text-color);
}

.dark-theme .nav-link:hover {
    color: var(--link-color);
}

/* Dark theme table styles */
.dark-theme .table {
    color: var(--text-color);
    border-color: var(--border-color);
}

.dark-theme .table th,
.dark-theme .table td {
    border-color: var(--border-color);
}

.dark-theme .table thead th {
    background-color: var(--card-bg);
    border-bottom: 2px solid var(--border-color);
    color: var(--text-color);
}

.dark-theme .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, 0.05);
}

.dark-theme .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.075);
}

/* Dark theme card styles */
.dark-theme .card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.dark-theme .card-title {
    color: var(--text-color);
}

.dark-theme .card-header {
    background-color: rgba(0, 0, 0, 0.2);
    border-bottom-color: var(--border-color);
}

/* Calendar styles for dark theme */
.dark-theme .fc {
    background-color: var(--card-bg);
    color: var(--text-color);
}

.dark-theme .fc-toolbar-title {
    color: var(--text-color);
}

.dark-theme .fc-button {
    background-color: #4B49AC !important;
    border-color: #4B49AC !important;
    color: #ffffff !important;
}

.dark-theme .fc-button:hover {
    background-color: #3f3e91 !important;
    border-color: #3f3e91 !important;
}

.dark-theme .fc-daygrid-day {
    background-color: var(--card-bg);
    border-color: var(--border-color) !important;
}

.dark-theme .fc-daygrid-day-number {
    color: var(--text-color) !important;
}

.dark-theme .fc-daygrid-day.fc-day-today {
    background-color: rgba(75, 73, 172, 0.15) !important;
}

.dark-theme .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    color: #6ea8fe !important;
    font-weight: bold;
}

.dark-theme .fc-col-header-cell {
    background-color: rgba(75, 73, 172, 0.1);
    color: var(--text-color);
    border-color: var(--border-color) !important;
}

/* Weekly Agenda styles for dark theme */
.dark-theme .list-group-item {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

.dark-theme .list-group-item:hover {
    background-color: var(--hover-bg);
}

.dark-theme .list-group-item strong {
    color: #6ea8fe;
}

.dark-theme .list-group-item a {
    color: #6ea8fe;
}

.dark-theme .list-group-item time {
    color: var(--muted-text);
}

/* Calendar event styles */
.dark-theme .fc-event {
    background-color: #4B49AC;
    border-color: #4B49AC;
    color: #ffffff;
}

.dark-theme .fc-event.assignment-event {
    background-color: #dc3545;
    border-color: #dc3545;
}

.dark-theme .fc-event.contest-event {
    background-color: #28a745;
    border-color: #28a745;
}

/* Empty state styling */
.dark-theme .empty-state {
    color: var(--muted-text);
}

/* Form control styles */
.dark-theme .form-control {
    background-color: var(--input-bg);
    border-color: var(--border-color);
    color: var(--input-text);
}

.dark-theme .form-control:focus {
    background-color: var(--input-bg);
    border-color: var(--link-color);
    color: var(--input-text);
}

/* Badge styles */
.dark-theme .badge {
    background-color: rgba(75, 73, 172, 0.2);
    color: #6ea8fe;
}

/* Progress bar styles */
.dark-theme .progress {
    background-color: rgba(255, 255, 255, 0.1);
}

.dark-theme .progress-bar {
    background-color: #4B49AC;
}

/* Card percentage text styles for dark theme */
.dark-theme .card .progress-wrapper {
    color: var(--text-color);
}

.dark-theme .card .progress + span {
    color: var(--text-color);
}

/* Sidebar specific dark theme styles */
.dark-theme .sidebar {
    background-color: var(--sidebar-bg);
    border-right: 1px solid var(--border-color);
}

.dark-theme .sidebar .nav .nav-item .nav-link {
    color: var(--sidebar-text);
}

.dark-theme .sidebar .nav .nav-item .nav-link:hover {
    background-color: var(--hover-bg);
}

.dark-theme .sidebar .nav .nav-item .nav-link.active {
    background-color: rgba(75, 73, 172, 0.15);
    color: #6ea8fe;
}

.dark-theme .sidebar .nav .nav-item .nav-link i {
    color: var(--sidebar-text);
}

.dark-theme .sidebar .nav .nav-item .nav-link.active i {
    color: #6ea8fe;
}

.dark-theme .sidebar hr {
    border-color: var(--border-color);
}

/* Course card specific styles */
.dark-theme .card .card-title {
    color: var(--text-color) !important;
}

.dark-theme .card .card-text {
    color: var(--text-color);
}

.dark-theme .card .card-link {
    color: #6ea8fe;
}

.dark-theme .card .card-link:hover {
    color: #8bb9fe;
    text-decoration: underline;
}

/* Progress percentage text */
.dark-theme .d-flex.align-items-center span {
    color: var(--text-color) !important;
}

/* Empty state image and text */
.dark-theme .text-center img {
    opacity: 0.8;
}

.dark-theme .text-center .text-muted {
    color: var(--muted-text) !important;
}

/* Add these discussion forum specific dark theme styles */
.dark-theme .discussion-forum {
    color: var(--text-color);
}

/* Discussion form styles */
.dark-theme .discussion-form textarea {
    background-color: var(--input-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

/* Discussion table styles */
.dark-theme .table td[style*="padding-left: 40px"] {
    color: var(--text-color);
}

/* Discussion post styles */
.dark-theme .post-content {
    color: var(--text-color);
}

/* Reply form styles */
.dark-theme #reply-form textarea {
    background-color: var(--input-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

/* Discussion timestamps */
.dark-theme .created-time {
    color: var(--muted-text);
}

/* Reply section styles */
.dark-theme .replies-section {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

/* Discussion labels and headings */
.dark-theme .discussion-label {
    color: var(--text-color);
}

/* Discussion buttons */
.dark-theme .btn-info {
    background-color: #4B49AC;
    border-color: #4B49AC;
    color: #ffffff;
}

.dark-theme .btn-info:hover {
    background-color: #3f3e91;
    border-color: #3f3e91;
}

.dark-theme .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #ffffff;
}

.dark-theme .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

/* Discussion form labels */
.dark-theme .form-group label {
    color: var(--text-color);
}

/* Discussion alerts */
.dark-theme .alert {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

/* Discussion dropdown menu */
.dark-theme .dropdown-menu {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.dark-theme .dropdown-item {
    color: var(--text-color);
}

.dark-theme .dropdown-item:hover {
    background-color: var(--hover-bg);
    color: var(--link-color);
}

/* Discussion scrollbar */
.dark-theme ::-webkit-scrollbar-track {
    background: var(--card-bg);
}

.dark-theme ::-webkit-scrollbar-thumb {
    background: #666;
}

.dark-theme ::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Add these styles for discussion forum headings and titles */
.dark-theme .card-title.mb-0 {
    color: var(--text-color) !important;
    font-weight: 600;
}

.dark-theme .margin-top {
    color: var(--text-color) !important;
}

.dark-theme h4, 
.dark-theme h5, 
.dark-theme h6 {
    color: var(--text-color) !important;
}

/* Discussion section headings */
.dark-theme .section-heading {
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Table header text */
.dark-theme thead th {
    color: var(--text-color) !important;
    font-weight: 600;
}

/* Discussion forum message text */
.dark-theme .message-text {
    color: var(--text-color);
}

/* Discussion forum metadata */
.dark-theme .post-metadata {
    color: var(--muted-text);
}

/* Make sure all paragraph text is visible */
.dark-theme p {
    color: var(--text-color);
}

/* Update the sidebar styles */
.sidebar .nav .nav-item .nav-link {
    color: #333333;
    transition: all 0.3s ease;
}

.sidebar .nav .nav-item .nav-link i {
    color: #333333;
    transition: all 0.3s ease;
}

.sidebar .nav .nav-item .nav-link:hover {
    background-color: #4B49AC;
    color: #ffffff;
}

.sidebar .nav .nav-item .nav-link:hover i {
    color: #ffffff;
}

.sidebar .nav .nav-item .nav-link.active {
    background-color: #4B49AC;
    color: #ffffff !important;
}

.sidebar .nav .nav-item .nav-link.active i {
    color: #ffffff !important;
}

/* Dark theme sidebar styles */
.dark-theme .sidebar .nav .nav-item .nav-link {
    color: #e1e1e1;
    transition: all 0.3s ease;
}

.dark-theme .sidebar .nav .nav-item .nav-link i {
    color: #e1e1e1;
    transition: all 0.3s ease;
}

.dark-theme .sidebar .nav .nav-item .nav-link:hover {
    background-color: #4B49AC;
    color: #ffffff;
}

.dark-theme .sidebar .nav .nav-item .nav-link:hover i {
    color: #ffffff;
}

.dark-theme .sidebar .nav .nav-item .nav-link.active {
    background-color: #4B49AC;
    color: #ffffff !important;
}

.dark-theme .sidebar .nav .nav-item .nav-link.active i {
    color: #ffffff !important;
}
</style>
