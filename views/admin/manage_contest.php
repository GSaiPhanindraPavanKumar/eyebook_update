<?php include("sidebar.php"); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Manage Contests</h3>
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th class="col-university-name">University Name</th>
                            <th class="col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($universities as $university): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                <td>
                                    <a href="/admin/view_contests_by_university/<?php echo $university['id']; ?>" class="btn btn-info">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .col-university-name { width: 80%; }
    .col-action { width: 20%; }
    @media (max-width: 768px) {
        .col-university-name, .col-action {
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

<script>
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