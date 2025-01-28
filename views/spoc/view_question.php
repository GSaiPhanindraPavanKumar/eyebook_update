<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Question Details</h3>
                <div class="card">
                    <div class="card-body">
                        <p><strong>Question:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($question['description']); ?></p>
                        <p><strong>Input:</strong> <?php echo htmlspecialchars($question['input']); ?></p>
                        <p><strong>Output:</strong> <?php echo htmlspecialchars($question['output']); ?></p>
                        <p><strong>Grade:</strong> <?php echo htmlspecialchars($question['grade']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Submissions</h3>
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Submission Date</th>
                                    <th>Runtime</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $submissions = !empty($question['submissions']) ? $question['submissions'] : [];
                                if (!empty($submissions)): 
                                    foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['runtime']); ?></td>
                                        </tr>
                                    <?php endforeach; 
                                else: ?>
                                    <tr>
                                        <td colspan="3">No submissions found for this question.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>