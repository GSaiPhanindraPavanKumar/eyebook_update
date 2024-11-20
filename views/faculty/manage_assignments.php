<?php include('sidebar.php'); ?>

<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Manage Assignments</h1>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <input type="text" id="search" placeholder="Search assignments..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">Title</th>
                    <th class="py-2 px-4 border-b">Course</th>
                    <th class="py-2 px-4 border-b">Deadline</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody id="assignmentTable">
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['title']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['course_name']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['deadline']) ?></td>
                        <td class="py-2 px-4 border-b">
                            <a href="/faculty/grade_assignment/<?= $assignment['id'] ?>" class="text-blue-500 hover:underline">Grade</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-4">
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Previous</button>
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Next</button>
        </div>
    </div>
</div>

<?php include('footer.html'); ?>