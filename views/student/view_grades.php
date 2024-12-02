<?php include('sidebar.php'); ?>
<div class="container">
    <h1>View Grades</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Assignment Title</th>
                <th>Grade</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><?php echo htmlspecialchars($grade['title']); ?></td>
                    <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                    <td><?php echo htmlspecialchars($grade['feedback']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>