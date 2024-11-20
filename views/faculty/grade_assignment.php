<?php include('sidebar.php'); ?>

<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Grade Assignment</h1>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Submission</h2>
        <p><strong>Student:</strong> <?= htmlspecialchars($submission['student_name']) ?></p>
        <p><strong>File:</strong> <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="text-blue-500 hover:underline">Download</a></p>
        <form method="POST" action="/faculty/grade_assignment/<?= $submission['assignment_id'] ?>/<?= $submission['student_id'] ?>">
            <div class="mb-4">
                <label for="grade" class="block text-sm font-medium text-gray-700">Grade</label>
                <input type="number" id="grade" name="grade" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Submit Grade</button>
        </form>
        <div class="mt-4">
            <a href="/faculty/manage_assignments" class="text-blue-500 hover:underline">Back to Assignments</a>
        </div>
    </div>
</div>

<?php include('footer.html'); ?>