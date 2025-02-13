<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Manage Labs</h3>
                    <a href="/admin/create_public_lab" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Lab
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card"><br>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th class="col-course-name">Course Name</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['name'] ?? ''); ?></td>
                                            <td>
                                                <a href="/admin/view_labs_by_public_course/<?php echo $course['id']; ?>" class="btn btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No courses found.</td>
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

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-course-name { width: 40%; }
    .col-college-name { width: 40%; }
    .col-actions { width: 20%; }
    @media (max-width: 768px) {
        .col-course-name, .col-college-name, .col-actions {
            width: auto;
        }
    }
</style>

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
                const viewButton = row.querySelector('a.btn-info') || row.querySelector('a[href*="view_labs"]');
                if (viewButton) {
                    window.location.href = viewButton.href;
                }
            }
        }
    });
});
</script>

<style>
/* Add these styles */
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