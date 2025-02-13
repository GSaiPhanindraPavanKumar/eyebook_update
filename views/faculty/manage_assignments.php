<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Manage Assignments</h3>
                    <a href="/faculty/create_assignment" class="btn btn-primary">
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
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Submissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assignments)): ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['course_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['submission_count']); ?></td>
                                            <td>
                                                <a href="/faculty/view_assignment/<?php echo $assignment['id']; ?>" class="btn btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No assignments found.</td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    
    table.addEventListener('click', function(e) {
        // Find the closest row to the clicked element
        const row = e.target.closest('tr');
        
        // Ensure we have a row and it's not the header row
        if (row && !row.closest('thead')) {
            // If the click was not on a button/link/form
            if (!e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
                // Find the view button in this row and get its href
                const viewButton = row.querySelector('a.btn-info') || row.querySelector('a[href*="view_assignment"]');
                if (viewButton) {
                    window.location.href = viewButton.href;
                }
            }
        }
    });
});
</script>

<style>
tbody tr {
    cursor: pointer;
}

tbody tr:hover {
    background-color: rgba(0,0,0,0.05) !important;
}

tbody tr a,
tbody tr button,
tbody tr input {
    position: relative;
    z-index: 2;
}
</style>