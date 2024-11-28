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
                            <a href="/admin/reset_student_password/<?= $student['id'] ?>" class="btn btn-secondary">Reset Password</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>