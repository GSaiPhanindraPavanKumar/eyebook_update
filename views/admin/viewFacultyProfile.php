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
                            <button class="btn btn-secondary" onclick="resetPassword(<?= $faculty['id'] ?>)">Reset Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    function resetPassword(facultyId) {
        var newPassword = prompt("Enter new password:");
        if (newPassword) {
            var confirmPassword = prompt("Confirm new password:");
            if (newPassword === confirmPassword) {
                $.ajax({
                    type: 'POST',
                    url: '/admin/reset_faculty_password/' + facultyId,
                    data: JSON.stringify({ new_password: newPassword, confirm_password: confirmPassword }),
                    contentType: 'application/json',
                    success: function(response) {
                        console.log(response);
                        alert('Password reset successfully.');
                        location.reload();
                    },
                    error: function(response) {
                        console.log(response);
                        alert('An error occurred while resetting the password.');
                    }
                });
            } else {
                alert("Passwords do not match.");
            }
        }
    }
</script>