<?php
include('sidebar.php');
require_once 'functions.php';

// Dummy data for assessments
$assessments = [
    [
        'id' => 1,
        'title' => 'Deep Learning',
        'deadline' => '2024-11-21 23:59:59'
    ],
    [
        'id' => 2,
        'title' => 'Sample',
        'deadline' => '2024-11-21 23:59:59'
    ],
    [
        'id' => 3,
        'title' => 'Class Test',
        'deadline' => '2024-11-21 23:59:59'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Manage Assessments</h1>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Assessment Title</th>
                        <th class="py-2 px-4 border-b">Deadline</th>
                        <th class="py-2 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assessments)): ?>
                        <?php foreach ($assessments as $assessment): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($assessment['title']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($assessment['deadline']) ?></td>
                                <td class="py-2 px-4 border-b">
                                    <a href="view_assessment_report/<?= $assessment['id'] ?>" class="text-blue-500 hover:underline">View Report</a>
                                    <a href="download_assessment_report/<?= $assessment['id'] ?>" class="text-green-500 hover:underline ml-4">Download Report</a>
                                    <!-- <a href="download_assessment_report_excel.php?id=<?= $assessment['id'] ?>" class="text-purple-500 hover:underline ml-4">Download Excel</a> -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="py-2 px-4 border-b text-center">No assessments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php include('footer.html'); ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('table');
        
        table.addEventListener('click', function(e) {
            // Find the closest row to the clicked element
            const row = e.target.closest('tr');
            
            // Ensure we have a row and it's not the header row
            if (row && !row.closest('thead')) {
                // If the click was not on a button/link/form
                if (!e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
                    // Find the view button in this row and get its href
                    const viewButton = row.querySelector('a[href*="view_assessment_report"]');
                    if (viewButton) {
                        window.location.href = viewButton.href;
                    }
                }
            }
        });
    });
    </script>

    <style>
    tbody tr {
        cursor: pointer;
    }

    tbody tr:hover {
        background-color: rgba(0,0,0,0.05) !important;
    }

    tbody tr a,
    tbody tr button,
    tbody tr input {
        position: relative;
        z-index: 2;
    }
    </style>

</body>
</html>