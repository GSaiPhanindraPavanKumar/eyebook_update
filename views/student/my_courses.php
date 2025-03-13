<?php
use Models\Database;
use Models\Student;
use Models\Course;
use Models\Notification;

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = Database::getConnection();
if (!isset($_SESSION['student_id'])) {
    die('Student ID not set in session.');
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
    $profileImageUrl = !empty($userData['profile_image_url']) && filter_var($userData['profile_image_url'], FILTER_VALIDATE_URL) ? 
        htmlspecialchars($userData['profile_image_url']) : 
        'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=4B49AC&color=fff';
} else {
    header("Location: /logout");
    exit;
}

// Fetch the student's data
$student = Student::getById($conn, $studentId);
$studentUniversityId = $student['university_id'];

// Fetch the assigned courses for the student
$assignedCourses = Student::getAssignedCourses($conn, $studentId);

// Fetch courses from the database
$courses = [];
if (!empty($assignedCourses)) {
    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $sql = "SELECT id, name, description, course_book, status FROM courses WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($assignedCourses);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch the student's completed books
$completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

// Calculate progress data for each course
$progressData = [];
foreach ($courses as $course) {
    $courseId = $course['id'];
    $studentCompletedBooks = $completedBooks[$courseId] ?? [];
    $totalBooks = !empty($course['course_book']) ? count(json_decode($course['course_book'], true) ?? []) : 0;
    $progress = $totalBooks > 0 ? (count($studentCompletedBooks) / $totalBooks) * 100 : 0;
    $progressData[$courseId] = $progress;
}

// Separate ongoing and archived courses
$ongoingCourses = array_filter($courses, function($course) {
    return $course['status'] === 'ongoing';
});
$archivedCourses = array_filter($courses, function($course) {
    return $course['status'] === 'archived';
});

// Get notifications
$notifications = array_map(function($notification) {
    return [
        'id' => $notification['id'] ?? '',
        'message' => $notification['message'] ?? '',
        'created_at' => $notification['created_at'] ?? '',
        'link' => $notification['link'] ?? '#',
        'type' => $notification['type'] ?? 'info',
        'is_read' => $notification['is_read'] ?? false
    ];
}, Notification::getByStudentId($conn, $studentId));
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Knowbots</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* Notification count styles */
        .count {
            min-width: 1rem;
            height: 1rem;
            padding: 0 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Notification dropdown styles */
        .notification-dropdown {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Smooth transitions for notifications */
        [data-notification-id] {
            transition: opacity 0.3s ease-out;
        }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                            
                            <a href="/student/my_courses" class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-primary/10 text-primary dark:text-white dark:bg-primary/20 group transition-all duration-300">
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
                        </div>
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
                        <!-- Profile dropdown -->
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

        <!-- Main Content -->
        <div class="flex-1 main-content-expanded" id="main-content">
            <!-- Top Navigation Bar -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-800 shadow">
                <button id="sidebarToggle" class="px-4 text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none md:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex">
                        <button id="sidebarToggleDesktop" class="px-4 text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none hidden md:flex items-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Right side buttons -->
                    <div class="ml-4 flex items-center md:ml-6 space-x-3">
                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 relative">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <?php if (!empty($notifications)): ?>
                                    <span class="count absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">
                                        <?php echo count($notifications); ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            
                            <!-- Notification dropdown -->
                            <div x-show="open" @click.away="open = false" 
                                 class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <?php if (empty($notifications)): ?>
                                            <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                No new notifications
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($notifications as $notification): ?>
                                                <a href="<?php echo htmlspecialchars($notification['link']); ?>" 
                                                   data-notification-id="<?php echo htmlspecialchars($notification['id']); ?>"
                                                   onclick="markNotificationAsRead('<?php echo htmlspecialchars($notification['id']); ?>', this, event)"
                                                   class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($notification['message']); ?>
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <?php echo date('M j, Y, g:i a', strtotime($notification['created_at'])); ?>
                                                    </p>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle -->
                        <button id="themeToggle" class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                            <svg id="darkIcon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg id="lightIcon" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    <!-- Page Header -->
                    <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-6 md:p-8 text-white mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold">My Courses</h1>
                                <p class="mt-2 text-white/80">Manage and track your course progress</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ongoing Courses Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Ongoing Courses</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($ongoingCourses as $course): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                    <div class="p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            <?php echo htmlspecialchars($course['name']); ?>
                                        </h3>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>
                                        
                                        <!-- Progress Bar -->
                                        <div class="mb-4">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    <?php echo round($progressData[$course['id']], 1); ?>%
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-primary rounded-full h-2 transition-all duration-300"
                                                     style="width: <?php echo $progressData[$course['id']]; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-2">
                                            <?php 
                                            $hashedId = base64_encode($course['id']);
                                            $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                            ?>
                                            <a href="view_course/<?php echo $hashedId; ?>" 
                                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-hover transition-colors">
                                                View Course
                                            </a>
                                            <a href="view_lab/<?php echo $hashedId; ?>" 
                                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-primary text-sm font-medium rounded-lg text-primary hover:bg-primary hover:text-white transition-colors">
                                                View Lab
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Archived Courses Section -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Archived Courses</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($archivedCourses as $course): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden opacity-75">
                                    <div class="p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            <?php echo htmlspecialchars($course['name']); ?>
                                        </h3>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>
                                        
                                        <!-- Progress Bar -->
                                        <div class="mb-4">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    <?php echo round($progressData[$course['id']], 1); ?>%
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-gray-500 rounded-full h-2 transition-all duration-300"
                                                     style="width: <?php echo $progressData[$course['id']]; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-2">
                                            <?php 
                                            $hashedId = base64_encode($course['id']);
                                            $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                                            ?>
                                            <a href="view_course/<?php echo $hashedId; ?>" 
                                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-500 hover:bg-gray-600 transition-colors">
                                                View Course
                                            </a>
                                            <a href="view_lab/<?php echo $hashedId; ?>" 
                                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-500 text-sm font-medium rounded-lg text-gray-500 hover:bg-gray-500 hover:text-white transition-colors">
                                                View Lab
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add the sidebar toggle script -->
    <script>
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

        // Connect sidebar toggle buttons
        document.addEventListener('DOMContentLoaded', () => {
            // Add click event listeners to both toggle buttons
            document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
            document.getElementById('sidebarToggleDesktop').addEventListener('click', toggleSidebar);

            // Initialize sidebar state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.querySelector('#sidebar');
            const mainContent = document.querySelector('#main-content');
            
            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.remove('sidebar-expanded');
                mainContent.classList.add('main-content-collapsed');
                mainContent.classList.remove('main-content-expanded');
            }
        });

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
    </script>

    <!-- Add this script before your existing scripts -->
    <script>
    // IndexedDB setup
    const dbName = 'notificationsDB';  // Match the database name from sidebar.php
    const storeName = 'notifications';  // Match the store name from sidebar.php
    let db;

    // Initialize IndexedDB
    async function initDB() {
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
                    const store = db.createObjectStore(storeName, { keyPath: 'key' });
                    store.createIndex('userId', 'userId', { unique: false });
                }
            };
        });
    }

    // Mark notification as read
    async function markNotificationAsRead(notificationId, element, event) {
        event.preventDefault();
        const userId = <?php echo json_encode($studentId); ?>;
        const key = `${userId}_${notificationId}`;
        
        try {
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            await store.put({ 
                key: key,
                userId: userId,
                notificationId: notificationId,
                readAt: new Date().toISOString() 
            });
            
            // Remove the clicked notification with animation
            element.style.opacity = '0';
            setTimeout(() => {
                element.remove();
                
                // Check if there are no more notifications
                const remainingNotifications = document.querySelectorAll('[data-notification-id]');
                if (remainingNotifications.length === 0) {
                    const container = document.querySelector('.max-h-96.overflow-y-auto');
                    if (container) {
                        container.innerHTML = `
                            <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                No new notifications
                            </div>
                        `;
                    }
                }

                // Update notification count
                updateNotificationCount();

                // Navigate to the notification link
                window.location.href = element.href;
            }, 300);
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Check if notification is read
    async function isNotificationRead(notificationId) {
        const userId = <?php echo json_encode($studentId); ?>;
        const key = `${userId}_${notificationId}`;
        
        try {
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const result = await store.get(key);
            return !!result;
        } catch (error) {
            console.error('Error checking notification status:', error);
            return false;
        }
    }

    // Update notification count in the UI
    async function updateNotificationCount() {
        const unreadCount = await getUnreadCount();
        const countElement = document.querySelector('.count');
        if (countElement) {
            if (unreadCount > 0) {
                countElement.style.display = 'flex';
                countElement.textContent = unreadCount;
            } else {
                countElement.style.display = 'none';
            }
        }
    }

    // Get unread notifications count
    async function getUnreadCount() {
        const notifications = <?php echo json_encode($notifications); ?>;
        let unreadCount = notifications.length; // Start with total count
        
        for (const notification of notifications) {
            const isRead = await isNotificationRead(notification.id);
            if (isRead) unreadCount--; // Decrease count only for read notifications
        }
        
        return unreadCount;
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            await initDB();
            
            // Get all notification elements
            const notifications = document.querySelectorAll('[data-notification-id]');
            
            // Only add transition for smooth removal
            notifications.forEach(notification => {
                notification.style.transition = 'opacity 0.3s ease-out';
            });

            // Update notification count
            await updateNotificationCount();
        } catch (error) {
            console.error('Error initializing IndexedDB:', error);
        }
    });
    </script>
</body>
</html>
