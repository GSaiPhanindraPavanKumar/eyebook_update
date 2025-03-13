<?php include "sidebar-content.php"; ?>
<?php
// Get completed assessments for the current student
$stmt = $conn->prepare("SELECT assessment_id, score FROM assessment_results WHERE student_email = ?");
$stmt->execute([$_SESSION['email']]);
$completedAssessments = [];
while ($row = $stmt->fetch()) {
    $completedAssessments[$row['assessment_id']] = $row['score'];
}
?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">View Assessments</h1>
            </div>
        </div>

        <!-- Assessments Table -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Start Time</th>
                            <th class="px-4 py-3">End Time</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php foreach ($assessments as $assessment): 
                            $currentTime = new DateTime();
                            $startTime = new DateTime($assessment['start_time']);
                            $endTime = new DateTime($assessment['end_time']);
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($assessment['title'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo htmlspecialchars($assessment['start_time'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo htmlspecialchars($assessment['end_time'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo htmlspecialchars($assessment['duration'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (isset($completedAssessments[$assessment['id']])): ?>
                                        <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                            Completed (<?php echo $completedAssessments[$assessment['id']]; ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-700 dark:text-yellow-100">
                                            Not Attempted
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (isset($completedAssessments[$assessment['id']])): ?>
                                        <button type="button" 
                                                onclick="showCompletedMessage(<?php echo $completedAssessments[$assessment['id']]; ?>)"
                                                class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                            View Result
                                        </button>
                                    <?php elseif ($currentTime >= $startTime && $currentTime <= $endTime): ?>
                                        <a href="/student/view_assessment/<?php echo $assessment['id']; ?>" 
                                           class="px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                            Start Assessment
                                        </a>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-md dark:bg-gray-700 dark:text-gray-300">
                                            Not Available
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showCompletedMessage(score) {
    Swal.fire({
        title: 'Assessment Already Completed',
        html: `You have already completed this assessment.<br>Your score: ${score}%`,
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#4B49AC'
    });
}

// Show error message if it exists
<?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
        title: 'Notice',
        text: '<?php echo $_SESSION['error']; ?>',
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#4B49AC'
    });
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
</script>