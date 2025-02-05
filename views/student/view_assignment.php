<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">View Assignment</h3>
                    <a href="/student/view_course/<?php echo str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)); ?>" class="btn btn-secondary">Back to Course</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                        <p><strong>Start Time:</strong> <?php echo htmlspecialchars($assignment['start_time']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>

                        <?php
                        $current_time = new DateTime();
                        $current_time->modify('+5 hours +30 minutes'); // Adjust for -5 hours 30 minutes offset
                        $start_time = new DateTime($assignment['start_time']);
                        if ($current_time < $start_time): ?>
                            <p><strong>Status:</strong> The assignment has not yet started.</p>
                        <?php else: ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                            <p><strong>File Content:</strong></p>
                            <?php if (!empty($assignment['file_content'])): ?>
                                <button id="viewButton" class="btn btn-info mb-3" onclick="toggleFileContent()">View File</button>
                                <div id="fileContent" style="display: none; margin-top: 20px;">
                                    <embed src="data:application/pdf;base64,<?php echo base64_encode($assignment['file_content']); ?>" type="application/pdf" width="100%" height="600px" />
                                </div>
                            <?php else: ?>
                                <p>No file attached.</p>
                            <?php endif; ?>

                            <?php if (!empty($student_submission)): ?>
                                <h5 class="mt-4">Your Submission</h5>
                                <button id="viewSubmissionButton" class="btn btn-info mb-3" onclick="toggleSubmissionContent()">View Submission</button>
                                <div id="submissionContent" style="display: none; margin-top: 20px;">
                                    <embed src="data:application/pdf;base64,<?php echo $student_submission['file']; ?>" type="application/pdf" width="100%" height="600px" />
                                </div>
                                <?php if (empty($student_submission['grade']) && empty($student_submission['feedback'])): ?>
                                    <form action="/student/delete_submission/<?php echo $assignment['id']; ?>" method="post" style="margin-top: 20px;">
                                        <button type="submit" class="btn btn-danger">Delete Submission</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!empty($student_submission['grade']) || !empty($student_submission['feedback'])): ?>
                                    <h5 class="mt-4">Grading</h5>
                                    <p><strong>Grade:</strong> <?php echo htmlspecialchars($student_submission['grade']); ?></p>
                                    <p><strong>Feedback:</strong> <?php echo htmlspecialchars($student_submission['feedback']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <form action="/student/submit_assignment/<?php echo $assignment['id']; ?>" method="post" enctype="multipart/form-data" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="submission_file">Upload Submission (PDF only):</label>
                                        <input type="file" class="form-control" id="submission_file" name="submission_file" accept="application/pdf" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            <?php endif; ?>
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

function toggleSubmissionContent() {
    var submissionContent = document.getElementById('submissionContent');
    var viewSubmissionButton = document.getElementById('viewSubmissionButton');
    if (submissionContent.style.display === 'none') {
        submissionContent.style.display = 'block';
        viewSubmissionButton.textContent = 'Hide Submission';
    } else {
        submissionContent.style.display = 'none';
        viewSubmissionButton.textContent = 'View Submission';
    }
}
</script>