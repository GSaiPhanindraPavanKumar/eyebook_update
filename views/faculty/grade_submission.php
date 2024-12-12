<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Grade Submission</h3>
                    <a href="/faculty/view_assignment/<?php echo $assignment['id']; ?>" class="btn btn-secondary">Back to Assignment</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_submission['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student_submission['email']); ?></p>
                        <p><strong>Date of Submission:</strong> <?php echo htmlspecialchars($student_submission['date_of_submit']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p><strong>Submission:</strong></p>
                            <button id="viewButton" class="btn btn-info" onclick="toggleFileContent()">View File</button>
                        </div>
                        <?php if (!empty($student_submission['file'])): ?>
                            <div id="fileContent" style="display: none; margin-top: 20px;">
                                <embed src="data:application/pdf;base64,<?php echo $student_submission['file']; ?>" type="application/pdf" width="100%" height="600px" />
                            </div>
                        <?php else: ?>
                            <p>No submission file attached.</p>
                        <?php endif; ?>

                        <?php if (!empty($student_submission['grade']) || !empty($student_submission['feedback'])): ?>
                            <h5 class="mt-4">Grading</h5>
                            <p><strong>Grade:</strong> <?php echo htmlspecialchars($student_submission['grade']); ?></p>
                            <p><strong>Feedback:</strong> <?php echo htmlspecialchars($student_submission['feedback']); ?></p>
                        <?php else: ?>
                            <form action="/faculty/grade_submission/<?php echo $assignment['id']; ?>/<?php echo $student_submission['student_id']; ?>" method="post" style="margin-top: 20px;">
                                <div class="form-group">
                                    <label for="grade">Grade (/100):</label>
                                    <input type="number" class="form-control" id="grade" name="grade" value="<?php echo htmlspecialchars($student_submission['grade'] ?? ''); ?>" min="0" max="100" required>
                                </div>
                                <div class="form-group">
                                    <label for="feedback">Feedback:</label>
                                    <textarea class="form-control" id="feedback" name="feedback" required><?php echo htmlspecialchars($student_submission['feedback'] ?? ''); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Grade</button>
                            </form>
                        <?php endif; ?>
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