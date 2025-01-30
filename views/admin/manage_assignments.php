<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Manage Assignments</h3>
                    <a href="/admin/create_assignment" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Assignment
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card"><br>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Title</th>
                                    <th style="width: 20%;">Course</th>
                                    <th style="width: 15%;">Start Time</th>
                                    <th style="width: 15%;">Due Date</th>
                                    <th style="width: 15%;">Submissions</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php error_log(print_r($assignments,true))?>
                                <?php error_log(print_r(count($assignments))) ?>
                                <?php if (!empty($assignments)): ?>
                                    <?php for($i = 0; $i < count($assignments); $i++): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignments[$i]['title']); ?></td>
                                            <td><?php echo htmlspecialchars($assignments[$i]['course_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assignments[$i]['start_time']); ?></td>
                                            <td><?php echo htmlspecialchars($assignments[$i]['due_date']); ?></td>
                                            <td><?php echo htmlspecialchars($assignments[$i]['submission_count']); ?></td>
                                            <td>
                                                <a href="/admin/view_assignment/<?php echo $assignments[$i]['id']; ?>" class="btn btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">No assignments found.</td>
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