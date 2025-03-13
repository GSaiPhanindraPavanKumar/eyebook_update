<?php include "sidebar-content.php";
use Models\Lab;
use Models\Database;

// Get student ID and database connection
$studentId = $_SESSION['student_id'];
$conn = Database::getConnection();

// Fetch all labs with course details
$sql = "SELECT l.*, c.name as course_name 
        FROM labs l 
        JOIN courses c ON l.course_id = c.id 
        WHERE l.status = 'active'
        ORDER BY l.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student's submissions
$sql = "SELECT * FROM lab_submissions WHERE student_id = :student_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['student_id' => $studentId]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create submissions lookup
$submissionStatus = [];
foreach ($submissions as $submission) {
    $submissionStatus[$submission['lab_id']] = $submission;
}
?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Lab Assignments
            </h1>
        </div>

        <!-- Labs Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Course
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Lab Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Due Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                            <?php if (!empty($labs)): ?>
                                <?php foreach ($labs as $lab): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($lab['course_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($lab['lab_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($lab['description']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo $lab['due_date'] ? date('Y-m-d H:i', strtotime($lab['due_date'])) : 'N/A'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            if (isset($submissionStatus[$lab['id']])) {
                                                $status = $submissionStatus[$lab['id']]['status'];
                                                $statusClasses = match($status) {
                                                    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                    default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                };
                                                echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$statusClasses}'>" . 
                                                     htmlspecialchars($status) . "</span>";
                                            } else {
                                                echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="/views/student/i-Lab/index.html" 
                                               class="inline-flex px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                                Open Lab
                                            </a>
                                            <?php if ($lab['file_path']): ?>
                                                <a href="<?php echo htmlspecialchars($lab['file_path']); ?>" 
                                                   class="inline-flex px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                                                    Download
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No labs available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>