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
</style>