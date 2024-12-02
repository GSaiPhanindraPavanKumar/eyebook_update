<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Manage Assignments</h3>
                    </div>
                    <!-- <div class="col-12 col-xl-6 text-right">
                        <a href="/faculty/create_assignment" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Assignment
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
            <div class="col-md-12 grid-margin stretch-card"><br>
                <div class="card">

                    <!-- <a href="/faculty/create_assignment" class="btn btn-primary">Create Assignment</a> -->
                    <div class="card-body" style="text-align: center;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                        <td>
                                            <a href="/faculty/view_assignment/<?php echo $assignment['id']; ?>" class="btn btn-info">View</a>
                                            <!-- <a href="/faculty/download_report/<?php echo $assignment['id']; ?>/pdf" class="btn btn-secondary">Preview PDF</a> -->
                                            <a href="/faculty/download_report/<?php echo $assignment['id']; ?>/excel" class="btn btn-secondary">Download Excel</a>
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
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">