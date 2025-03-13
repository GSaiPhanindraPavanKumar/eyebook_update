<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Manage Contests</h1>
            </div>
        </div>

        <!-- Contests Table -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Start Date</th>
                            <th class="px-4 py-3">End Date</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php if (!empty($contests)): ?>
                            <?php foreach ($contests as $contest): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($contest['title']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($contest['description']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($contest['start_date']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <?php echo htmlspecialchars($contest['end_date']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="/student/view_contest/<?php echo $contest['id']; ?>" 
                                           class="px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No contests found.
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
                    const viewButton = row.querySelector('a[href*="view_contest"]');
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