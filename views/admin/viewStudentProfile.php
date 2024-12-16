<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Student Profile</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Profile Details</p><br>

                        <!-- Display session message -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                                <?= $_SESSION['message'] ?>
                                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Registration Number:</th>
                                    <td><?= htmlspecialchars($student['regd_no']) ?></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Section:</th>
                                    <td><?= htmlspecialchars($student['section']) ?></td>
                                </tr>
                                <tr>
                                    <th>Stream:</th>
                                    <td><?= htmlspecialchars($student['stream']) ?></td>
                                </tr>
                                <tr>
                                    <th>Year:</th>
                                    <td><?= htmlspecialchars($student['year']) ?></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td><?= htmlspecialchars($student['dept']) ?></td>
                                </tr>
                                <tr>
                                    <th>University:</th>
                                    <td><?= htmlspecialchars($university['long_name'] ?? 'N/A') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="actions mt-4">
                            <a href="/admin/edit_student/<?= $student['id'] ?>" class="btn btn-warning">Edit Student</a>
                            <a href="/admin/delete_student/<?= $student['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete Student</a>
                            <button class="btn btn-secondary" data-toggle="modal" data-target="#resetPasswordModal">Reset Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>
<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="resetPasswordForm" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body non-clickable">
                    <!-- Non-clickable fields -->
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control non-clickable-field" id="newPassword" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control non-clickable-field" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Keep these clickable -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var regd_no = $(this).data('regd_no');
            var name = $(this).data('name');
            var email = $(this).data('email');
            $('#edit-id').val(id);
            $('#edit-regd_no').val(regd_no);
            $('#edit-name').val(name);
            $('#edit-email').val(email);
            $('#editModal').modal('show');
        });

        $('#selectAll').on('click', function() {
            $('input[name="selected[]"]').prop('checked', this.checked);
        });

        $('#resetPasswordForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = '/admin/reset_student_password/<?= $student['id'] ?>';

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                success: function(response) {
                    $('#resetPasswordModal').modal('hide');
                    alert('Password reset successfully.');
                    location.reload();
                },
                error: function(response) {
                    alert('An error occurred while resetting the password.');
                }
            });
        });
    });
</script>

<style>
    /* Make elements non-clickable */
    .non-clickable-field {
        pointer-events: none; /* Disable clicking */
        background-color: #e9ecef; /* Optional: Gray background to indicate non-clickable fields */
        opacity: 0.7; /* Optional: Reduced opacity */
    }

    /* Ensure buttons are still clickable */
    .modal-footer .btn {
        pointer-events: auto; /* Enable pointer events for buttons */
    }

</style>