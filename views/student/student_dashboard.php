<?php
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../models/zoom_integration.php';

use Models\Database;
use Models\Student;
use Models\Course;
use Models\VirtualClassroom;

$conn = Database::getConnection();

// Initialize current time
$current_time = new DateTime('now', new DateTimeZone('UTC'));

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

// Fetch student ID from session
$studentId = $_SESSION['student_id'];

// Step 1: Get the assigned course IDs from the students table
$sql = "SELECT assigned_courses FROM students WHERE id = :student_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['student_id' => $studentId]);
$assignedCourses = $stmt->fetchColumn();
$assignedCourses = $assignedCourses ? json_decode($assignedCourses, true) : [];

if (empty($assignedCourses)) {
    $assignedCourses = [];
}

// Step 2: Get the virtual class IDs from the courses table
$virtualClassIds = [];
if (!empty($assignedCourses)) {
    $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
    $sql = "SELECT virtual_class_id FROM courses WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($assignedCourses);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids = !empty($row['virtual_class_id']) ? json_decode($row['virtual_class_id'], true) : [];
        if (is_array($ids)) {
            $virtualClassIds = array_merge($virtualClassIds, $ids);
        }
    }
    $virtualClassIds = array_unique($virtualClassIds);
}

// Step 3: Fetch the virtual class details from the virtual classrooms table using the id column
$allClassrooms = [];
if (!empty($virtualClassIds)) {
    $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
    $sql = "SELECT * FROM virtual_classrooms WHERE id IN ($placeholders) ORDER BY start_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($virtualClassIds);
    $allClassrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch attendance data for the student from virtual_classrooms table
$attendanceStatus = [];
$totalClasses = 0;
$attendedClasses = 0;

foreach ($allClassrooms as $classroom) {
    if (!empty($classroom['attendance'])) {
        $attendance = json_decode($classroom['attendance'], true);
        if (isset($attendance[$studentId])) {
            $attendanceStatus[$classroom['id']] = $attendance[$studentId];
            if ($attendance[$studentId] === 'present') {
                $attendedClasses++;
            }
        } else {
            $attendanceStatus[$classroom['id']] = 'Absent';
        }
        $totalClasses++;
    }
}

// Calculate attendance percentage based on assigned classes
$attendancePercentage = $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0;
?>

<?php include "sidebar-content.php"; ?>

<!-- Main Content -->
<div class="main-panel ml-64 transition-all duration-300 ease-in-out flex-1">
    <div class="content-wrapper p-8 bg-gray-50 dark:bg-gray-900 min-h-screen mt-16">
        <!-- Attendance Overview Card -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-semibold text-gray-800 dark:text-white">Attendance Overview</h4>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-300">Overall Attendance:</span>
                            <span class="text-lg font-semibold <?php echo $attendancePercentage >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                <?php echo round($attendancePercentage, 2); ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's and Upcoming Classes -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-6">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Today's and Upcoming Classes</h4>
                    <div class="overflow-x-auto">
                        <?php
                        $hasUpcomingClasses = false;
                        foreach ($allClassrooms as $classroom) {
                            $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                            $end_time = clone $start_time;
                            $end_time->modify('+' . $classroom['duration'] . ' minutes');
                            if ($current_time <= $end_time) {
                                $hasUpcomingClasses = true;
                                break;
                            }
                        }
                        
                        if ($hasUpcomingClasses): ?>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Topic</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Join URL</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php
                                    foreach ($allClassrooms as $classroom):
                                        $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                        $end_time = clone $start_time;
                                        $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                        if ($current_time <= $end_time):
                                            $attendance = $attendanceStatus[$classroom['id']] ?? null;
                                    ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($classroom['topic']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($start_time->format('Y-m-d')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($start_time->format('H:i:s')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($end_time->format('H:i:s')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="<?php echo htmlspecialchars($classroom['join_url']); ?>" target="_blank" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                                    Join Class
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($attendance === 'present'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Present
                                                    </span>
                                                <?php elseif ($attendance === 'absent'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Absent
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        Not Uploaded
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No Upcoming Classes</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">There are no classes scheduled for today or the near future.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Past Classes -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-6">
                    <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Past Classes</h4>
                    <div class="overflow-x-auto">
                        <?php
                        $hasPastClasses = false;
                        foreach ($allClassrooms as $classroom) {
                            $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                            $end_time = clone $start_time;
                            $end_time->modify('+' . $classroom['duration'] . ' minutes');
                            if ($current_time > $end_time) {
                                $hasPastClasses = true;
                                break;
                            }
                        }
                        
                        if ($hasPastClasses): ?>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Topic</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php
                                    foreach ($allClassrooms as $classroom):
                                        $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                        $end_time = clone $start_time;
                                        $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                        if ($current_time > $end_time):
                                            $attendance = $attendanceStatus[$classroom['id']] ?? null;
                                    ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($classroom['topic']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($start_time->format('Y-m-d')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($start_time->format('H:i:s')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                <?php echo htmlspecialchars($end_time->format('H:i:s')); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($attendance === 'present'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Present
                                                    </span>
                                                <?php elseif ($attendance === 'absent'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Absent
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        Not Uploaded
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12a8 8 0 11-16 0 8 8 0 0116 0z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No Past Classes</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">You haven't attended any classes yet. Your class history will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
