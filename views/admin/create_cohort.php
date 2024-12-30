<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Create Cohort</h3>
                    <button type="submit" form="createCohortForm" class="btn btn-primary">Create Cohort</button>
                </div>
                <?php if (isset($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <form id="createCohortForm" method="POST" action="/admin/create_cohort">
                    <div class="form-group">
                        <label for="name">Cohort Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="student_ids">Student IDs (optional)</label>
                        <input type="text" id="studentSearch" class="form-control mb-3" placeholder="Search students...">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-striped" id="studentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Select</th>
                                        <th>Student Name</th>
                                        <th>University</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $existing_student_ids = $existing_student_ids ?? []; // Initialize the variable if not set
                                    foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" <?php echo in_array($student['id'], $existing_student_ids) ? 'disabled' : ''; ?>>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['university']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script>
document.getElementById('studentSearch').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#studentsTable tbody tr');
    rows.forEach(function(row) {
        var studentName = row.cells[1].textContent.toLowerCase();
        var university = row.cells[2].textContent.toLowerCase();
        if (studentName.includes(searchValue) || university.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>