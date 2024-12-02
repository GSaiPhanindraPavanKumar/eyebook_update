<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">View Assignment</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <h2 class="font-weight-bold"><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></h2>
                        <p><strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($assignment['instructions'] ?? '')); ?></p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date'] ?? ''); ?></p>
                        <h3 class="font-weight-bold">Submissions</h3>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>File</th>
                                <th>Grade</th>
                                <th>Feedback</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['student_name'] ?? ''); ?></td>
                                    <td>
                                        <?php if (!empty($submission['file_content'])): ?>
                                            <a href="data:application/pdf;base64,<?php echo base64_encode($submission['file_content']); ?>" download="submission_<?php echo $submission['student_id']; ?>.pdf" class="btn btn-outline-primary btn-sm">Download File</a>
                                        <?php else: ?>
                                            <span class="text-danger">Not Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($submission['grade'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></td>
                                    <td>
                                        <a href="/faculty/grade_assignment/<?php echo $assignment['id']; ?>/<?php echo $submission['student_id']; ?>" class="btn btn-primary btn-sm">Grade</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">