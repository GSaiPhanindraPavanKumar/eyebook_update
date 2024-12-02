<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Faculty Profile</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Profile Details</p><br>
                        <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($_GET['message_type']); ?>" role="alert">
                                <?php echo htmlspecialchars($_GET['message']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Name:</th>
                                    <td><?= htmlspecialchars($faculty['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($faculty['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?= htmlspecialchars($faculty['phone']) ?></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td><?= htmlspecialchars($faculty['department']) ?></td>
                                </tr>
                                <tr>
                                    <th>University:</th>
                                    <td><?= htmlspecialchars($faculty['university']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="actions mt-4">
                            <a href="/admin/edit_faculty/<?= $faculty['id'] ?>" class="btn btn-warning">Edit Faculty</a>
                            <a href="/admin/delete_faculty/<?= $faculty['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this faculty?');">Delete Faculty</a>
                            <form method="post" action="/admin/reset_faculty_password/<?= $faculty['id'] ?>" style="display:inline;">
                                <button type="submit" class="btn btn-secondary">Reset Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>