<?php
// Start the session
// session_start();

// Include the autoload file
require __DIR__ . '/../../vendor/autoload.php';

// Check if the user is not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: /");
    exit;
}

// Get the admin data from the session
$admin = $_SESSION['admin'];

// Use the Admin model to fetch user details
$adminModel = new \Models\Admin();
// $userData = $adminModel->userprofile();

// Check if user details are found
// if (!$userData) {
//     echo "Invalid Credentials";
//     exit;
// }

// Add after getting admin data
$profileImageUrl = isset($admin['profile_image_url']) && filter_var($admin['profile_image_url'], FILTER_VALIDATE_URL) ? 
    htmlspecialchars($admin['profile_image_url']) : 
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
    <link rel="stylesheet" href="/views/public/vendors/feather/feather.css">
    <link rel="stylesheet" href="/views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/views/public/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/views/public/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="/views/public/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="../../views/public/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="/views/public/css/vertical-layout-light/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="/views/public/assets/images/android-chrome-512x512.png" />
</head>
<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo mr-5" href="#"><img src="/views/public/assets/images/logo1.png" class="mr-2" alt="logo" height="100" width="50">Knowbots</a>
            <a class="navbar-brand brand-logo-mini" href="#"><img src="/views/public/assets/images/logo1.png" alt="logo" height="100" width="50"></a>
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
                            <img src="/views/public/images/user.jpg" alt="profile"/>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="/admin/profile">
                            <i class="ti-user text-primary"></i>
                            Profile
                        </a>
                        <a class="dropdown-item" href="/admin/logout">
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
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item"><hr></li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#university" aria-expanded="false" aria-controls="university">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">University</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="university">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/addCompany" style="font-size: 12px;">Create University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_company" style="font-size: 12px;">Manage University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/addUniversity" style="font-size: 12px;">Create Sub-University</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_university" style="font-size: 12px;">Manage Sub-University</a></li>
<!--                            <li class="nav-item"> <a class="nav-link" href="#">Typography</a></li>-->
                        </ul>
                    </div>
                </li>


                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#courses" aria-expanded="false" aria-controls="courses">
                        <i class="icon-columns menu-icon"></i>
                        <span class="menu-title" >Courses</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="courses">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="/admin/add_courses" style="font-size: 12px;">Add Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/admin/manage_courses" style="font-size: 12px;">Manage Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/admin/manage_public_courses" style="font-size: 12px;">Manage Public Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/admin/virtual_classroom" style="font-size: 12px;">Virtual Classroom</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" href="courses">Submission</a></li> -->
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#faculty" aria-expanded="false" aria-controls="faculty">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Faculty</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="faculty">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/uploadFaculty" style="font-size: 12px;">Upload Faculty</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_faculty" style="font-size: 12px;">Manage Faculty</a></li>
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
                        <li class="nav-item"> <a class="nav-link" href="/admin/uploadStudents" style="font-size: 12px;">Upload Students</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_students" style="font-size: 12px;">Manage Student</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#assessments" aria-expanded="false" aria-controls="assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assignments</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="assessments">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_assignment" style="font-size: 12px;">Create Assignments</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_assignments" style="font-size: 12px;">Manage Assignments</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/manage_assessments">
                        <i class="icon-bar-graph menu-icon"></i>
                        <span class="menu-title">Assessments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#cohort" aria-expanded="false" aria-controls="cohort">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Cohort</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="cohort">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_cohort" style="font-size: 12px;">Create Cohort</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_cohort" style="font-size: 12px;">Manage Cohort</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#labs" aria-expanded="false" aria-controls="labs">
                        <i class="icon-layout menu-icon"></i>
                        <span class="menu-title">Labs</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="labs">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_lab" style="font-size: 12px;">Create Lab</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_labs" style="font-size: 12px;">Manage Labs</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_public_lab" style="font-size: 12px;">Create Public Lab</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_public_labs" style="font-size: 12px;">Manage Public Labs</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/create_contest" style="font-size: 12px;">Create Contest</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/manage_contest" style="font-size: 12px;">Manage Contest</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/admin/certificate_generations">
                        <i class="icon-grid menu-icon"></i>
                        <span class="menu-title">Certificate Generation</span>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#tickets" aria-expanded="false" aria-controls="tickets">
                        <i class="icon-paper menu-icon"></i>
                        <span class="menu-title">Support Tickets</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="tickets">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="/admin/tickets" style="font-size: 12px;">Manage Tickets</a></li>
                            <li class="nav-item"> <a class="nav-link" href="/admin/ticket_analytics" style="font-size: 12px;">Ticket Analytics</a></li>
                        </ul>
                    </div>
                </li> -->

                <li class="nav-item"><hr></li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/profile">
                        <i class="ti-user menu-icon"></i>
                        <span class="menu-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/updatePassword">
                        <i class="ti-settings menu-icon"></i>
                        <span class="menu-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logout">
                        <i class="ti-power-off menu-icon"></i>
                        <span class="menu-title">Logout</span>
                </a>
            </li>
            </ul>

        </nav>
        <!-- partial -->
        
        <!-- Add these script tags before closing body tag
        <script src="/views/public/vendors/js/vendor.bundle.base.js"></script>
        <script src="/views/public/js/off-canvas.js"></script>
        <script src="/views/public/js/hoverable-collapse.js"></script>
        <script src="/views/public/js/template.js"></script>
        <script src="/views/public/js/settings.js"></script>
        <script src="/views/public/js/todolist.js"></script>
        End custom js for this page-->
    </body>
</html>

<script>
// Theme management constants
const THEME_DB_NAME = 'adminThemeDB';
const THEME_STORE_NAME = 'themeSettings';
const THEME_KEY = 'currentTheme';

// Initialize IndexedDB with better error handling
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

// Save theme with better error handling
const saveThemePreference = async (isDark) => {
    try {
        const db = await initThemeDB();
        const tx = db.transaction(THEME_STORE_NAME, 'readwrite');
        const store = tx.objectStore(THEME_STORE_NAME);
        
        return new Promise((resolve, reject) => {
            const request = store.put(isDark, THEME_KEY);
            
            request.onsuccess = () => {
                // Broadcast theme change to other open admin pages
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
        // Fallback to localStorage if IndexedDB fails
        localStorage.setItem(THEME_KEY, JSON.stringify(isDark));
    }
};

// Load theme with better error handling
const loadThemePreference = async () => {
    try {
        const db = await initThemeDB();
        const tx = db.transaction(THEME_STORE_NAME, 'readonly');
        const store = tx.objectStore(THEME_STORE_NAME);
        
        return new Promise((resolve, reject) => {
            const request = store.get(THEME_KEY);
            
            request.onsuccess = () => {
                resolve(request.result ?? false); // Default to light theme
            };
            
            request.onerror = () => {
                console.error('Failed to load theme:', request.error);
                reject(request.error);
            };
            
            tx.oncomplete = () => db.close();
        });
    } catch (error) {
        console.error('Error loading theme:', error);
        // Fallback to localStorage if IndexedDB fails
        const fallbackTheme = localStorage.getItem(THEME_KEY);
        return fallbackTheme ? JSON.parse(fallbackTheme) : false;
    }
};

// Listen for theme changes from other windows/tabs
window.addEventListener('storage', async (event) => {
    if (event.key === 'themeChange') {
        const newTheme = await loadThemePreference();
        applyTheme(newTheme);
        updateThemeToggleUI(newTheme);
    }
});

// Update UI to match current theme
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

// Update the theme toggle handler
const handleThemeToggle = async (isDark) => {
    await saveThemePreference(isDark);
    applyTheme(isDark);
    updateThemeToggleUI(isDark);
};

// Initialize theme on page load
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
        // Fallback to light theme
        applyTheme(false);
    }
});

// Apply theme to the entire site
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
        document.body.classList.add('dark-theme');
        
        // Update sidebar theme
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('sidebar-light');
            sidebar.classList.add('sidebar-dark');
        }

        // Update navbar theme
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.remove('navbar-light');
            navbar.classList.add('navbar-dark');
        }
        
        // Update content wrapper background
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '#1a1a1a';
        }

        // Update table styles
        root.style.setProperty('--table-stripe-bg', 'rgba(255, 255, 255, 0.05)');
        root.style.setProperty('--table-hover-bg', 'rgba(255, 255, 255, 0.075)');

        // Add these calendar-specific styles to your themeStyles
        themeStyles.textContent += `
        /* Calendar styles */
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
            font-weight: 500;
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

        /* Weekly Agenda styles */
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
            text-decoration: none;
        }

        .dark-theme .list-group-item a:hover {
            text-decoration: underline;
        }

        /* Calendar event colors */
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

        /* Calendar event popover */
        .dark-theme .fc-popover {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        .dark-theme .fc-popover-header {
            background-color: rgba(75, 73, 172, 0.1);
            color: var(--text-color);
        }

        /* Calendar more link */
        .dark-theme .fc-daygrid-more-link {
            color: #6ea8fe !important;
        }

        /* Weekly agenda time slots */
        .dark-theme .list-group-item time {
            color: var(--muted-text);
        }

        .dark-theme .list-group-item .event-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.875em;
            margin-right: 8px;
        }

        .dark-theme .list-group-item .event-type.class {
            background-color: rgba(75, 73, 172, 0.2);
            color: #6ea8fe;
        }

        .dark-theme .list-group-item .event-type.assignment {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ea868f;
        }

        .dark-theme .list-group-item .event-type.contest {
            background-color: rgba(40, 167, 69, 0.2);
            color: #75b798;
        }

        /* Empty state styling */
        .dark-theme .list-group-item.empty-state {
            color: var(--muted-text);
            font-style: italic;
            text-align: center;
            padding: 2rem;
        }
        `;

        // Add these variables to your dark theme in applyTheme function
        root.style.setProperty('--calendar-bg', '#2d2d2d');
        root.style.setProperty('--calendar-header', 'rgba(75, 73, 172, 0.1)');
        root.style.setProperty('--calendar-today', 'rgba(75, 73, 172, 0.15)');
        root.style.setProperty('--calendar-event', '#4B49AC');
        root.style.setProperty('--calendar-text', '#e1e1e1');
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
        document.body.classList.remove('dark-theme');
        
        // Update sidebar theme
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('sidebar-dark');
            sidebar.classList.add('sidebar-light');
        }

        // Update navbar theme
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-light');
        }
        
        // Reset content wrapper background
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '#f4f7fa';
        }

        // Reset table styles
        root.style.setProperty('--table-stripe-bg', 'rgba(0, 0, 0, 0.05)');
        root.style.setProperty('--table-hover-bg', 'rgba(0, 0, 0, 0.075)');

        // Add these variables to your light theme in applyTheme function
        root.style.setProperty('--calendar-bg', '#ffffff');
        root.style.setProperty('--calendar-header', '#f8f9fa');
        root.style.setProperty('--calendar-today', '#e8f4ff');
        root.style.setProperty('--calendar-event', '#4B49AC');
        root.style.setProperty('--calendar-text', '#333333');
    }
};

// Add this CSS to the head of the document
const themeStyles = document.createElement('style');
themeStyles.textContent = `
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
    --table-stripe-bg: rgba(0, 0, 0, 0.05);
    --table-hover-bg: rgba(0, 0, 0, 0.075);
}

body {
    background-color: var(--body-bg);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
}

.card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.form-control {
    background-color: var(--input-bg);
    color: var(--input-text);
    border-color: var(--border-color);
}

.table {
    background-color: var(--table-bg);
    color: var(--text-color);
}

.table td, .table th {
    border-color: var(--table-border);
}

.table-hover tbody tr:hover {
    background-color: var(--hover-bg);
}

.modal-content {
    background-color: var(--card-bg);
    color: var(--text-color);
}

.alert {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

/* Dark theme specific overrides */
.dark-theme .btn-primary {
    background-color: #0d6efd;
    border-color: #0a58ca;
}

.dark-theme .btn-secondary {
    background-color: #6c757d;
    border-color: #565e64;
}

.dark-theme .alert-success {
    background-color: rgba(40, 167, 69, 0.2);
    border-color: rgba(40, 167, 69, 0.3);
    color: #98c9a3;
}

.dark-theme .alert-danger {
    background-color: rgba(220, 53, 69, 0.2);
    border-color: rgba(220, 53, 69, 0.3);
    color: #ea868f;
}

.dark-theme .alert-warning {
    background-color: rgba(255, 193, 7, 0.2);
    border-color: rgba(255, 193, 7, 0.3);
    color: #ffda6a;
}

.dark-theme .alert-info {
    background-color: rgba(23, 162, 184, 0.2);
    border-color: rgba(23, 162, 184, 0.3);
    color: #6edff6;
}

.dark-theme .pagination .page-link {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--link-color);
}

.dark-theme .pagination .page-item.active .page-link {
    background-color: var(--link-color);
    border-color: var(--link-color);
    color: #ffffff;
}

.settings-panel {
    padding: 2rem;
}

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
    background-color: rgba(110, 168, 254, 0.2);
}

.color-tiles {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
}

.color-tiles .tiles {
    width: 35px;
    height: 35px;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s;
}

.color-tiles .tiles:hover {
    transform: scale(1.1);
}

/* Header skin colors */
.tiles.primary { background-color: #4B49AC; }
.tiles.success { background-color: #28a745; }
.tiles.warning { background-color: #ffc107; }
.tiles.danger { background-color: #dc3545; }
.tiles.info { background-color: #17a2b8; }
.tiles.dark { background-color: #343a40; }
.tiles.light { background-color: #f8f9fa; border: 1px solid #dee2e6; }

/* Add sidebar specific styles */
.sidebar {
    background-color: var(--sidebar-bg);
    color: var(--sidebar-text);
    transition: background-color 0.3s, color 0.3s;
}

.sidebar .nav-link {
    color: var(--sidebar-text);
}

.sidebar-dark {
    background-color: #2d2d2d;
}

.sidebar-dark .nav-link {
    color: #ffffff;
}

.sidebar-dark .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Navbar styles */
.navbar {
    background-color: var(--navbar-bg);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
}

/* Settings panel styles */
.settings-panel {
    padding: 2rem;
    background-color: var(--card-bg);
    color: var(--text-color);
}

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
    background-color: rgba(110, 168, 254, 0.2);
}

/* Dark theme overrides */
.dark-theme .sidebar {
    border-right: 1px solid var(--border-color);
}

.dark-theme .navbar {
    border-bottom: 1px solid var(--border-color);
}

.dark-theme .settings-panel {
    border-left: 1px solid var(--border-color);
}

/* Dark theme specific overrides */
.dark-theme {
    /* Improve text visibility in dark mode */
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

.dark-theme .sidebar .nav-link {
    color: var(--sidebar-text);
}

.dark-theme .sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--link-color);
}

.dark-theme .menu-title {
    color: var(--text-color) !important;
}

.dark-theme .card-title {
    color: var(--text-color);
}

.dark-theme .form-control {
    background-color: var(--input-bg);
    color: var(--input-text);
    border-color: var(--border-color);
}

.dark-theme .form-control:focus {
    background-color: var(--input-bg);
    color: var(--input-text);
    border-color: var(--link-color);
    box-shadow: 0 0 0 0.2rem rgba(110, 168, 254, 0.25);
}

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
    background-color: var(--table-stripe-bg);
    color: var(--text-color);
}

.dark-theme .table-hover tbody tr:hover {
    background-color: var(--table-hover-bg);
    color: var(--text-color);
}

.dark-theme .table-bordered {
    border-color: var(--border-color);
}

.dark-theme .table-bordered th,
.dark-theme .table-bordered td {
    border-color: var(--border-color);
}

.dark-theme .table-responsive {
    border-color: var(--border-color);
}

.dark-theme .table caption {
    color: var(--muted-text);
}

.dark-theme .table .sorting::after,
.dark-theme .table .sorting_asc::after,
.dark-theme .table .sorting_desc::after {
    color: var(--text-color);
}

.dark-theme .table tfoot th,
.dark-theme .table tfoot td {
    border-top: 2px solid var(--border-color);
    color: var(--text-color);
}

.dark-theme .table .selected {
    background-color: rgba(110, 168, 254, 0.1) !important;
}

.dark-theme .table .highlight {
    background-color: rgba(255, 193, 7, 0.1);
}
`;

document.head.appendChild(themeStyles);
</script>