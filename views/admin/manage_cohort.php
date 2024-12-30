<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Manage Cohorts</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>S.no</th>
                                <th>Cohort Name</th>
                                <th>No. of Students</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serialNumber = 1; ?>
                            <?php foreach ($cohorts as $cohort): ?>
                                <tr>
                                    <td><?php echo $serialNumber++; ?></td>
                                    <td><?php echo htmlspecialchars($cohort['name']); ?></td>
                                    <td><?php echo htmlspecialchars($cohort['student_count']); ?></td>
                                    <td>
                                        <a href="/admin/view_cohort/<?php echo $cohort['id']; ?>" class="btn btn-outline-info btn-sm">View</a>
                                        <a href="/admin/edit_cohort/<?php echo $cohort['id']; ?>" class="btn btn-outline-warning btn-sm">Edit</a>
                                        <a href="/admin/delete_cohort/<?php echo $cohort['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this cohort?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>