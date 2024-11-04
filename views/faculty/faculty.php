<?php

include 'sidebar.php';
include '../../src/functions.php';

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Handle form submission for creating an assessment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_assessment') {
    $title = $_POST['title'];
    $deadline = $_POST['deadline'];
    $questionsJson = isset($_POST['questions']) ? $_POST['questions'] : '[]';
    $questions = json_decode($questionsJson, true);

    // Ensure $questions is an array
    if (!is_array($questions)) {
        $error = "Invalid questions format.";
    } else {
        try {
            createAssessment($title, $questions, $deadline);
            $success = "Assessment created successfully!";
        } catch (Exception $e) {
            $error = "Error creating assessment: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Faculty Dashboard</h1>
        <div class="mb-4">
            <a href="faculty.php?action=create_assessment" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Create Assessment</a>
            <a href="faculty.php?action=manage_assessments" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">Manage Assessments</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'create_assessment'): ?>
            <!-- Create Assessment Form -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4">Create Assessment</h2>
                <form id="assessmentForm" method="POST" action="faculty.php?action=create_assessment">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700">Title</label>
                        <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="deadline" class="block text-gray-700">Deadline</label>
                        <input type="date" id="deadline" name="deadline" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="questions" class="block text-gray-700">Questions (JSON format)</label>
                        <textarea id="questions" name="questions" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        <small>Example: [{"questionText": "What is 2+2?", "options": ["3", "4", "5"], "correctAnswer": "4", "marks": 5}]</small>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Assessment</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>
</body>
</html>