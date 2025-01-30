<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Contests for University: <?php echo htmlspecialchars($university['long_name']); ?></h3>
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th class="col-title">Title</th>
                            <th class="col-description">Description</th>
                            <th class="col-start-date">Start Date</th>
                            <th class="col-end-date">End Date</th>
                            <th class="col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contests)): ?>
                            <?php foreach ($contests as $contest): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contest['title']); ?></td>
                                    <td><?php echo htmlspecialchars($contest['description']); ?></td>
                                    <td><?php echo htmlspecialchars($contest['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($contest['end_date']); ?></td>
                                    <td>
                                        <a href="/admin/view_contest/<?php echo $contest['id']; ?>" class="btn btn-info">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No contests found for this university.</td>
                            </tr>
                        <?php endif; ?>
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
    .col-title { width: 20%; }
    .col-description { width: 30%; }
    .col-start-date { width: 15%; }
    .col-end-date { width: 15%; }
    .col-action { width: 20%; }
    @media (max-width: 768px) {
        .col-title, .col-description, .col-start-date, .col-end-date, .col-action {
            width: auto;
        }
    }
</style>