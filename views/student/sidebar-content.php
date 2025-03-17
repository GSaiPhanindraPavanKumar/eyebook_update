<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
use Models\Database;
$conn = Database::getConnection();

// Get user data from database
$email = $_SESSION['email'] ?? '';
$stmt = $conn->prepare("SELECT * FROM students WHERE email = :email");
$stmt->execute(['email' => $email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    $studentName = htmlspecialchars($userData['name'] ?? 'Student');
    $studentEmail = htmlspecialchars($userData['email']);
    $profileImageUrl = !empty($userData['profile_image_url']) && filter_var($userData['profile_image_url'], FILTER_VALIDATE_URL) ? 
        htmlspecialchars($userData['profile_image_url']) : 
        'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=4B49AC&color=fff';
} else {
    $studentName = 'Student';
    $studentEmail = $email;
    $profileImageUrl = 'https://ui-avatars.com/api/?name=Student&background=4B49AC&color=fff';
}
?>

<!-- Add Tailwind and Alpine.js -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#4B49AC',
                    'primary-hover': '#3f3e91',
                    'secondary': '#7978E9',
                    'success': '#3AC977',
                    'warning': '#F3B415',
                    'info': '#3BA2B8',
                    'danger': '#FF4747'
                },
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                }
            }
        },
        darkMode: 'class'
    }
</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    /* Update your existing sidebar styles */
    .sidebar-expanded {
        width: 256px;
        transition: width 0.3s ease-in-out;
    }
    
    .sidebar-collapsed {
        width: 80px;
        transition: width 0.3s ease-in-out;
    }
    
    .main-content-expanded {
        margin-left: 256px;
        transition: margin-left 0.3s ease-in-out;
    }
    
    .main-content-collapsed {
        margin-left: 80px;
        transition: margin-left 0.3s ease-in-out;
    }
    
    /* Make sure transitions are smooth for all elements */
    .nav-text,
    .category-header,
    .logo-text {
        transition: opacity 0.2s ease-in-out;
    }
    
    /* Hide elements in collapsed state */
    .sidebar-collapsed .nav-text,
    .sidebar-collapsed .category-header,
    .sidebar-collapsed .logo-text {
        display: none;
    }
    
    /* Center icons in collapsed state */
    .sidebar-collapsed .nav-link {
        justify-content: center;
        padding: 0.75rem 0;
    }
    
    .sidebar-collapsed .nav-link svg {
        margin: 0;
    }
</style>

<!-- Top Navigation Bar -->
<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 fixed top-0 right-0 left-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" >
        <div class="flex justify-between h-16" style="margin-left: -35px;">
            <!-- Left side with Logo and Toggle -->
            <div class="flex items-center" >
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <img src="../../views/public/assets/images/logo1.png" alt="Knowbots Logo" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        Knowbots
                    </span>
                </div>
                
                <!-- Sidebar Toggle -->
                <button id="sidebarToggle" class="ml-4 p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg id="lightIcon" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="darkIcon" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 bg-white dark:bg-gray-800 shadow-lg sidebar-expanded z-20 mt-16" id="sidebar">
    <div class="flex flex-col h-full">
        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            <div class="space-y-2">
                <!-- Dashboard -->
                <a href="/student/dashboard" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                    <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>

                <!-- Course Management Section -->
                <div class="nav-category">
                    <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase category-header">
                        Course Management
                    </div>
                    
                    <a href="/student/my_courses" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="nav-text">My Courses</span>
                    </a>
                    
                    <a href="/student/manage_public_courses" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="nav-text">Paid Courses</span>
                    </a>

                    <a href="/student/virtual_classroom" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="nav-text">Virtual Classroom</span>
                    </a>
                </div>

                <!-- Assignments & Labs Section -->
                <div class="nav-category mt-4">
                    <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase category-header">
                        Assignments & Labs
                    </div>
                    
                    <a href="/student/manage_assignments" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="nav-text">Assignments</span>
                    </a>

                    <a href="/student/view_assessments" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="nav-text">Assessments</span>
                    </a>
                </div>

                <!-- Community Section -->
                <div class="nav-category mt-4">
                    <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase category-header">
                        Community
                    </div>
                    
                    <a href="/student/discussion_forum" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        <span class="nav-text">Discussion Forum</span>
                    </a>
                </div>

                <!-- Miscellaneous Section -->
                <div class="nav-category mt-4">
                    <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase category-header">
                        Miscellaneous
                    </div>
                    
                    <a href="/student/xp_status" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="nav-text">XP Status</span>
                    </a>

                    <a href="/student/tickets" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span class="nav-text">Support Tickets</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Profile Section -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <img class="h-8 w-8 rounded-full object-cover" 
                    src="<?php echo $profileImageUrl; ?>"
                    alt="Profile">
                <div class="ml-3 min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"><?php echo htmlspecialchars($studentName); ?></div>
                    <div class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($studentEmail); ?></div>
                </div>
                <div class="ml-auto">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                            class="absolute right-0 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="/student/updatePassword" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Change Password</a>
                                <a href="/student/profile" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">My Profile</a>
                                <a href="/logout" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Chatbot Button -->
<div class="fixed bottom-6 right-6 z-50">
    <a href="/student/askguru" 
       class="flex items-center justify-center w-14 h-14 bg-primary hover:bg-primary-hover text-white rounded-full shadow-lg transition-all duration-300 hover:scale-110 focus:outline-none">
        <div class="relative">
            <!-- Chatbot Icon -->
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <!-- Pulse Animation -->
            <span class="absolute top-0 right-0 h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
            </span>
        </div>
    </a>
    <!-- Tooltip -->
    <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block">
        <div class="bg-gray-800 text-white text-sm rounded py-1 px-2 whitespace-nowrap">
            Ask Guru
        </div>
    </div>
</div>

<script>
    // Theme switching functionality
    const themeToggle = document.getElementById('themeToggle');
    const lightIcon = document.getElementById('lightIcon');
    const darkIcon = document.getElementById('darkIcon');
    
    function updateThemeIcons() {
        if (document.documentElement.classList.contains('dark')) {
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
        } else {
            lightIcon.classList.add('hidden');
            darkIcon.classList.remove('hidden');
        }
    }
    
    // Initial icon state
    updateThemeIcons();
    
    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        updateThemeIcons();
    });

    // Check for saved theme preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        if (sidebar) {
            sidebar.classList.toggle('sidebar-expanded');
            sidebar.classList.toggle('sidebar-collapsed');
        }
        
        if (mainContent) {
            mainContent.classList.toggle('main-content-expanded');
            mainContent.classList.toggle('main-content-collapsed');
        }
        
        // Save state to localStorage
        const isCollapsed = sidebar?.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    }

    // Initialize sidebar state
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // Get saved state
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (sidebar) {
            if (isCollapsed) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.add('sidebar-expanded');
                sidebar.classList.remove('sidebar-collapsed');
            }
        }
        
        if (mainContent) {
            if (isCollapsed) {
                mainContent.classList.remove('main-content-expanded');
                mainContent.classList.add('main-content-collapsed');
            } else {
                mainContent.classList.add('main-content-expanded');
                mainContent.classList.remove('main-content-collapsed');
            }
        }
        
        // Add click event listener to toggle button
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
    });
</script> 

</body>
</html> 