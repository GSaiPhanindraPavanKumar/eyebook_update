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
    <title>Submit Assignment - Knowbots</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <?php include('sidebar.php'); ?>

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

        <!-- Main Content Area -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Submit Assignment</h1>

                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                        <!-- Success/Error Messages -->
                        <div class="mb-6">
                            <?php
                            if (isset($success)): ?>
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($error)): ?>
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Assignment Form -->
                    <form action="assigments.php" method="post" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="assignment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Choose an assignment
                            </label>
                            <select name="assignment" id="assignment" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="assignment1">Assignment 1</option>
                                <option value="assignment2">Assignment 2</option>
                                <option value="assignment3">Assignment 3</option>
                            </select>
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Upload your file
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md dark:border-gray-600">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                        <label for="file" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-primary hover:text-primary-hover focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                            <span>Upload a file</span>
                                            <input id="file" name="file" type="file" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        PDF, DOC, DOCX up to 10MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Submit Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot Button -->
    <a href="/student/askguru" class="fixed bottom-6 right-6 bg-primary hover:bg-primary-hover text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </a>

    <!-- Include the IndexedDB script for notifications -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // IndexedDB setup and notification handling (same as my_courses.php)
        const dbName = 'notificationsDB';
        const storeName = 'notifications';
        let db;

        // ... (copy the rest of the IndexedDB and notification handling code from my_courses.php)
    </script>
</body>
</html>