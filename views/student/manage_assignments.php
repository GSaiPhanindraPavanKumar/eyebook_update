<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Manage Assignments</h1>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3">Due Date</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php if (!empty($assignments)): ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <?php
                                    $is_overdue = !$assignment['is_submitted'] && (new DateTime($assignment['due_date']) < new DateTime());
                                    $row_class = $is_overdue ? 'bg-red-50 dark:bg-red-900/20' : '';
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors <?php echo $row_class; ?>">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($assignment['title'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($assignment['course_name'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($assignment['due_date'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($assignment['is_submitted']): ?>
                                            <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                                Submitted
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-700 dark:text-yellow-100">
                                                Not Submitted
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($assignment['grade'] ?? 'Not Graded'); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="/student/view_assignment/<?php echo $assignment['id']; ?>" 
                                           class="px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No assignments found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    
    if (table) {
        table.addEventListener('click', function(e) {
            const row = e.target.closest('tr');
            
            if (row && !row.closest('thead')) {
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    const viewButton = row.querySelector('a[href*="view_assignment"]');
                    if (viewButton) {
                        window.location.href = viewButton.href;
                    }
                }
            }
        });
    }
});
</script>

<style>
/* Cursor pointer for clickable rows */
tbody tr {
    cursor: pointer;
}

/* Ensure buttons and links stay above the row click handler */
tbody tr a,
tbody tr button {
    position: relative;
    z-index: 2;
}
</style>