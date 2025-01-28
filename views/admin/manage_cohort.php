<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Manage Cohorts</h3>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search Cohorts...">
                </div>
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
                        <tbody id="cohortTable">
                            <?php $serialNumber = 1; ?>
                            <!-- <?php error_log('Total number of cohorts: ' . print_r($cohorts, true)); ?> -->
                            <?php if (!empty($cohorts)): ?>
                                <?php 
                                $processed_ids = array();
                                for ($i = 0; $i < count($cohorts); $i++): 
                                    error_log(print_r($i, true));
                                    $cohorts[$i];
                                    error_log(print_r($cohort, true));
                                    // Skip if we've already processed this cohort ID
                                    if (isset($processed_ids[$cohorts[$i]['id']])) {
                                        continue;
                                    }
                                    // Add the ID to processed array
                                    $processed_ids[] = $cohorts[$i]['id'];
                                ?>
                                    <tr>
                                        <td><?php echo $serialNumber++; ?></td>
                                        <td><?php echo htmlspecialchars($cohorts[$i]['name']); ?></td>
                                        <td><?php echo htmlspecialchars($cohorts[$i]['student_count']); ?></td>
                                        <td>
                                            <a href="/admin/view_cohort/<?php echo $cohorts[$i]['id']; ?>" class="btn btn-outline-info btn-sm">View</a>
                                            <a href="/admin/edit_cohort/<?php echo $cohorts[$i]['id']; ?>" class="btn btn-outline-warning btn-sm">Edit</a>
                                            <a href="/admin/delete_cohort/<?php echo $cohorts[$i]['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this cohort?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No cohorts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#cohortTable tr');
    rows.forEach(function(row) {
        var cohortName = row.cells[1].textContent.toLowerCase();
        if (cohortName.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>