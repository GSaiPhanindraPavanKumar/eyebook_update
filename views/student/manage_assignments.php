<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Manage Assignments</h3>
                    </div>
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
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Submitted</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assignments)): ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <?php
                                            $is_overdue = !$assignment['is_submitted'] && (new DateTime($assignment['due_date']) < new DateTime());
                                            $row_class = $is_overdue ? 'table-danger' : '';
                                        ?>
                                        <tr class="<?php echo $row_class; ?>">
                                            <td><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['course_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['due_date'] ?? ''); ?></td>
                                            <td><?php echo $assignment['is_submitted'] ? 'Submitted' : 'Not Submitted'; ?></td>
                                            <td><?php echo htmlspecialchars($assignment['grade'] ?? 'Not Graded'); ?></td>
                                            <td>
                                                <a href="/student/view_assignment/<?php echo $assignment['id']; ?>" class="btn btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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