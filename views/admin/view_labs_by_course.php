<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Labs for Course: <?php echo htmlspecialchars($course['name']); ?></h3>
                    <a href="/admin/manage_labs" class="btn btn-secondary">Back to Manage Labs</a>
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
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($labs)): ?>
                                    <?php for($i = 0; $i < count($labs); $i++): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($labs[$i]['title']); ?></td>
                                            <td><?php echo htmlspecialchars($labs[$i]['due_date']); ?></td>
                                            <td>
                                                <a href="/admin/view_lab_detail/<?php echo $labs[$i]['id']; ?>" class="btn btn-info">View</a>
                                                <a href="/admin/edit_lab/<?php echo $labs[$i]['id']; ?>" class="btn btn-warning">Edit</a>
                                                <a href="/admin/delete_lab/<?php echo $labs[$i]['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this lab?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No labs found for this course.</td>
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