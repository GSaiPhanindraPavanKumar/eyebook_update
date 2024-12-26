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
                            <button class="btn btn-secondary" onclick="resetPassword(<?= $student['id'] ?>)">Reset Password</button>
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
    });

    function resetPassword(studentId) {
        var newPassword = prompt("Enter new password:");
        if (newPassword) {
            var confirmPassword = prompt("Confirm new password:");
            if (newPassword === confirmPassword) {
                $.ajax({
                    type: 'POST',
                    url: '/admin/reset_student_password/' + studentId,
                    data: { new_password: newPassword, confirm_password: confirmPassword },
                    success: function(response) {
                        alert('Password reset successfully.');
                        location.reload();
                    },
                    error: function(response) {
                        alert('An error occurred while resetting the password.');
                    }
                });
            } else {
                alert("Passwords do not match.");
            }
        }
    }

    // Eye shutter functionality
    function togglePasswordVisibility(inputId, toggleButtonId) {
        var passwordField = document.getElementById(inputId);
        var toggleButton = document.getElementById(toggleButtonId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            passwordField.type = "password";
            toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }
</script>