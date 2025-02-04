<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Lab Details</h3>
                    <a href="/admin/download_lab_report/<?php echo $lab['id']; ?>" class="btn btn-success">Download Report</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lab['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($lab['description']); ?></p>
                        <p><strong>Input:</strong> <?php echo htmlspecialchars($lab['input']); ?></p>
                        <p><strong>Output:</strong> <?php echo htmlspecialchars($lab['output']); ?></p>
                        <h5>Submissions</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Runtime</th>
                                    <th>Submission Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lab['submissions'])): ?>
                                    <?php foreach ($lab['submissions'] as $submission): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['runtime']); ?></td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($submission['submission_date']);
                                                echo $date->format('Y-m-d H:i:s');
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No submissions found for this lab.</td>
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

<!-- Include Bootstrap CSS -->
