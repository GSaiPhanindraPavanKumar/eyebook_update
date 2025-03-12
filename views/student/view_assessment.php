<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Assessment Details</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['duration']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <h5 class="mt-4">Questions</h5>
                        <pre><?php echo htmlspecialchars(json_encode(json_decode($assessment['questions']), JSON_PRETTY_PRINT)); ?></pre>
                        <h5 class="mt-4">Submissions</h5>
                        <pre><?php echo htmlspecialchars(json_encode(json_decode($assessment['submissions']), JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>