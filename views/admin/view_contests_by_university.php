<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Contests for University: <?php echo htmlspecialchars($university['long_name']); ?></h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Action</th>
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
</div>