<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Manage Assessments</h4>
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