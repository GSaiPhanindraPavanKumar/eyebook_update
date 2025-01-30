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
                        <a href="/admin/edit_question/<?php echo $question['id']; ?>" class="btn btn-warning">Edit Question</a>
                        <a href="/admin/delete_question/<?php echo $question['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this question?');">Delete Question</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Submissions</h3>
                <div class="card">
                    <div class="card-body">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th class="col-student-name">Student Name</th>
                                    <th class="col-submission-date">Submission Date</th>
                                    <th class="col-runtime">Runtime</th>
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

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-student-name { width: 40%; }
    .col-submission-date { width: 30%; }
    .col-runtime { width: 30%; }
    @media (max-width: 768px) {
        .col-student-name, .col-submission-date, .col-runtime {
            width: auto;
        }
    }
</style>