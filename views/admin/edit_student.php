<?php 
include "sidebar.php"; 
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Edit Student</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Student</h5>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="/admin/edit_student/<?php echo $student['id']; ?>" method="post">
                            <div class="form-group">
                                <label for="regd_no">Registration Number</label>
                                <input type="text" class="form-control" id="regd_no" name="regd_no" value="<?php echo htmlspecialchars($student['regd_no']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="section">Section</label>
                                <input type="text" class="form-control" id="section" name="section" value="<?php echo htmlspecialchars($student['section']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="stream">Stream</label>
                                <input type="text" class="form-control" id="stream" name="stream" value="<?php echo htmlspecialchars($student['stream']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="year">Year</label>
                                <input type="text" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($student['year']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="dept">Department</label>
                                <input type="text" class="form-control" id="dept" name="dept" value="<?php echo htmlspecialchars($student['dept']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                            <a href="/admin/viewStudentProfile/<?php echo $student['id']; ?>" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">