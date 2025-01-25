<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">View Assignment</h3>
                    <a href="/faculty/view_course/<?php echo str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)); ?>" class="btn btn-secondary">Back to Course</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                        <p><strong>Start Time:</strong> <?php echo htmlspecialchars($assignment['start_time']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                        <p><strong>File Content:</strong></p>
                        <?php if (!empty($assignment['file_content'])): ?>
                            <button id="viewButton" class="btn btn-info mb-3" onclick="toggleFileContent()">View File</button>
                            <div id="fileContent" style="display: none; margin-top: 20px;">
                                <embed src="data:application/pdf;base64,<?php echo base64_encode($assignment['file_content']); ?>" type="application/pdf" width="100%" height="600px" />
                            </div>
                        <?php else: ?>
                            <p>No file attached.</p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Submissions</h5>
                            <a href="/faculty/download_report/<?php echo $assignment['id']; ?>" class="btn btn-primary">Download Grades</a>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Grade</th>
                                    <th>Submission Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($submissions)): ?>
                                    <?php foreach ($submissions as $submission): ?>
                                        <?php $isLate = isset($submission['date_of_submit']) && strtotime($submission['date_of_submit']) > strtotime($assignment['due_date']); ?>
                                        <tr style="color: <?php echo $isLate ? 'red' : 'inherit'; ?>">
                                            <td><?php echo htmlspecialchars($submission['name']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['email']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['grade'] ?? 'Not Graded'); ?></td>
                                            <td><?php echo htmlspecialchars($submission['date_of_submit'] ?? ''); ?></td>
                                            <td>
                                                <a href="/faculty/grade_submission/<?php echo $assignment['id']; ?>/<?php echo $submission['student_id']; ?>" class="btn btn-primary">Grade</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No submissions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
function toggleFileContent() {
    var fileContent = document.getElementById('fileContent');
    var viewButton = document.getElementById('viewButton');
    if (fileContent.style.display === 'none') {
        fileContent.style.display = 'block';
        viewButton.textContent = 'Hide File';
    } else {
        fileContent.style.display = 'none';
        viewButton.textContent = 'View File';
    }
}
</script>