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
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th class="col-title">Title</th>
                                    <th class="col-course">Course</th>
                                    <th class="col-start-time">Start Time</th>
                                    <th class="col-due-date">Due Date</th>
                                    <th class="col-submissions">Submissions</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
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

<script>
// Add this after your existing scripts
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    
    table.addEventListener('click', function(e) {
        // Find the closest row to the clicked element
        const row = e.target.closest('tr');
        
        // Ensure we have a row and it's not the header row
        if (row && !row.closest('thead')) {
            // If the click was not on a button/link/form
            if (!e.target.closest('a') && !e.target.closest('button') && !e.target.closest('form') && !e.target.closest('input')) {
                // Find the view button in this row and get its href
                const viewButton = row.querySelector('a.btn-info') || row.querySelector('a[href*="view"]');
                if (viewButton) {
                    window.location.href = viewButton.href;
                }
            }
        }
    });
});
</script>

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-title { width: 20%; }
    .col-course { width: 20%; }
    .col-start-time { width: 15%; }
    .col-due-date { width: 15%; }
    .col-submissions { width: 15%; }
    .col-actions { width: 15%; }
    @media (max-width: 768px) {
        .col-title, .col-course, .col-start-time, .col-due-date, .col-submissions, .col-actions {
            width: auto;
        }
    }

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