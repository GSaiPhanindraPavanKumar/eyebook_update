<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Manage Assessments</h4>
                        <a href="/faculty/create_assessment" class="btn btn-primary mb-3">Create New Assessment</a>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assessments as $assessment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['start_time']); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['end_time']); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['duration']); ?></td>
                                            <td>
                                                <a href="/faculty/edit_assessment/<?php echo $assessment['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="/faculty/delete_assessment/<?php echo $assessment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this assessment?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>