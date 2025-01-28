<?php include("sidebar.php"); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Manage Contests</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>University Name</th>
                            <th>Action</th>
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