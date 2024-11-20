<?php
require_once 'functions.php';

// Dummy data for assessment results
$results = [
    ['student_name' => 'Sumanth', 'score' => 85],
    ['student_name' => 'Ravi Ram', 'score' => 90],
    ['student_name' => 'Phani', 'score' => 78]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Assessment Report</h1>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Student Name</th>
                        <th class="py-2 px-4 border-b">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($result['student_name']) ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($result['score']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>