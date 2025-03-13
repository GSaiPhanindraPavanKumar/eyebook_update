<?php
include 'sidebar-content.php';
use Models\Database;
use Models\PublicCourse;

$conn = Database::getConnection();
if (!isset($_SESSION['student_id'])) {
    die('Student ID not set in session.');
}
$studentId = $_SESSION['student_id'];

// Fetch enrolled courses
$enrolledCourses = PublicCourse::getEnrolledCourses($conn, $studentId);

// Fetch featured courses
$featuredCourses = PublicCourse::getFeaturedCourses($conn);

// Create an array of enrolled course IDs for easy lookup
$enrolledCourseIds = array_map(function($course) {
    return $course['id'];
}, $enrolledCourses);

function custom_base64_encode($data) {
    $encoded = base64_encode($data);
    return str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
}
?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900/20 dark:border-red-600 dark:text-red-400" role="alert">
                <div class="flex items-center justify-between">
                    <span><?php echo $_SESSION['error']; ?></span>
                    <button type="button" class="text-red-500 hover:text-red-600 focus:outline-none" onclick="this.parentElement.parentElement.style.display='none'">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Manage Public Courses</h1>
            </div>
        </div>

        <!-- Enrolled Courses Section -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Enrolled Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($enrolledCourses)): ?>
                    <p class="text-gray-500 dark:text-gray-400">You are not enrolled in any courses.</p>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    <?php echo htmlspecialchars($course['description']); ?>
                                </p>
                                <?php $hashedId = custom_base64_encode($course['id']); ?>
                                <div class="flex space-x-3">
                                    <a href="/student/view_public_course/<?php echo $hashedId; ?>" 
                                       class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                        View Course
                                    </a>
                                    <a href="/student/view_public_lab/<?php echo $hashedId; ?>" 
                                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                        View Lab
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Featured Courses Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Featured Courses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($featuredCourses)): ?>
                    <p class="text-gray-500 dark:text-gray-400">No featured courses available.</p>
                <?php else: ?>
                    <?php foreach ($featuredCourses as $course): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-2">
                                    <?php echo htmlspecialchars($course['description']); ?>
                                </p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Price: Rs<?php echo htmlspecialchars($course['price']); ?>
                                </p>
                                <?php if (!in_array($course['id'], $enrolledCourseIds)): ?>
                                    <?php if ($course['price'] > 0): ?>
                                        <form action="/student/pay_for_course" method="POST">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $course['price']; ?>">
                                            <button type="submit" 
                                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-success hover:bg-success/90 rounded-md transition-colors">
                                                Enroll
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="/student/enroll_in_course" method="POST">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <button type="submit" 
                                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-success hover:bg-success/90 rounded-md transition-colors">
                                                Enroll
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button disabled 
                                            class="w-full px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed dark:bg-gray-700 dark:text-gray-400">
                                        Enrolled
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>