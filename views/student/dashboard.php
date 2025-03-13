<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
use Models\Database;
use Models\Notification;

$conn = Database::getConnection();

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: /login");
    exit;
}

// Get user data
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM students WHERE email = :email");
$stmt->execute(['email' => $email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    $studentId = $userData['id'];
    $studentName = htmlspecialchars($userData['name'] ?? 'Student');
    $studentEmail = htmlspecialchars($userData['email']);
    // Format notifications with default values
    $notifications = array_map(function($notification) {
        return [
            'message' => $notification['message'] ?? '',
            'created_at' => $notification['created_at'] ?? '',
            'link' => $notification['link'] ?? '#',
            'type' => $notification['type'] ?? 'info',
            'is_read' => $notification['is_read'] ?? false
        ];
    }, Notification::getByStudentId($conn, $studentId));
    $profileImageUrl = !empty($userData['profile_image_url']) && filter_var($userData['profile_image_url'], FILTER_VALIDATE_URL) ? 
        htmlspecialchars($userData['profile_image_url']) : 
        'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=4B49AC&color=fff';
} else {
    header("Location: /logout");
    exit;
}

// Get statistics
$stmt = $conn->prepare("SELECT 
    assigned_courses,
    xp,
    level,
    check_in_streak,
    total_check_ins,
    last_check_in
  FROM students 
  WHERE id = :student_id");
$stmt->execute(['student_id' => $studentId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get enrolled courses count
$assignedCourses = json_decode($stats['assigned_courses'] ?? '[]', true);
$enrolledCourses = count($assignedCourses);

// Get assignments due
$stmt = $conn->prepare("SELECT COUNT(*) as assignment_count FROM assignments WHERE due_date > NOW()");
$stmt->execute();
$assignmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
$assignmentsDue = $assignmentStats['assignment_count'] ?? 0;

// Get XP Points
$xpPoints = $stats['xp'] ?? 0;

// Calculate completion rate based on completed_books
$completedBooks = json_decode($userData['completed_books'] ?? '[]', true);
$totalBooks = count($assignedCourses);
$completionRate = $totalBooks > 0 ? round((count($completedBooks) / $totalBooks) * 100) : 0;

// Check if the user has checked in today
$today = date('Y-m-d');
$lastCheckIn = $stats['last_check_in'] ?? null;
$hasCheckedInToday = ($lastCheckIn === $today);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add IndexedDB library -->
    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>
    <!-- Add Alpine.js first -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Check for saved theme preference, otherwise use system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        // IndexedDB setup for notifications
        const dbName = 'studentDB';
        const storeName = 'notifications';
        
        async function initDB() {
            const db = await idb.openDB(dbName, 1, {
                upgrade(db) {
                    if (!db.objectStoreNames.contains(storeName)) {
                        const store = db.createObjectStore(storeName, { keyPath: 'id', autoIncrement: true });
                        store.createIndex('read', 'read');
                        store.createIndex('timestamp', 'timestamp');
                    }
                },
            });
            return db;
        }
        
        async function saveNotification(notification) {
            const db = await initDB();
            await db.add(storeName, {
                ...notification,
                timestamp: new Date().toISOString(),
                read: false
            });
        }
        
        async function markAsRead(notificationId) {
            const db = await initDB();
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const notification = await store.get(notificationId);
            if (notification) {
                notification.read = true;
                await store.put(notification);
            }
            await tx.done;
        }
        
        async function getUnreadCount() {
            const db = await initDB();
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const unread = await store.index('read').count(0);
            return unread;
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Knowbots</title>
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
                    },
                }
            },
            darkMode: 'class'
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react/24/outline@latest/index.min.js"></script>
    <style>
        /* Smooth transitions for sidebar */
        .sidebar-expanded {
            width: 256px;
            transition: all 0.3s ease-in-out;
        }
        
        .sidebar-collapsed {
            width: 80px;
            transition: all 0.3s ease-in-out;
        }
        
        .nav-text {
            transition: opacity 0.2s ease-in-out;
            white-space: nowrap;
        }
        
        .main-content-expanded {
            padding-left: 256px;
            transition: all 0.3s ease-in-out;
        }
        
        .main-content-collapsed {
            padding-left: 80px;
            transition: all 0.3s ease-in-out;
        }
        
        /* Hide text in collapsed state */
        .sidebar-collapsed .nav-text {
            display: none;
        }
        
        /* Hide category headers in collapsed state */
        .sidebar-collapsed .category-header {
            display: none;
        }
        
        /* Hide logo text in collapsed state */
        .sidebar-collapsed .logo-text {
            display: none;
        }
        
        /* Center icons in collapsed state */
        .sidebar-collapsed .nav-link {
            justify-content: center;
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }
        
        /* Fix icon alignment in collapsed state */
        .sidebar-collapsed .nav-link svg {
            margin: 0;
        }
        
        /* Modal backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 0.3s ease-in-out;
        }
    </style>
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 bg-white dark:bg-gray-800 shadow-lg sidebar-expanded z-20" id="sidebar">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4 py-5 border-b border-gray-200 dark:border-gray-700">
                    <img src="../../views/public/assets/images/logo1.png" alt="Knowbots Logo" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent logo-text">
                        Knowbots
                    </span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                    <div class="space-y-2">
                        <!-- Dashboard -->
                        <a href="/student/dashboard" class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-primary/10 text-primary dark:text-white dark:bg-primary/20 group transition-all duration-300">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
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

                        <!-- Progress Tracking -->
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
                            <!-- <a href="/student/view_grades" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-primary group">
                                <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="flex-1">View Grades</span>
                            </a> -->
                        </div>

                        <!-- Labs Section -->
                        <!-- <div class="nav-category mt-4">
                            <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 uppercase">
                                Labs
                            </div>
                            
                            <a href="/student/view_lab" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-primary group">
                                <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                <span class="flex-1">Labs</span>
                            </a>
                            
                            <a href="/student/view_public_lab" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-primary group">
                                <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span class="flex-1">Public Labs</span>
                            </a>
                        </div> -->

                        <!-- Assessments Section -->
                        <div class="nav-category mt-4">
                            <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase category-header">
                                Assessments
                            </div>
                            
                            <a href="/student/view_assessments" class="nav-link flex items-center px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-white group transition-all duration-300">
                                <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span class="nav-text">Assessments</span>
                            </a>
                        </div>

                        <!-- Support Section -->
                        <!-- <div class="nav-category mt-4">
                            <div class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 uppercase">
                                Support
                            </div>
                            

                        </div> -->
                    </div>
                </nav>

                <!-- Profile Section -->
                <div class="flex-shrink-0 border-t border-gray-200 p-4">
                    <div class="flex items-center">
                        <img class="h-8 w-8 rounded-full object-cover" 
                            src="<?php echo $profileImageUrl; ?>"
                            alt="Profile">
                        <div class="ml-3 min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"><?php echo $studentName; ?></div>
                            <div class="text-xs text-gray-500 truncate"><?php echo $studentEmail; ?></div>
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

        <div class="flex-1 main-content-expanded" id="main-content">
            <!-- Navbar -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- Left side -->
                        <div class="flex items-center">
                            <!-- Sidebar Toggle -->
                            <button id="sidebarToggle" class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Search Bar -->
                        <div class="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-end">
                            <div class="max-w-lg w-full lg:max-w-xs">
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input id="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Search courses..." type="search">
                                </div>
                            </div>
                        </div>

                        <!-- Right side buttons -->
                        <div class="flex items-center space-x-4">
                            <!-- Theme Toggle -->
                            <button id="themeToggle" class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                                <!-- Sun icon -->
                                <svg id="lightIcon" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <!-- Moon icon -->
                                <svg id="darkIcon" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>
                            <!-- Notifications -->
                            <div x-data="notificationsData()" 
                                 @keydown.escape="showNotifications = false"
                                 @notification-received.window="handleNewNotification($event.detail)">
                                <button @click="showNotifications = true" 
                                        class="flex-shrink-0 p-1 rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200">
                                    <span class="sr-only">Notifications</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount"
                                          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                                    </span>
                                </button>
                                
                                <!-- Notifications Modal -->
                                <div x-show="showNotifications" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 z-50 overflow-y-auto" 
                                     aria-labelledby="modal-title" 
                                     role="dialog" 
                                     aria-modal="true"
                                     @click.away="showNotifications = false"
                                     style="display: none;">
                                    
                                    <!-- Modal backdrop -->
                                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

                                    <!-- Modal panel -->
                                    <div class="relative min-h-screen flex items-center justify-center p-4">
                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full shadow-xl">
                                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                                <button @click="showNotifications = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                                    <span class="sr-only">Close</span>
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[70vh] overflow-y-auto">
                                                <template x-if="notifications.length > 0">
                                                    <template x-for="notification in notifications" :key="notification.id">
                                                        <a :href="notification.link" 
                                                           @click="markRead(notification.id); showNotifications = false; $event.stopPropagation();"
                                                           class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                           :class="{ 'opacity-75': notification.read }">
                                                            <div class="flex items-start">
                                                                <div class="flex-shrink-0">
                                                                    <template x-if="notification.type === 'success'">
                                                                        <span class="w-2 h-2 bg-success rounded-full block"></span>
                                                                    </template>
                                                                    <template x-if="notification.type === 'warning'">
                                                                        <span class="w-2 h-2 bg-warning rounded-full block"></span>
                                                                    </template>
                                                                    <template x-if="notification.type === 'info'">
                                                                        <span class="w-2 h-2 bg-info rounded-full block"></span>
                                                                    </template>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <p class="text-sm text-gray-900 dark:text-white" x-text="notification.message"></p>
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="formatDate(notification.timestamp)"></p>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </template>
                                                </template>
                                                <template x-if="notifications.length === 0">
                                                    <div class="px-4 py-3">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">No new notifications</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none dark:bg-gray-900">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        <!-- Welcome Section -->
                        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-6 md:p-8 text-white mb-6">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                                <div>
                                    <h1 class="text-2xl md:text-3xl font-bold">Welcome back, <?php echo $studentName; ?>! 👋</h1>
                                    <p class="mt-2 text-white/80">Ready to continue your learning journey?</p>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    <a href="/student/all_courses" class="inline-flex items-center px-4 py-2 bg-white text-primary rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors">
                                        Explore Courses
                                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <!-- Check-in Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex flex-col space-y-4">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-lg bg-warning/10 dark:bg-warning/20">
                                            <svg class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Daily Check-in</h3>
                                            <?php if (!$hasCheckedInToday): ?>
                                                <button id="checkInButton" 
                                                        onclick="handleCheckIn()"
                                                        class="mt-2 w-full px-4 py-2 bg-warning hover:bg-warning/90 text-white rounded-lg transition-colors">
                                                    Check In
                                                </button>
                                            <?php else: ?>
                                                <p class="text-sm font-medium text-success">✓ Checked In</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Check-in History Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-info/10 dark:bg-info/20">
                                        <svg class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-in History</h3>
                                        <div class="flex items-center space-x-2">
                                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $stats['total_check_ins']; ?></p>
                                            <button onclick="viewCheckInHistory()"
                                                    class="text-sm text-primary hover:text-primary-hover dark:text-primary dark:hover:text-primary-hover transition-colors">
                                                View History
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Level Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-primary/10 dark:bg-primary/20">
                                        <svg class="h-6 w-6 text-primary dark:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Level</h3>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $stats['level']; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Check-in Streak -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-warning/10 dark:bg-warning/20">
                                        <svg class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-in Streak</h3>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $stats['check_in_streak']; ?> days</p>
                                    </div>
                                </div>
                            </div>

                            <!-- XP Points -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-success/10 dark:bg-success/20">
                                        <svg class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">XP Points</h3>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo number_format($xpPoints); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Completion Rate -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-info/10 dark:bg-info/20">
                                        <svg class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Completion Rate</h3>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $completionRate; ?>%</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity & Upcoming Tasks -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Recent Activity -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
                                    <div class="space-y-4">
                                        <!-- Activity Item -->
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Completed Python Basics Quiz</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Score: 95%</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">2 hours ago</p>
                                            </div>
                                        </div>

                                        <!-- More activity items... -->
                                    </div>
                                </div>
                            </div>

                            <!-- Upcoming Tasks -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upcoming Tasks</h2>
                                    <div class="space-y-4">
                                        <!-- Task Item -->
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-warning/10 dark:bg-warning/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Database Design Assignment</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Due in 2 days</p>
                                                <div class="mt-2">
                                                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                        <div class="bg-warning rounded-full h-1.5 w-3/4"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- More task items... -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Support Chat Icon -->
    <div class="fixed bottom-6 right-6 z-50">
        <button onclick="toggleChat()" 
                class="bg-primary hover:bg-primary-hover text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </button>
    </div>

    <script>
        // Add Alpine.js for dropdown functionality
        document.addEventListener('alpine:init', () => {
            Alpine.data('dropdown', () => ({
                open: false,
                toggle() {
                    this.open = !this.open
                }
            }))
        })

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
        
        // Function to toggle sidebar collapse
        function toggleSidebar() {
            const sidebar = document.querySelector('#sidebar');
            const mainContent = document.querySelector('#main-content');
            
            // Toggle classes
            sidebar.classList.toggle('sidebar-expanded');
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-expanded');
            mainContent.classList.toggle('main-content-collapsed');
            
            // Store the state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
        }

        // Connect sidebar toggle button
        document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);

        function toggleChat() {
            // Implement your chat widget toggle logic here
            console.log('Toggle chat');
        }

        // Initialize sidebar state
        document.addEventListener('DOMContentLoaded', () => {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.querySelector('#sidebar');
            const mainContent = document.querySelector('#main-content');
            
            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.remove('sidebar-expanded');
                mainContent.classList.add('main-content-collapsed');
                mainContent.classList.remove('main-content-expanded');
            }
            
            // Initialize Alpine.js
            window.Alpine = window.Alpine || {};
            Alpine.start();
        });

        // Notifications component
        function notificationsData() {
            return {
                showNotifications: false,
                notifications: [],
                unreadCount: 0,
                async init() {
                    const db = await initDB();
                    const tx = db.transaction(storeName, 'readonly');
                    const store = tx.objectStore(storeName);
                    this.notifications = await store.index('timestamp').getAll();
                    this.notifications.reverse();
                    this.updateUnreadCount();
                },
                async handleNewNotification(notification) {
                    await saveNotification(notification);
                    this.notifications.unshift({
                        ...notification,
                        timestamp: new Date().toISOString(),
                        read: false
                    });
                    this.updateUnreadCount();
                },
                async markRead(id) {
                    await markAsRead(id);
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification) {
                        notification.read = true;
                        this.updateUnreadCount();
                    }
                },
                async updateUnreadCount() {
                    this.unreadCount = await getUnreadCount();
                },
                formatDate(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diff = now - date;
                    
                    if (diff < 60000) return 'Just now';
                    if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`;
                    if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`;
                    if (diff < 604800000) return `${Math.floor(diff/86400000)}d ago`;
                    return date.toLocaleDateString();
                }
            }
        }
    </script>

    <!-- Add Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Add this JavaScript before the closing </body> tag -->
    <script>
    async function handleCheckIn() {
        try {
            // Show loading alert
            Swal.fire({
                title: 'Checking in...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch('/student/check_in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update UI
                const button = document.getElementById('checkInButton');
                button.parentElement.innerHTML = '<p class="text-success font-medium">Checked In!</p>';
                
                // Update streak display
                const streakElement = document.querySelector('[data-streak]');
                if (streakElement) {
                    streakElement.textContent = `${data.data.check_in_streak} days`;
                }
                
                // Show success alert
                await Swal.fire({
                    icon: 'success',
                    title: 'Checked In Successfully!',
                    text: `You're on a ${data.data.check_in_streak} day streak! Keep it up! 🎉`,
                    confirmButtonColor: '#4B49AC',
                    timer: 3000,
                    timerProgressBar: true
                });

                // Optionally reload the page to refresh all stats
                location.reload();
            } else {
                // Show error alert
                await Swal.fire({
                    icon: 'error',
                    title: 'Check-in Failed',
                    text: 'Unable to process your check-in. Please try again.',
                    confirmButtonColor: '#4B49AC'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            
            // Show error alert
            await Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again later.',
                confirmButtonColor: '#4B49AC'
            });
        }
    }

    // Add this to show a welcome back message with streak info when page loads
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($hasCheckedInToday): ?>
        Swal.fire({
            icon: 'info',
            title: 'Welcome Back!',
            text: `Current streak: ${<?php echo $stats['check_in_streak']; ?>} days`,
            confirmButtonColor: '#4B49AC',
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
        <?php endif; ?>
    });

    async function viewCheckInHistory() {
        try {
            const response = await fetch('/student/check_in_history');
            const data = await response.json();
            
            if (data.success) {
                // Create HTML for check-in history
                const historyHTML = data.history.map(checkin => {
                    const date = new Date(checkin.check_in_date).toLocaleDateString();
                    return `<div class="flex justify-between py-2 border-b">
                        <span>${date}</span>
                        <span class="text-success">✓</span>
                    </div>`;
                }).join('');
                
                Swal.fire({
                    title: 'Check-in History',
                    html: `
                        <div class="max-h-96 overflow-y-auto">
                            <div class="text-left">
                                <p class="mb-4">Total Check-ins: ${data.data.total_check_ins}</p>
                                <p class="mb-4">Current Streak: ${data.data.check_in_streak} days</p>
                                <div class="divide-y">${historyHTML}</div>
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#4B49AC',
                    width: '400px'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Unable to load check-in history.',
                confirmButtonColor: '#4B49AC'
            });
        }
    }
    </script>
</body>
</html>