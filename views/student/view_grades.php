<?php include('sidebar-content.php'); ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                View Grades
            </h1>
        </div>

        <!-- Grades Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Assignment Title
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Grade
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Feedback
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                            <?php if (!empty($grades)): ?>
                                <?php foreach ($grades as $grade): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($grade['title']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                $gradeValue = intval($grade['grade']);
                                                if ($gradeValue >= 90) {
                                                    echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                } elseif ($gradeValue >= 70) {
                                                    echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                                } elseif ($gradeValue >= 50) {
                                                    echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                } else {
                                                    echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($grade['grade']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <?php 
                                            if (!empty($grade['feedback'])) {
                                                echo htmlspecialchars($grade['feedback']);
                                            } else {
                                                echo '<span class="text-gray-400 dark:text-gray-500">No feedback provided</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No grades available.
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