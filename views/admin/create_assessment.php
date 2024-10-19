<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $questions = json_decode($_POST['questions'], true);
    $deadline = $_POST['deadline'];

    try {
        createAssessment($title, $questions, $deadline);
        $success = "Assessment created successfully!";
    } catch (Exception $e) {
        $error = "Error creating assessment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mx-auto p-4">
        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Create Assessment</h2>
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <form id="assessmentForm" method="POST">
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Assessment Title
                        </label>
                        <input type="text" id="title" name="title" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-700">AI-Powered Question Generation</h3>
                        <div class="space-y-2 mb-2">
                            <input type="text" id="topic" placeholder="Enter topic for questions" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Questions</label>
                                    <input type="number" id="numQuestions" value="1" min="1" max="10" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marks per Question</label>
                                    <input type="number" id="marksPerQuestion" value="1" min="1" max="10" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        <button type="button" id="generateQuestions" class="w-full bg-purple-600 text-white p-2 rounded-md hover:bg-purple-700 transition duration-300">
                            Generate Questions
                        </button>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-700">Questions</h3>
                        <div id="questionsContainer"></div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <button type="button" id="addQuestion" class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 transition duration-300">
                                Add Question
                            </button>
                            <div class="text-gray-600">
                                <span class="mr-4">Total Questions: <span id="totalQuestions">0</span></span>
                                <span>Total Marks: <span id="totalMarks">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="deadline">
                            Deadline
                        </label>
                        <input type="datetime-local" id="deadline" name="deadline" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <input type="hidden" id="questionsData" name="questions">
                    <button type="submit" id="submitForm" class="w-full bg-green-500 text-white p-3 rounded-md hover:bg-green-600 transition duration-300">
                        Create Assessment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/assessment.js"></script>
</body>
</html>
